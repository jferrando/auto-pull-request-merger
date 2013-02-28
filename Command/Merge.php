<?php
namespace Command;

use \Library\GitHub;
use \Config;
use \App;


/**
 * this is the basic class used to check our pull requests
 */
class Merge
{
    protected $_client;


    protected $config;


    public function __construct($configFile)
    {
        $this->config = $configFile;
    }


    /**
     * Execution
     *
     * @return int|void
     */
    public function pullRequest($user = null, $password = null, $owner = null, $repo = null)
    {

        $startTime = microtime(true);

        if (!empty($user)) {
            $this->config->set("github_user",$user);
        }
        if (!empty($password)) {
            $this->config->set("github_password",$password);
        }

        if (!empty($owner)) {
            $this->config->set("github_repository_owner",$owner);
        }

        if (!empty($repo)) {
            $this->config->set("github_repository_name",$repo);
        }


        $this->_client = new \Library\GitHub\GitHubApi(new  \Library\GitHub\GitHubCurl());

        $this->_client->auth(
            $this->config->get("github_user"),
            $this->config->get("github_password"),
            \Library\GitHub\GitHubApi::AUTH_HTTP
        );
        $requestsList = $this->_getOpenPullRequests();
        for ($i = count($requestsList) - 1; $i >= 0; $i--) {
            $pullRequest = $requestsList[$i];

            $comments = $this->_getPullRequestComments($pullRequest->number);
            if (!$this->_canBeMerged($comments, $pullRequest->head->sha, $pullRequest->number)) {
                continue;
            }

            $this->_mergePullRequest($pullRequest->number);
            break;
        }
        $endTime = microtime(true);
        $time = sprintf("%0.2f", $endTime - $startTime);
        if(count($requestsList)== 0){
            App::dispatchEvent("no_pull_requests_to_parse");
        }
        echo ("Process finished: Parsed " . count($requestsList) . " open pull requests in $time seconds\n");
    }


    /**
     * Get the open pull requests of the repo
     * @return array
     */
    protected function _getOpenPullRequests()
    {
        try {

            $prs = $this->_client->get(
                '/repos/:owner/:repo/pulls',
                array(
                    'owner' => $this->config->get("github_repository_owner"),
                    'repo' => $this->config->get("github_repository_name")
                )
            );

            if (count($prs) >= $this->config->get("max_open_pull_requests")) {
                App::dispatchEvent("too_many_open_requests");

            }

            return $prs;

        } catch (\Exception $e) {
            echo "$e\n";

            return array();
        }
    }


    /**
     * Get the comments of a pull request
     * @param integer $number
     *
     * @return array
     */
    protected function _getPullRequestComments($number)
    {
        $prs = $this->_client->get(
            '/repos/:owner/:repo/issues/:number/comments',
            array(
                'owner' => $this->config->get("github_repository_owner"),
                'repo' => $this->config->get("github_repository_name"),
                'number' => $number
            )
        );

        return $prs;

    }


    /**
     * Merges a pull request
     * @param integer $number
     */
    protected function _mergePullRequest($number)
    {
        try {
            $this->_client->put(
                '/repos/:owner/:repo/pulls/:number/merge',
                array(
                    'owner' => $this->config->get("github_repository_owner"),
                    'repo' => $this->config->get("github_repository_name"),
                    'number' => $number
                ),
                array(
                    'message' => 'test',
                )
            );
            echo("Merged pull $number\n");

        } catch (\Exception $e) {
            $ex = json_decode($e->getMessage());
            $this->_addCommentToPullRequest($number, $ex->message);
            App::dispatchEvent("cannot_merge_pull_request");
        }

    }


    /**
     * Check if a pull request can be merged
     *
     * based on 3 "+1" and no blocker
     *
     * @param array  $comments
     * @param string $sha
     * @param int    $pullRequestNumber
     *
     * @return bool
     */
    protected function _canBeMerged($comments, $sha, $pullRequestNumber)
    {
        $passedCodeReview = $this->_passedCodeReview($comments, $sha, $pullRequestNumber);
        if ($passedCodeReview) {
            $this->_prepareTestingEnvironment($pullRequestNumber);
            if ($this->_passedUAT($comments)) {
                return true;
            }

            return false;
        }
    }

    protected function _prepareTestingEnvironment($pullRequestNumber)
    {
        // TODO prepare the environment
        // basic version, only checkout the code
        $jiraIssueNumber = $this->_findJiraIssueNumber($pullRequestNumber);
        if (!$jiraIssueNumber) {
            $jiraIssueNumber = "pull-request-$pullRequestNumber";
            echo "cannot find jira issue number for PR $pullRequestNumber, we use a fake branch name";
        }
        $shellCommand = "./prepareTestEnv.sh $pullRequestNumber $jiraIssueNumber";
        echo "Preparing local branch $jiraIssueNumber merging master branch with pull request $pullRequestNumber\n";
        shell_exec($shellCommand);
    }

    protected function _passedUAT($comments)
    {
        // TODO : check if there is a test ok message
        foreach ($comments as $comment) {
            if ($this->_isAUatOK($comment)) {
                return true;
            }
        }

        return false;
    }

    protected function _passedCodeReview($comments, $sha, $pullRequestNumber)
    {
        $pluses = 0;
        $blocker = false;
        if (!$this->_isBuildOk($sha) and $this->config->get("force_build_confirmation")) {
            echo("Pull request $pullRequestNumber has no build success confirmation message \n");

            return false;
        }

        foreach ($comments as $comment) {
            if ($this->_isACodeReviewOK($comment)) {
                ++$pluses;
                $blocker = false;
            } else {
                if ($this->_isACodeReviewKO($comment)) {
                    echo("Blocker found\n");

                    $blocker = true;
                    break;
                }
            }
        }

        if ($pluses >= $this->config->get("required_possitive_reviews") && !$blocker) {
            return true;
        }

        $this->_addCommentToPullRequest(
            $pullRequestNumber,
            "Will not merge pull request $pullRequestNumber,only $pluses positive reviews"
        );
        echo("Pull request $pullRequestNumber has only $pluses positive reviews\n");

    }


    /**
     * Check if the build was ok
     * @param string $sha
     *
     * @return bool
     */
    protected
    function _isBuildOk(
        $sha
    ) {
        $response = $this->_client->get(
            '/repos/:owner/:repo/statuses/:sha',
            array(
                'owner' => $this->config->get("github_repository_owner"),
                'repo' => $this->config->get("github_repository_name"),
                'sha' => $sha
            )
        );
        $last = isset($response[0]) ? $response[0] : null;

        return (!empty($last) && $last->state == 'success');
    }


    /**
     * Add a comment to a pull request
     * @param integer $number
     * @param string  $message
     */
    protected
    function _addCommentToPullRequest(
        $number,
        $message
    ) {
        $this->_client->post(
            '/repos/:owner/:repo/issues/:number/comments',
            array(
                'owner' => $this->config->get("github_repository_owner"),
                'repo' => $this->config->get("github_repository_name"),
                'number' => $number
            ),
            array(
                'body' => $message,
            )
        );
    }




    protected function _findJiraIssueNumber($pullRequestNumber)
    {

        $jiraIssue = null;
        try {
            $prs = $this->_client->get(
                '/repos/:owner/:repo/pulls/:number',
                array(
                    'owner' => $this->config->get("github_repository_owner"),
                    'repo' => $this->config->get("github_repository_name"),
                    'number' => $pullRequestNumber
                )
            );
            $title = $prs->title;
            if (preg_match("/\#[A-Za-z]+\-[0-9]+/", $title, $matches)) {
                $jiraIssue = $matches[0];
            }
        } catch (GitHubCommonException $e) {
            echo "Exception: $e , request: /repos/" . $this->config->get("github_repository_owner") . "/" . $this->config->get("github_repository_name") . "/pulls/" . $pullRequestNumber . "/";
        }
        $jiraIssue = trim($jiraIssue, "#");

        return $jiraIssue;

    }

    private function _isACodeReviewOK($comment)
    {
        foreach ($this->$this->config->get("valid_positive_code_review_messages") as $positiveMessage) {
            if (false !== strpos($comment->body, $positiveMessage)) {
                return true;
            }
        }

        return false;
    }

    private function _isACodeReviewKO($comment)
    {

        foreach ($this->$this->config->get("valid_blocker_code_review_messages") as $blockerMessage) {
            if (false !== strpos($comment->body, $blockerMessage)
            ) {
                return true;
            }
        }

        return false;
    }

    private function _isAUatOK($comment)
    {
        foreach ($this->$this->config->get("valid_uat_ok_messages") as $uatOKMessage) {
            if (false !== strpos(strtolower($comment->body), strtolower($uatOKMessage))
            ) {
                return true;
            }
        }

        return false;
    }

}

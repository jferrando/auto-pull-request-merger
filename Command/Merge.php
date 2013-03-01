<?php
namespace Command;

use \Library\GitHub;
use \Library\HipChat;
use \Config;
use \App;


/**
 * this is the basic class used to check our pull requests
 */
class Merge
{


    protected $config;


    public function __construct(\Config\Config $configObject, \Library\GitHub\Github $gitHub = null)
    {
        $this->config = $configObject;
        $this->_client = new \Library\GitHub\GitHubApi(new  \Library\GitHub\GitHubCurl());
        $this->gitHub = new \Library\GitHub\GitHub(
            $this->config->get("github_user"),
            $this->config->get("github_password"),
            $this->config->get("github_repository_owner"),
            $this->config->get("github_repository_name")

        ) ;

    }


    /**
     * Execution
     *
     * @return int|void
     */
    public function pullRequest()
    {

        $startTime = microtime(true);


        $requestsList = $this->gitHub->openPullRequests();
        for ($i = count($requestsList) - 1; $i >= 0; $i--) {
            $pullRequest = new \Library\GitHub\PullRequest($this->gitHub, $requestsList[$i]);

            if ($pullRequest->canBeMerged()){
                $pullRequest->merge();
            }

        }
        $endTime = microtime(true);
        $time = sprintf("%0.2f", $endTime - $startTime);
        if (count($requestsList) == 0) {
            App::dispatchEvent("no_pull_requests_to_parse");
        }
        echo ("Process finished: Parsed " . count($requestsList) . " open pull requests in $time seconds\n");
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
            echo "Exception: $e , request: /repos/" . $this->config->get(
                "github_repository_owner"
            ) . "/" . $this->config->get("github_repository_name") . "/pulls/" . $pullRequestNumber . "/";
        }
        $jiraIssue = trim($jiraIssue, "#");

        return $jiraIssue;

    }
}

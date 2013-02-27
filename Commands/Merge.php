<?php



/**
 * this is the basic class used to check our pull requests
 */
class Merge
{
    const MAX_OPEN_PULL_REQUESTS = 25;
    const HIPCHAT_TOKEN = 'e1'; // this is the hipchat token of the room you want to be notified
    const REQUIRED_POSITIVE_REVIEWS = 1; // this is the number of positive reviews you require to merge the pull request

    protected $validPositiveReviewMessages = array(":+1:", "+1");
    protected $validBlockerMessages = array("[B]", "[b]");

    protected $user = "myUser";
    protected $password = "myPass";
    protected $owner = 'Company';
    protected $repo = 'repo';
    protected $_client;


    /**
     * Execution
     *
     * @return int|void
     */
    public function pullRequest($user = null, $password = null, $owner = null, $repo = null)
    {

        $startTime = microtime(true);

        GitHubAutoloader::getInstance();

        if (!empty($user)) {
            $this->user = $user;
        }
        if (!empty($password)) {
            $this->password = $password;
        }

        if (!empty($owner)) {
            $this->owner = $owner;
        }

        if (!empty($repo)) {
            $this->repo = $repo;
        }


        $this->_client = new GitHubApi(new  GitHubCurl());

        $this->_client->auth(
            $this->user,
            $this->password,
            GitHubApi::AUTH_HTTP
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
                    'owner' => $this->owner,
                    'repo' => $this->repo
                )
            );

            if (count($prs) >= self::MAX_OPEN_PULL_REQUESTS) {
                $this->_sendMessage(
                    "Hey! @all We have " . count($prs) .
                        " review code or die!!"
                );
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
                'owner' => $this->owner,
                'repo' => $this->repo,
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
                    'owner' => $this->owner,
                    'repo' => $this->repo,
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
            echo("Can't merge $number\n");

        }

    }


    /**
     * Check if a pull request can be merged
     *
     * based on 3 "+1" and no blocker
     *
     * @param array  $comments
     * @param string $sha
     * @param int    $number
     *
     * @return bool
     */
    protected function _canBeMerged($comments, $sha, $number)
    {
        $pluses = 0;
        $blocker = false;
        if (!$this->_isBuildOk($sha)) {
//          $this->_addCommentToPullRequest($number,'Build failed');
            echo("Will not merge pull request $number, no build success confirmation message \n");

            return false;
        }

        foreach ($comments as $comment) {
            // parse and count aprovals
            foreach ($this->validPositiveReviewMessages as $positiveMessage) {
                if (false !== strpos($comment->body, $positiveMessage)) {
                    ++$pluses;
                    $blocker = false;
                }
            }
            // parse refusals
            foreach ($this->validBlockerMessages as $blockerMessage) {
                if (false !== strpos($comment->body, $blockerMessage)
                ) {
                    echo("Blocker found\n");

                    $blocker = true;
                    break;
                }
            }
        }

        if ($pluses >= self::REQUIRED_POSITIVE_REVIEWS && !$blocker) {
            return true;
        }

//  enable the next line if you want the script to notify you on the hipchat Room
//        $this->_addCommentToPullRequest($number,"Will not merge pull request $number,only $pluses positive reviews");
        echo("Will not merge pull request $number,only $pluses positive reviews\n");

        return false;
    }


    /**
     * Check if the build was ok
     * @param string $sha
     *
     * @return bool
     */
    protected function _isBuildOk($sha)
    {
        $response = $this->_client->get(
            '/repos/:owner/:repo/statuses/:sha',
            array(
                'owner' => $this->owner,
                'repo' => $this->repo,
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
    protected function _addCommentToPullRequest($number, $message)
    {
        $this->_client->post(
            '/repos/:owner/:repo/issues/:number/comments',
            array(
                'owner' => $this->owner,
                'repo' => $this->repo,
                'number' => $number
            ),
            array(
                'body' => $message,
            )
        );
    }


    /**
     * Send a message to hipchat
     * @param string $msg
     *
     * @return null
     */
    protected function _sendMessage($msg)
    {
        try {
            $hc = new HipChat(self::HIPCHAT_TOKEN);
            $hc->message_room('work', 'Pull-Requester', $msg, false, HipChat::COLOR_RED);
        } catch (\Exception $e) {
            echo "\nHIPCHAT API NOT RESPONDING\n";
            echo "$e\n";
        }
    }

}

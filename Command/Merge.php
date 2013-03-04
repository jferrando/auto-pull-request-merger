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
    public function __construct()
    {
        $this->_client = new \Library\GitHub\GitHubApi(new  \Library\GitHub\GitHubCurl());
        $this->gitHub = new \Library\GitHub\GitHubAdapter(
            App::Config()->get("github_user"),
            App::Config()->get("github_password"),
            App::Config()->get("github_repository_owner"),
            App::Config()->get("github_repository_name")

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
}

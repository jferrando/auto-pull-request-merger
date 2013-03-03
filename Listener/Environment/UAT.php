<?php

namespace Listener\Environment;

use \Listener;
use App;

class UAT implements \Listener\ListenerInterface
{

    public function eventList()
    {
        return array(
            "code_review_passed" => 'createUATEnvironment'
        );
    }


    public function createUATEnvironment($pullRequestNumber, $issueTrackerNumber)
    {
        // basic version, only checkout the code
        if (!$issueTrackerNumber) {
            $issueTrackerNumber = "pull-request-$pullRequestNumber";
            $cannotFindIssueMessage = "cannot find issue number for Pull Request $pullRequestNumber"
                . "creating branch with name $issueTrackerNumber name";
            App::log($cannotFindIssueMessage);
        }
        $shellCommand = "./prepareTestEnv.sh $pullRequestNumber $issueTrackerNumber";
        $message = "Preparing local branch $issueTrackerNumber merging master branch "
            . "with pull request $pullRequestNumber\n";
        App::log($message);
        shell_exec($shellCommand);
    }


}
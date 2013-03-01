<?php

namespace Library\GitHub;

class PullRequest
{

    protected $number;

    protected $gitHub;
    protected $apiPullRequest;
    protected $pullRequestComments;

    public function __construct($gitHub, $pullRequest)
    {
        $this->gitHub = $gitHub;
        $this->apiPullRequest = $pullRequest;
        $this->number = $this->apiPullRequest->number;
        $this->sha = $this->apiPullRequest->head->sha;

    }


    public function canBeMerged()
    {
        if ($this->hasPassedCodeReview()) {
            App::dispatchEvent("passed_code_review");
            if ($this->hasPassedUAT()) {
                App::dispatchEvent("pull_request_can_be_merged");

                return true;
            }

            return false;
        }
    }

    public function comments()
    {

        if (empty($this->pullRequestComments)) {
            $this->pullRequestComments = $this->gitHub->pullRequestComments($this->number);
        }

        return $this->pullRequestComments;
    }


    public function hasPassedCodeReview()
    {
        $pluses = 0;
        $blocker = false;
        //TODO force confirmation stuff
        $forceConfirmation = App::config()->get("force_build_confirmation");
        $requiredPositiveReviews = App::config()->get("required_possitive_reviews");
        if (!$this->buildIsOk() and $forceConfirmation) {
            echo("Pull request " . $this->number . " has no build success confirmation message \n");

            return false;
        }

        foreach ($this->comments() as $comment) {
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

        if ($pluses >= $requiredPositiveReviews && !$blocker) {
            return true;
        }

        $this->_addCommentToPullRequest(
            $this->number,
            "Will not merge pull request " . $this->number . ",only $pluses positive reviews"
        );
        echo("Pull request " . $this->number . " has only $pluses positive reviews\n");

    }

    public function buildIsOk()
    {
        $shaIdentifier = $this->gitHub->getStatus($this->sha);

        return (!empty($shaIdentifier) && $shaIdentifier->state == 'success');
    }

    public function hasPassedUAT()
    {
        foreach ($this->comments() as $comment) {

            if ($comment->isAValidUATOKComment()) {
                return true;
            }
        }

        return false;
    }

    public function merge()
    {
        return $this->gitHub->merge($this->number);
    }


    public function addComment($message)
    {

        return $this->gitHub->addComment($this->number, $message);
    }


}

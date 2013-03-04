<?php

namespace Library\GitHub;

use App;

class PullRequestComment
{

    protected $text;
    protected $pullRequestObject;

    public function __construct($pullRequestCommentApiObj)
    {
        $this->pr = $pullRequestCommentApiObj;
        $this->text = $pullRequestCommentApiObj->body;
    }


    public function isAValidUATOKComment()
    {
        foreach (App::config()->get("valid_uat_ok_messages") as $uatOKMessage) {
            if (false !== strpos(strtolower($this->text), strtolower($uatOKMessage))
            ) {
                return true;
            }
        }

        return false;
    }

    public function isAValidUATKOComment()
    {
        foreach (App::config()->get("valid_blocker_code_review_messages") as $blockerMessage) {
            if (false !== strpos($this->text, $blockerMessage)
            ) {
                return true;
            }
        }

        return false;
    }

    public function isAValidCodeReviewOKComment()
    {
        foreach (App::config()->get("valid_positive_code_review_messages") as $positiveMessage) {
            if (false !== strpos($this->text, $positiveMessage)) {
                return true;
            }
        }

        return false;
    }

    public function isAValidCodeReviewBlockerComment()
    {
        foreach (App::config()->get("valid_blocker_code_review_messages") as $blockerMessage) {
            if (false !== strpos($this->text, $blockerMessage)) {
                return true;
            }
        }

        return false;
    }


}
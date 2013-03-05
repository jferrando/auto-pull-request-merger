<?php

namespace Listener;
use Library\HipChat;

class ListenerHipChat implements ListenerInterface
{

    public function eventList()
    {
        // array ("eventName" => "methodToExecute"
        return array(
            "too_many_open_pull_requests" => 'tooManyOpenPullRequests'
        );
    }

    public function tooManyOpenPullRequests()
    {
        return $this->_sendMessage("Too many open pull requests. @all, Please have a look!");
    }

    /**
     * Send a message to HipChat
     * @param string $msg
     *
     * @return null
     */
    protected
    function _sendMessage(
        $msg
    ) {
        try {
            $hc = new \Library\HipChat\HipChat(\App::config()->get("hipchat_token"));
            $hc->message_room('work', 'Pull-Requester', $msg, false, \Library\Hipchat\HipChat::COLOR_RED);
        } catch (\Exception $e) {
            echo "\n HIPCHAT API NOT RESPONDING \n";
            echo "$e \n";
        }
    }
}
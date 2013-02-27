<?php

namespace Listener;
use Library\HipChat;

class ListenerHipChat implements ListenerInterface
{

    public function eventList()
    {
        return array(
            "too_many_open_pull_requests" => call_user_func(array(__CLASS__, 'tooManyOpenPullRequests'), null)
        );
    }

    public function tooManyOpenPullRequests()
    {
        return $this->_sendMessage("Too many open pull requests. @all, Please have a look!");
    }

    /**
     * Send a message to hipchat
     * @param string $msg
     *
     * @return null
     */
    protected
    function _sendMessage(
        $msg
    ) {
        try {
            $hc = new LibraryHipChat(self::HIPCHAT_TOKEN);
            $hc->message_room('work', 'Pull-Requester', $msg, false, HipChat::COLOR_RED);
        } catch (\Exception $e) {
            echo "\n HIPCHAT API NOT RESPONDING \n";
            echo "$e \n";
        }
    }
}
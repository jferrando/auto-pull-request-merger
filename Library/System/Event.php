<?php

namespace Library\System;

/**
 *  System events class
 */
class Event extends \Library\System\SingleData
{

    // generic events thrown by the application
    protected static $data = array(
        'cannot_merge_pull_request',
        'pull_request_merged',
        'too_many_open_pull_requests',
        'no_pull_requests_to_parse',
        'code_review_passed',
        'code_review_failed',
        'log'
    );


    /**
     * @param string $event event title to add
     * @return bool the event has been added
     */
    public function add(string $event)
    {

        $eventAdded = false;
        if ($this->isValid($event)) {
            array_push(self::$data, $event);
            $eventAdded = true;
        }

        return $eventAdded;
    }

    /**
     * @param string $event ensure the object matches the required conditions: format , etc...
     * @return bool the parameter matches
     */
    public function isValid(string $event)
    {
        return is_string($event);
    }
}
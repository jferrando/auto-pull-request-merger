<?php

namespace Library\System;

class Events
{
    // events thrown by the application
    protected static $list = array(
        'cannot_merge_pull_request',
        'pull_request_merged',
        'too_many_open_pull_requests',
        'no_pull_requests_to_parse'
    );


    public static function inPlace(){
        return self::$list;
    }
}
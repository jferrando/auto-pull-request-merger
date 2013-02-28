<?php

namespace Library\System;

class Events extends \Library\System\SingleData
{

    // events thrown by the application
    protected static $data = array(
        'cannot_merge_pull_request',
        'pull_request_merged',
        'too_many_open_pull_requests',
        'no_pull_requests_to_parse'
    );



}
<?php

namespace Listener;


class FileSystemLog
{
    public function eventList()
    {
        return array(
            "log" => 'printLn'
        );
    }


    public function printLn($message)
    {
        if( true === @file_put_contents(App::config("file_system_log_path"), $message)){
            return true;
        }

    }

}


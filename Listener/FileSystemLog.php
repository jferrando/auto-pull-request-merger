<?php

namespace Listener;

use App;

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
        $messageLine = "[" . date("Y-m-d H:i:s") . "] $message\n";
        if (true === file_put_contents(App::config("file_system_log_path"), $messageLine, FILE_APPEND)) {
            return true;
        }

    }

}


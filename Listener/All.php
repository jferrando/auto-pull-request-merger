<?php

namespace Listener;

/**
 * ANY NEW LISTENER HAS TO BE DEFINED IN THIS CLASS
 */
class All extends \Library\System\SingleData
{

    protected static $data = array(
        'Listener\ListenerHipChat',
        'Listener\FileSystemLog'
    );


}
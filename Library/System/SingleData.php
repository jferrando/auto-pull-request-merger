<?php

namespace Library\System;

class SingleData
{


    public static function inPlace()
    {
        return static::$data;
    }

}
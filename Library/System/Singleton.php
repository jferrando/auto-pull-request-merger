<?php

namespace Library\System;

class Singleton
{


    public static function inPlace()
    {
        return static::$data;
    }

}
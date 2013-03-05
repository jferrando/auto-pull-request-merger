<?php

namespace Library\System;

/**
 * class
 */
abstract class SingleData
{

    protected static $data;

    public static function inPlace()
    {
        return static::$data;
    }

}
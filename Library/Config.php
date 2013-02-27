<?php

namespace Library;

use Symfony\Component\Yaml\Parser;


class Config
{
    static $data;

    public function __construct($inputFile)
    {
        $yaml = new Parser();

        self::$data = $yaml->parse(file_get_contents($inputFile));
    }

    public function get($key)
    {
        if (empty(self::$data)) {
            throw new \Exception ("Empty configuration array");
        }
        if (!isset(self::$data[$key])) {
            throw new \Exception("No value for key $key");
        }

        return self::$data[$key];
    }


    public function set($key, $value)
    {
        self::$data[$key] = $value;

        return $this;
    }
}
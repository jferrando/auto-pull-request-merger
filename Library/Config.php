<?php

namespace Library;

class Config{
    static $data;

    public function __construct($inputFile)
    {
        self::$data = yaml_parse($inputFile);
        if(false === self::$data){
            throw new \Exception("Error parsing $inputFile");
        }
    }

    public function get($key){
        if(empty(self::$data)){
            throw new exception ("Empty configuration array");
        }
        if(!isset(self::$data[$key])){
            throw new exception("No value for key $key");
        }

        return self::$data[$key];
    }
}
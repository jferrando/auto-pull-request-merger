<?php

namespace Config;

use Symfony\Component\Yaml\Parser;


class Config
{
    protected $data;

    public function __construct($inputFile)
    {
        $yaml = new Parser();

        $this->data = $yaml->parse(file_get_contents($inputFile));
    }

    public function get($key)
    {
        if (empty($this->data)) {
            throw new \Exception ("Empty configuration array");
        }
        if (!isset($this->data[$key])) {
            throw new \Exception("No value for key $key");
        }

        return $this->data[$key];
    }


    public function set($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    public function isDefined($key)
    {
        return isset($this->data[$key]);
    }

    public function parse($params)
    {

        foreach ($params as $key => $value) {
            if ($this->isDefined($key)) {
                $this->set($key, $value);
            }
        }

    }

    public function all()
    {
        return $this->data;
    }

}
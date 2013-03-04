<?php

namespace Tests;

use App;
use Phake;

/**
 * @
 */
class BaseTestDefinition extends \PHPUnit_Framework_TestCase
{

    protected $app;
    protected $fixturesPath;
    protected $config;

    public function setUp()
    {

        $this->app = Phake::mock("\App");
        $this->config =  new \Config\Config("Config/config_ci.yaml");
        $this->fixturesPath = __DIR__ . "/Mock/";

    }


}
<?php

require_once './vendor/autoload.php';


use Symfony\Component\ClassLoader\UniversalClassLoader;
use \Listeners;
use \Library;
use \Library\System;

class App
{

    protected $loader;

    protected $config;

    protected $listener;

    protected static $singleton;


    public function __construct($configFile = "Config/config.yaml")
    {
        $this->loader = new UniversalClassLoader();

        $this->loader->useIncludePath(true);

        $this->loader->registerNamespaces(
            array(
                'Symfony' => __DIR__ . '/vendor/symfony/symfony/src',
                'Library' => __DIR__ . '/Library',
                'Command' => __DIR__ . '/Command',
                'Listener' => __DIR__ . '/Listener',
                'System' => __DIR__ . '/Library/System'
            )
        );

        $this->loader->register();
        $this->config = new \Config\Config(__DIR__ . "/" . $configFile);
        $this->loadModules();
    }

    public function loadModules()
    {
        foreach (Listener\All::inPlace() as $module) {
            $this->loadListener($module);
        }
    }

    public function loadListener($module)
    {

        $moduleObject = new $module;
        $this->registerObserver($module, $moduleObject->eventList());
    }

    protected function registerObserver($module, $eventList)
    {
        foreach ($eventList as $event => $method) {
            if (!in_array($event, System\Event::inPlace())) {
                throw new Exception("You cannot subscribe to the event $event. it does not exist");
            }
        }
        $this->listener[$event][$module] = $method;
    }


    public static function dispatchEvent($event, $params = null)
    {
        self::loadSingleton();

        if (isset(self::$singleton->listener[$event])) {
            foreach (self::$singleton->listener[$event] as $class => $method) {
                $obj = new $class;
                call_user_func(array($obj, $method), $params);
            }
        }
    }

    public static function Config($key = null)
    {
        self::loadSingleton();

        if (!empty($key)) {
            return self::$singleton->config[$key];
        }

        return self::$singleton->config;
    }

    public static function loadSingleton()
    {
        // system log event
        if (empty(self::$singleton)) {
            self::$singleton = new App();
        }

    }

    public static function log($message)
    {
        self::dispatchEvent("log", $message);
    }

}

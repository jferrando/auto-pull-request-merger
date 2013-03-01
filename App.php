<?php

require_once './vendor/autoload.php';


use Symfony\Component\ClassLoader\UniversalClassLoader;
use \Listeners;
use \Library;
use \Library\System;

class App
{

    protected static $loader;

    protected static $config;

    protected static $listener;

    public function start($configFile = "Config/config.yaml")
    {
        self::$loader = new UniversalClassLoader();

        self::$loader->useIncludePath(true);

        self::$loader->registerNamespaces(
            array(
                'Symfony' => __DIR__ . '/vendor/symfony/symfony/src',
                'Library' => __DIR__ . '/Library',
                'Command' => __DIR__ . '/Command',
                'Listener' => __DIR__ . '/Listener',
                'System' => __DIR__ . '/Library/System'
            )
        );

        self::$loader->register();
        self::$config = new \Config\Config($configFile);
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
            if (!in_array($event, System\Events::inPlace())) {
                throw new Exception("You cannot subscribe to the event $event. it does not exist");
            }
        }
        self::$listener[$event][$module] = $method;
    }


    public static function dispatchEvent($event, $params = null)
    {
        echo $event . "\n";
        if (isset(self::$listener[$event])) {
            foreach (self::$listener[$event] as $class => $method) {
                $obj = new $class;
                call_user_func(array($obj, $method), $params);
            }
        }
    }

    public static function Config($key = null)
    {
        if (!empty($key)) {
            return self::$config[$key];
        }

        return self::$config;
    }

}

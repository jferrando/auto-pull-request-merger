<?php
namespace App;
require_once './vendor/autoload.php';


use Symfony\Component\ClassLoader\UniversalClassLoader;
use \Listeners;


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
                'Symfony' => __DIR__ . './vendor/symfony/symfony/src',
                'Library' => __DIR__ . './Library',
                'Command' => __DIR__ . './Command',
                'Listener' => __DIR__ . './Listener'
            )
        );

        self::$loader->register();
        self::$config = new \Config\Config($configFile);
        $this->loadModules();
    }

    public function loadModules()
    {
        foreach (self::$config->get("listeners") as $module) {
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
            if (!in_array($event, self::$config->get("system_events"))) {
                throw new Exception("event $event does not exist");
            }
        }
        echo "registering $event for $module and $method\n";
        self::$listener[$event][$module] = $method;
    }


    public static function dispatchEvent($event)
    {
        echo $event."\n";
        if (isset(self::$listener[$event])) {
            echo "event $event defined\n";
            foreach (self::$listener[$event] as $class => $method) {
                $obj = new $class;
                call_user_func(array($obj, $method), null);
            }
        }
    }

    public static function Config()
    {
        return self::$config;
    }

}

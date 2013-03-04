<?php

require_once './vendor/autoload.php';


use Symfony\Component\ClassLoader\UniversalClassLoader;
use \Listeners;
use \Library;
use \Library\System;
use Symfony\Component\DependencyInjection\ContainerBuilder;


class App
{

    protected $classLoader;

    /**
     * @var Config\Config using underscore to avoid problems with method config()
     */
    protected $_config;

    protected $listener;

    /**
     * @var using underscore to avoid problems with method container()
     */
    protected $_container;

    protected static $_singleton;

    protected $dependency = array();

    protected $testMode = false;

    public function __construct($configFile = "Config/config.yaml", $testMode = false)
    {


        $classLoader = new UniversalClassLoader();
        $classLoader->useIncludePath(true);
        $classLoader->registerNamespaces(
            array(
                'Symfony' => __DIR__ . '/vendor/symfony/symfony/src',
                'Library' => __DIR__ . '/Library',
                'Command' => __DIR__ . '/Command',
                'Listener' => __DIR__ . '/Listener',
                'System' => __DIR__ . '/Library/System'
            )
        );
        $classLoader->register();
        $this->loadContainer();
        $this->_config = new \Config\Config(__DIR__ . "/" . $configFile);
        $this->loadModules();
        $this->loadDependencies();
        $this->testMode = $testMode;


    }

    public function loadContainer()
    {
        if (empty($this->_container)) {
            $this->_container = new \Symfony\Component\DependencyInjection\ContainerBuilder();
        }
    }

    public function loadModules()
    {
        foreach (Listener\All::inPlace() as $module) {
            $this->loadListener($module);
        }
    }

    public static function container()
    {
        self::loadAppSingleton();
        self::singleton()->loadContainer();

        return self::singleton()->_container;
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
        self::loadAppSingleton();
        self::singleton()->dispatch($event, $params);

    }

    public function dispatch($event, $params = null)
    {
        if (isset($this->listener[$event])) {
            foreach ($this->listener[$event] as $class => $method) {
                $obj = new $class;
                call_user_func(array($obj, $method), $params);
            }
        }
    }

    public static function config($key = null)
    {
        self::loadAppSingleton();

        if ($key !== null) {
            return self::singleton()->_config->get($key);
        }

        return self::singleton()->_config;
    }

    public static function loadAppSingleton()
    {
        // system log event
        if (empty(self::$_singleton)) {
            self::$_singleton = new App();
        }

    }

    public static function log($message)
    {
        self::dispatchEvent("log", $message);
    }


    public function object($name, $constructorParams = null, $mocked = false)
    {
        self::loadAppSingleton();

        $obj = new $name($constructorParams);
        foreach (self::singleton()->dependency[$name] as $dependency) {
            $parts = preg_split("\\", $dependency);
            $dependencyName = $parts[count($parts) - 1];
            if (!$mocked) {
                $obj->addDependency($dependencyName, new $dependency);
            } else {
                $obj->addDependency($dependencyName, Phake::mock($dependency));
            }
        }

        return $obj;
    }

    public function addObjectDependency($className, $dependency)
    {
        return (bool)array_push($this->dependency[$className], $dependency);
    }

    public function loadDependencies()
    {
        foreach ($this->dependencyDefinitions() as $dependency) {
            $dependency->load($this->_container);
        }

    }

    public function dependencyDefinitions()
    {
        $directoryIterator = new DirectoryIterator(__DIR__ . "/Dependency");
        $dependencyArray = array();
        foreach ($directoryIterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $className = trim($file->getFileName(), ".php");
            $classNameSpaced = '\\Dependency\\' . $className;
            array_push($dependencyArray, new $classNameSpaced);

        }

        return $dependencyArray;
    }


    public static function singleton($set = null)
    {
        if ($set) {
            self::$_singleton = $set;
        }

        return self::$_singleton;
    }
}

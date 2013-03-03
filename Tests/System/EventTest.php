<?php
namespace Tests;
require_once __DIR__ . "/../../App.php";



class EventTest extends \PHPUnit_Framework_TestCase
{


    public function testAdd()
    {
        new \App();
        $testEvent = "test_event_title";
        $foundEvent= false;
        $systemEvent = new \Library\System\Event;
        $systemEvent->add($testEvent);
        foreach ($systemEvent->inPlace() as $event) {
            if ($event == $testEvent) {
                $foundEvent = true;
            }
        }
        $this->assertTrue($foundEvent);
    }

}
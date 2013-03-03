<?php
namespace Tests;

require_once __DIR__ . "/../../App.php";


class EventTest extends \PHPUnit_Framework_TestCase
{


    public function testAdd()
    {
        new \App("Config/config_ci.yaml");
        $testEvent = "test_event_title";
        $foundEvent = false;
        $systemEvent = new \Library\System\Event;
        $originalEventCount = count($systemEvent->inPlace());
        $systemEvent->add($testEvent);
        $newEventCount = $originalEventCount + 1;

        $eventsInPlace = $systemEvent->inPlace();
        $this->assertContains($testEvent, $eventsInPlace);
        $this->assertCount($newEventCount, $eventsInPlace);

    }

}
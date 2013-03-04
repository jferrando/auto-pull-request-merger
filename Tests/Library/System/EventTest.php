<?php
namespace Tests;


class EventTest extends BaseTestDefinition
{
    protected $app;

    public function setUp()
    {
        parent::setUp();
    }

    public function testAdd()
    {


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
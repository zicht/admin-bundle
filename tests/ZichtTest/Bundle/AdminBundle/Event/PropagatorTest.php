<?php
/**
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace ZichtTest\Bundle\AdminBundle\Event;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Zicht\Bundle\AdminBundle\Event\Propagator;

class PropagatorTest extends \PHPUnit_Framework_TestCase
{
    function testPropagation()
    {
        $propagator = new Propagator();
        $impl = $this->getMock('Zicht\Bundle\AdminBundle\Event\PropagationInterface');
        $dispatcher = $this->getMock(EventDispatcher::class);
        $impl->expects($this->once())->method('buildAndForwardEvent');
        $propagator->registerPropagation('some event', $impl);

        $event = $this->getMock('Symfony\Component\EventDispatcher\Event');
        $event->expects($this->any())->method('getName')->will($this->returnValue('some event'));
        $propagator->onEvent($event, 'some event', $dispatcher);
    }
}
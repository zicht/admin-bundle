<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace ZichtTest\Bundle\AdminBundle\Event;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Zicht\Bundle\AdminBundle\Event\Propagator;

class PropagatorTest extends \PHPUnit_Framework_TestCase
{
    public function testPropagation()
    {
        $propagator = new Propagator();
        $impl = $this->createMock('Zicht\Bundle\AdminBundle\Event\PropagationInterface');
        $dispatcher = $this->createMock(EventDispatcher::class);
        $impl->expects($this->once())->method('buildAndForwardEvent');
        $propagator->registerPropagation('some.event', $impl);

        $event = $this->createMock('Symfony\Component\EventDispatcher\Event');
        $propagator->onEvent($event, 'some.event', $dispatcher);
    }
}

<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace ZichtTest\Bundle\AdminBundle\Event;

use Zicht\Bundle\AdminBundle\Event\Propagator;

class PropagatorTest extends \PHPUnit_Framework_TestCase
{
    function testPropagation()
    {
        $propagator = new Propagator();
        $impl = $this->getMock('Zicht\Bundle\AdminBundle\Event\PropagationInterface');
        $impl->expects($this->once())->method('buildAndForwardEvent');
        $propagator->registerPropagation('some event', $impl);

        $event = $this->getMock('Symfony\Component\EventDispatcher\Event');
        $event->expects($this->any())->method('getName')->will($this->returnValue('some event'));
        $propagator->onEvent($event);
    }
}
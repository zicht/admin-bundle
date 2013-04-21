<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace ZichtTest\Bundle\AdminBundle\DependencyInjection\Compiler;

use Zicht\Bundle\AdminBundle\DependencyInjection\Compiler\EventPropagationPass;
use Symfony\Component\DependencyInjection\Reference;

class EventPropagationPassTest extends \PHPUnit_Framework_TestCase
{
    function testCompilerPass()
    {
        $pass = new EventPropagationPass();
        $containerBuilder = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $containerBuilder->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with('zicht_admin.event_propagation')
            ->will($this->returnValue(array()));
        $pass->process($containerBuilder);
    }


    function testCompilerPassWillAttachEvents()
    {
        $pass = new EventPropagationPass();
        $definition = $this->getMock('Symfony\Component\DependencyInjection\Definition');

        $definition->expects($this->once())->method('addTag')->with('kernel.event_listener', array('event' => 'someevent', 'method' => 'onEvent'));
        $definition->expects($this->once())->method('addMethodCall')->with('registerPropagation', array('someevent', new Reference('my_service')));
        $containerBuilder = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $containerBuilder->expects($this->at(0))->method('getDefinition')->will($this->returnValue($definition));
        $containerBuilder->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with('zicht_admin.event_propagation')
            ->will($this->returnValue(array(
                'my_service' => array(array('event' => 'someevent'))
            )));
        $pass->process($containerBuilder);
    }
}
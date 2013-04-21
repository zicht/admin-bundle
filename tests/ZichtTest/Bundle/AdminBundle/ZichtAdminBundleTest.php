<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

class ZichtAdminBundleTest extends PHPUnit_Framework_TestCase
{
    function testBundleRegistersCompilerPass()
    {
        $containerBuilder = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $self = $this;
        $containerBuilder->expects($this->once())->method('addCompilerPass')->will($this->returnCallback(function($arg1, $arg2) use($self) {
            $self->assertInstanceOf('Zicht\Bundle\AdminBundle\DependencyInjection\Compiler\EventPropagationPass', $arg1);
            $self->assertEquals(\Symfony\Component\DependencyInjection\Compiler\PassConfig::TYPE_BEFORE_OPTIMIZATION, $arg2);
        }));

        $bundle = new \Zicht\Bundle\AdminBundle\ZichtAdminBundle();
        $bundle->build($containerBuilder);
    }
}
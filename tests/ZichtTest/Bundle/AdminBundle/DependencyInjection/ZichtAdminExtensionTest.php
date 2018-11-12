<?php
/**
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace ZichtTest\Bundle\AdminBundle\DependencyInjection\Compiler;


class ZichtAdminExtensionTest extends \PHPUnit_Framework_TestCase
{
    function testLoadWillLoadXmlFile()
    {
        $e = new \Zicht\Bundle\AdminBundle\DependencyInjection\ZichtAdminExtension();
        $builder = new \Symfony\Component\DependencyInjection\ContainerBuilder();
        $e->load(array(), $builder);

        $this->assertTrue($builder->hasDefinition('zicht_admin.menu'));
        $this->assertTrue($builder->hasDefinition('zicht_admin.event_subscriber'));
        $this->assertTrue($builder->hasDefinition('zicht_admin.event_propagator'));
        $this->assertTrue($builder->hasDefinition('zicht_admin.twig_extension'));
    }
}
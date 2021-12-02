<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace ZichtTest\Bundle\AdminBundle\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zicht\Bundle\AdminBundle\DependencyInjection\ZichtAdminExtension;

class ZichtAdminExtensionTest extends TestCase
{
    public function testLoadWillLoadXmlFile()
    {
        $e = new ZichtAdminExtension();
        $builder = new ContainerBuilder();
        $e->load([], $builder);

        $this->assertTrue($builder->hasDefinition('zicht_admin.menu'));
        $this->assertTrue($builder->hasDefinition('zicht_admin.event_subscriber'));
        $this->assertTrue($builder->hasDefinition('zicht_admin.event_propagator'));
        $this->assertTrue($builder->hasDefinition('zicht_admin.twig_extension'));
    }
}

<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass that registers the propagataor as a specified event for the specified propagation configuration.
 */
class EventPropagationPass implements CompilerPassInterface
{
    /**
     * Process
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $def = $container->getDefinition('zicht_admin.event_propagator');

        foreach ($container->findTaggedServiceIds('zicht_admin.event_propagation') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                $def->addTag('kernel.event_listener', array('event' => $attribute['event'], 'method' => 'onEvent'));
                $def->addMethodCall('registerPropagation', array($attribute['event'], new Reference($id)));
            }
        }
    }
}

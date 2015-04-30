<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht online <http://zicht.nl>
 */
namespace Zicht\Bundle\AdminBundle\DependencyInjection;

use \Symfony\Component\Config\Definition\Builder\TreeBuilder;
use \Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('zicht_admin');
        $rootNode
            ->children()
                ->arrayNode('transactional_listener')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('auto')->defaultTrue()->end()
                        ->scalarNode('pattern')->defaultValue('!^/admin.*(edit|delete|create|move)!')->end()
                    ->end()
                ->end()
                ->arrayNode('quicklist')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('repository')->end()
                            ->scalarNode('title')->end()
                            ->arrayNode('fields')->prototype('scalar')->end()->end()
                            ->scalarNode('name')->end()
                            ->booleanNode('exposed')->defaultValue(false)->end()
                        ->end()
                    ->end()
                    ->useAttributeAsKey('name')
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}

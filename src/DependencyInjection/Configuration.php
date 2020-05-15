<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('zicht_admin');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->arrayNode('menu')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('hosts')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('security')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('mapped_attributes')
                            ->prototype('scalar')->end()
                            ->defaultValue(['EDIT', 'DELETE', 'VIEW', 'CREATE', 'ADMIN'])
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('transactional_listener')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('auto')->defaultTrue()->end()
                        ->scalarNode('pattern')
                            ->defaultValue('!^/admin.*(edit|delete|create|move)!')
                            ->validate()
                                ->ifTrue(
                                    function ($p) {
                                        return (false === preg_match($p, ''));
                                    }
                                )->thenInvalid('Invalid PCRE pattern')
                            ->end()
                        ->end()
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
                ->arrayNode('rc')
                    ->prototype('array')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('mode')->defaultValue('trigger')->end()
                            ->scalarNode('route')->end()
                            ->arrayNode('route_params')->prototype('variable')->end()->defaultValue([])->end()
                            ->scalarNode('method')->defaultValue(null)->end()
                            ->scalarNode('title')->end()
                            ->scalarNode('button')->end()
                        ->end()
                    ->end()
                    ->useAttributeAsKey('name')
                ->end()
            ->end();

        return $treeBuilder;
    }
}

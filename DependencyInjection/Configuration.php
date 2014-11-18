<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace Fox\CategoryManagerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from app/config files
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fox_category_manager');

        $rootNode
            ->children()
                ->scalarNode('entity_manager')
                    ->defaultValue("default")
                ->end()
                ->arrayNode('connection')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('index_name')
                            ->defaultValue('fox-category-manager')
                            ->info('Index name for category manager')
                        ->end()
                        ->scalarNode('host')
                            ->info('Address of your category manager es database')
                            ->defaultValue('127.0.0.1')
                        ->end()
                        ->integerNode('port')
                            ->info('Port of your category manager es database')
                            ->defaultValue(9200)
                        ->end()
                        ->arrayNode('index_settings')
                            ->prototype('variable')
                                ->treatNullLike([])
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}

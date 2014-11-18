<?php

/*
 *************************************************************************
 * NFQ eXtremes CONFIDENTIAL
 * [2013] - [2014] NFQ eXtremes UAB
 * All Rights Reserved.
 *************************************************************************
 * NOTICE:
 * All information contained herein is, and remains the property of NFQ eXtremes UAB.
 * Dissemination of this information or reproduction of this material is strictly forbidden
 * unless prior written permission is obtained from NFQ eXtremes UAB.
 *************************************************************************
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

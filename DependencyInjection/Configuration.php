<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\CategoryManagerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from app/config files.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ongr_elasticsearch');

        $rootNode
            ->children()
                ->scalarNode('entity_manager')
                    ->defaultValue('default')
                ->end()
                ->arrayNode('connection')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('index_name')
                            ->defaultValue('ongr-category-manager')
                            ->info('Index name for category manager')
                        ->end()
                        ->scalarNode('host')
                            ->info('Address of your category manager es database')
                            ->defaultValue('127.0.0.1:9200')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}

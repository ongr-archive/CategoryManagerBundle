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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages bundle configuration
 */
class FoxCategoryManagerExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // set connection settings
        $container->setParameter('fox_category_manager.connection.index_name', $config['connection']['index_name']);
        $container->setParameter('fox_category_manager.connection.port', $config['connection']['port']);
        $container->setParameter('fox_category_manager.connection.host', $config['connection']['host']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('injection.yml');
        $loader->load('connection.yml');
        $loader->load('triggers.yml');

        // check if defined entity manager exists
        $doctrineManagers = $container->getParameter("doctrine.entity_managers");
        if (!array_key_exists($config['entity_manager'], $doctrineManagers)) {
            throw new \LogicException(
                "Parameter 'fox_category_manager.entity_manager' " .
                "must have value one of defined in 'doctrine.orm.managers'."
            );
        }

        // set local alias for defined doctrine entity manager
        $aliasedName = sprintf('doctrine.orm.%s_entity_manager', $config['entity_manager']);
        $container->setAlias('fox_category_manager.entity_manager', $aliasedName);

        $this->injectDDAL($config, $container);
        $this->tagTriggers($config, $container);
    }

    /**
     * Injects mapping and creates sessions for ddal
     */
    protected function injectDDAL($config, ContainerBuilder $container)
    {
        // Add custom index settings if provided
        if (!empty($config['connection']['index_settings'])) {
            $settings = $container->getParameter('fox_category_manager.node_model_connection.settings');

            $settings['index'] = array_merge(
                $settings['index'],
                $config['connection']['index_settings']
            );

            $container->setParameter('fox_category_manager.node_model_connection.settings', $settings);
        }

        $ddalConfig = $container->getParameter('fox_ddal.model_map');
        $contentConfig = $container->getParameter('fox_category_manager.fox_ddal.model_map');

        $ddalConfig = array_merge($ddalConfig, $contentConfig);

        $container->setParameter('fox_ddal.model_map', $ddalConfig);
    }

    /**
     * Adds tags to category manager triggers
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    protected function tagTriggers($config, ContainerBuilder $container)
    {
        $triggers = [
            'categories_insert',
            'categories_update',
            'categories_delete',
        ];

        foreach ($triggers as $trigger) {

            $definition = $container->getDefinition("fox_category_manager.triggers.{$trigger}");
            $definition->addTag(
                'fox_connections.trigger',
                ['trigger' => $trigger, 'connection' => $config['entity_manager']]
            );
        }
    }
}

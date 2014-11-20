<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\CategoryManagerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages bundle configuration.
 */
class ONGRCategoryManagerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Set connection settings.
        $container->setParameter('ongr_category_manager.connection.index_name', $config['connection']['index_name']);
        $container->setParameter('ongr_category_manager.connection.port', $config['connection']['port']);
        $container->setParameter('ongr_category_manager.connection.host', $config['connection']['host']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('injection.yml');
        $loader->load('connection.yml');
        $loader->load('triggers.yml');

        // Check if defined entity manager exists.
        $doctrineManagers = $container->getParameter('doctrine.entity_managers');
        if (!array_key_exists($config['entity_manager'], $doctrineManagers)) {
            throw new \LogicException(
                "Parameter 'ongr_category_manager.entity_manager' " .
                "must have value one of defined in 'doctrine.orm.managers'."
            );
        }

        // Set local alias for defined doctrine entity manager.
        $aliasedName = sprintf('doctrine.orm.%s_entity_manager', $config['entity_manager']);
        $container->setAlias('ongr_category_manager.entity_manager', $aliasedName);

        $this->tagTriggers($config, $container);
    }

    /**
     * Adds tags to category manager triggers.
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
            $definition = $container->getDefinition("ongr_category_manager.triggers.{$trigger}");
            $definition->addTag(
                'ongr_connections.trigger',
                ['trigger' => $trigger, 'connection' => $config['entity_manager']]
            );
        }
    }
}

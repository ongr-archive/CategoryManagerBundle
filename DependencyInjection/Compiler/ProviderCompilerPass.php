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

namespace Fox\CategoryManagerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to manage tagged providers
 */
class ProviderCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('fox_category_manager.transfer_manager');

        $taggedServices = $container->findTaggedServiceIds(
            'fox_category_manager.provider'
        );

        foreach ($taggedServices as $name => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (empty($attributes['id'])) {
                    throw new \LogicException("'fox_category_manager.provider' tag must have an 'id' attribute");
                }

                $definition->addMethodCall(
                    'addProvider',
                    [new Reference($name), $attributes['id']]
                );
            }
        }
    }
}

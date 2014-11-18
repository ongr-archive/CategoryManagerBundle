<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
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

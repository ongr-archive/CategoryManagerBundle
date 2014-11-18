<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\CategoryManagerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to manage tagged writers
 */
class WriterCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('ongr_category_manager.transfer_manager');

        $taggedServices = $container->findTaggedServiceIds(
            'ongr_category_manager.writer'
        );

        foreach ($taggedServices as $name => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (empty($attributes['id'])) {
                    throw new \LogicException("'ongr_category_manager.writer' tag must have an 'id' attribute");
                }

                $definition->addMethodCall(
                    'addWriter',
                    [new Reference($name), $attributes['id']]
                );
            }
        }
    }
}

<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace Fox\CategoryManagerBundle;

use Fox\CategoryManagerBundle\DependencyInjection\Compiler\ProviderCompilerPass;
use Fox\CategoryManagerBundle\DependencyInjection\Compiler\WriterCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FoxCategoryManagerBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ProviderCompilerPass());
        $container->addCompilerPass(new WriterCompilerPass());
    }
}

<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        return array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new ONGR\ElasticsearchBundle\ONGRElasticsearchBundle(),
            new ONGR\ConnectionsBundle\ONGRConnectionsBundle(),
            new ONGR\CategoryManagerBundle\ONGRCategoryManagerBundle(),
            new \FOS\JsRoutingBundle\FOSJsRoutingBundle(),
        );
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}

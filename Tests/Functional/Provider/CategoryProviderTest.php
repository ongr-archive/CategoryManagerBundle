<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace Fox\CategoryManagerBundle\Tests\Functional\Provider;

use Fox\CategoryManagerBundle\Provider\CategoryProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CategoryProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for getCategories()
     */
    public function testGetCategories()
    {
        $container = new ContainerBuilder();
        $container->set('fox_category_manager.entity_manager', $this->getMock('Doctrine\\ORM\\EntityManagerInterface'));

        $provider = new CategoryProvider('Fox\\CategoryManagerBundle\\Tests\\Functional\\Provider\\DummyProvider');
        $provider->setContainer($container);
        $result = $provider->getCategories();

        $this->assertInstanceOf('Fox\\CategoryManagerBundle\\Iterator\\CategoryIteratorInterface', $result);
    }

    /**
     * Test for getCategories() in case service container was not injected
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage must have service container injected
     */
    public function testGetCategoriesContainerException()
    {
        $provider = new CategoryProvider('Fox\\CategoryManagerBundle\\Tests\\Functional\\Provider\\DummyProvider');
        $result = $provider->getCategories();

        $this->assertInstanceOf('Fox\\CategoryManagerBundle\\Iterator\\CategoryIteratorInterface', $result);
    }

    /**
     * Test for getCategories() in case of exception
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage not found
     */
    public function testGetCategoriesException()
    {
        $provider = new CategoryProvider('foo\\non_existing_class');
        $provider->getCategories();
    }
}

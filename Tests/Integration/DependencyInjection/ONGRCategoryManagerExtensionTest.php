<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CategoryManagerBundle\Tests\Integration\DependencyInjection;

use ONGR\CategoryManagerBundle\DependencyInjection\ONGRCategoryManagerExtension;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class CategoryExtensionTest.
 */
class ONGRCategoryManagerExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return ContainerBuilder
     */
    private function getContainer()
    {
        $container = new ContainerBuilder();
        $container->setParameter('doctrine.entity_managers', ['test_manager' => [], 'default' => []]);
        $container->setParameter(
            'ongr_category_manager',
            [
                'mapping' => [],
                'node_model_connection' => [ 'settings' => []],
            ]
        );

        return $container;
    }

    /**
     * Data provider for testEntityManagerLoad.
     *
     * @return array
     */
    public function getEntityManagerLoadData()
    {
        $out = [];

        // Case #1 default configurations.
        $config = [];
        $out[] = [$config, 'doctrine.orm.default_entity_manager'];

        // Case #2 configurations with custom entity_manager.
        $config['ongr_category_manager'] = [
            'entity_manager' => 'test_manager',
        ];
        $out[] = [$config, 'doctrine.orm.test_manager_entity_manager'];

        return $out;
    }

    /**
     * Test to check if entity manager alias is assigned by given configuration.
     *
     * @param array  $config
     * @param string $aliasedName
     *
     * @dataProvider getEntityManagerLoadData
     */
    public function testEntityManagerLoad($config, $aliasedName)
    {
        $container = $this->getContainer();

        $extension = new ONGRCategoryManagerExtension();
        $extension->load($config, $container);

        $aliases = $container->getAliases();
        $expectedAlias = new Alias($aliasedName);

        $this->assertArrayHasKey('ongr_category_manager.entity_manager', $aliases);
        $this->assertEquals($expectedAlias, $aliases['ongr_category_manager.entity_manager']);
    }

    /**
     * Test if exception is thrown on invalid entity_manager configuration.
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage ongr_category_manager.entity_manager
     */
    public function testInvalidEntityManagerLoad()
    {
        $container = $this->getContainer();

        $config['ongr_category_manager'] = [
            'entity_manager' => 'invalid_manager',
        ];

        $extension = new ONGRCategoryManagerExtension();
        $extension->load($config, $container);
    }
}

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

namespace Fox\CategoryManagerBundle\Tests\Functional\DependencyInjection;

use Fox\CategoryManagerBundle\DependencyInjection\FoxCategoryManagerExtension;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CategoryExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return ContainerBuilder
     */
    private function getContainer()
    {
        $container = new ContainerBuilder();
        $container->setParameter('doctrine.entity_managers', ['test_manager' => [], 'default' => []]);
        $container->setParameter('fox_ddal.model_map', []);
        $container->setParameter(
            'fox_ddal.driver_map.elastic_search',
            [
                'map' => []
            ]
        );

        return $container;
    }

    /**
     * Data provider for testEntityManagerLoad
     *
     * @return array
     */
    public function getEntityManagerLoadData()
    {
        $out = [];

        // case #1 default configurations
        $config = [];
        $out[] = [$config, 'doctrine.orm.default_entity_manager'];

        // case #2 configurations with custom entity_manager
        $config['fox_category_manager'] = [
            'entity_manager' => 'test_manager',
        ];
        $out[] = [$config, 'doctrine.orm.test_manager_entity_manager'];

        return $out;
    }

    /**
     * Test to check if entity manager alias is assigned by given configuration
     *
     * @param array $config
     * @param string $aliasedName
     *
     * @dataProvider getEntityManagerLoadData
     */
    public function testEntityManagerLoad($config, $aliasedName)
    {
        $container = $this->getContainer();

        $extension = new FoxCategoryManagerExtension();
        $extension->load($config, $container);

        $aliases = $container->getAliases();
        $expectedAlias = new Alias($aliasedName);

        $this->assertArrayHasKey('fox_category_manager.entity_manager', $aliases);
        $this->assertEquals($expectedAlias, $aliases['fox_category_manager.entity_manager']);
    }

    /**
     * Test if exception is thrown on invalid entity_manager configuration
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage fox_category_manager.entity_manager
     */
    public function testInvalidEntityManagerLoad()
    {
        $container = $this->getContainer();

        $config['fox_category_manager'] = [
            'entity_manager' => 'invalid_manager',
        ];

        $extension = new FoxCategoryManagerExtension()  ;
        $extension->load($config, $container);
    }

    /**
     * Test if DDAL settings are loaded
     */
    public function testLoadDDALSettings()
    {
        $container = $this->getContainer();
        $extension = new FoxCategoryManagerExtension();

        $extension->load([], $container);

        $this->assertEquals(
            [
                'NodeModel.class' => 'Fox\\CategoryManagerBundle\\Model\\NodeModel',
            ],
            $container->getParameter('fox_ddal.model_map')
        );
        $this->assertTrue($container->hasDefinition('fox_category_manager.elastic_search_driver'));

        $definition = $container->getDefinition('fox_category_manager.elastic_search_driver');

        $this->assertEquals('Fox\DDALBundle\ElasticSearch\ElasticSearchDriver', $definition->getClass());
        $this->assertArrayHasKey('node', $container->getParameter('fox_category_manager.connection.mapping'));
    }

    /**
     * Data provider for testLoadConnectionSettings
     *
     * @return array[]
     */
    public function loadConnectionSettingsData()
    {
        // #0 default values are used
        $container = $this->getContainer();
        $config = [
            'connection' => [
                'index_name' => 'fox-category-manager',
                'host' => '127.0.0.1',
                'port' => 9200
            ]
        ];
        $out[] = [
            $container,
            $config,
            9200,
            '127.0.0.1',
            'fox-category-manager',
            [
                'index' => [
                    'number_of_shards' => 1,
                    'number_of_replicas' => 1,
                    'refresh_interval' => -1
                ],
                'analysis' => [
                    'analyzer' => [
                        'default' => [
                            'type' => 'snowball',
                            'language' => 'German2',
                            'stopwords' => 'der,die,das,mit,und,für',
                        ]
                    ],
                ],
            ]
        ];

        // #1 custom
        $container = $this->getContainer();
        $config = [
            'connection' => [
                'index_name' => 'fox-test',
                'host' => '156.39.58.10',
                'port' => 6666,
                'index_settings' => [
                    'number_of_shards' => 3
                ],
            ]
        ];
        $out[] = [
            $container,
            $config,
            6666,
            '156.39.58.10',
            'fox-test',
            [
                'index' => [
                    'number_of_shards' => 3,
                    'number_of_replicas' => 1,
                    'refresh_interval' => -1
                ],
                'analysis' => [
                    'analyzer' => [
                        'default' => [
                            'type' => 'snowball',
                            'language' => 'German2',
                            'stopwords' => 'der,die,das,mit,und,für',
                        ]
                    ],
                ],
            ]
        ];

        return $out;
    }

    /**
     * Tests if port host and index are loaded correctly i.e. tests connection settings
     *
     * @param ContainerBuilder $container
     * @param array $config
     * @param integer $expectedPort
     * @param string $expectedHost
     * @param string $expectedIndex
     * @param string $expectedSettings
     *
     * @dataProvider loadConnectionSettingsData
     */
    public function testLoadConnectionSettings(
        $container,
        $config,
        $expectedPort,
        $expectedHost,
        $expectedIndex,
        $expectedSettings
    ) {

        $extension = new FoxCategoryManagerExtension();

        $extension->load(['fox_category_manager' => $config], $container);

        // index
        $this->assertTrue($container->hasParameter('fox_category_manager.connection.index_name'));
        $this->assertEquals($expectedIndex, $container->getParameter('fox_category_manager.connection.index_name'));

        // host
        $this->assertTrue($container->hasParameter('fox_category_manager.connection.host'));
        $this->assertEquals($expectedHost, $container->getParameter('fox_category_manager.connection.host'));

        // port
        $this->assertTrue($container->hasParameter('fox_category_manager.connection.port'));
        $this->assertEquals($expectedPort, $container->getParameter('fox_category_manager.connection.port'));

        // index settings
        $this->assertTrue($container->hasParameter('fox_category_manager.node_model_connection.settings'));
        $this->assertEquals(
            $expectedSettings,
            $container->getParameter('fox_category_manager.node_model_connection.settings')
        );
    }
}

<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\CategoryManagerBundle\Tests\Unit\DependencyInjection;

use ONGR\CategoryManagerBundle\DependencyInjection\ONGRCategoryManagerExtension;
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
        $container->setParameter('ongr_ddal.model_map', []);
        $container->setParameter(
            'ongr_ddal.driver_map.elastic_search',
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
        $config['ongr_category_manager'] = [
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

        $extension = new ONGRCategoryManagerExtension();
        $extension->load($config, $container);

        $aliases = $container->getAliases();
        $expectedAlias = new Alias($aliasedName);

        $this->assertArrayHasKey('ongr_category_manager.entity_manager', $aliases);
        $this->assertEquals($expectedAlias, $aliases['ongr_category_manager.entity_manager']);
    }

    /**
     * Test if exception is thrown on invalid entity_manager configuration
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

        $extension = new ONGRCategoryManagerExtension()  ;
        $extension->load($config, $container);
    }

    /**
     * Test if DDAL settings are loaded
     */
    public function testLoadDDALSettings()
    {
        $container = $this->getContainer();
        $extension = new ONGRCategoryManagerExtension();

        $extension->load([], $container);

        $this->assertEquals(
            [
                'NodeModel.class' => 'ONGR\\CategoryManagerBundle\\Model\\NodeModel',
            ],
            $container->getParameter('ongr_ddal.model_map')
        );
        $this->assertTrue($container->hasDefinition('ongr_category_manager.elastic_search_driver'));

        $definition = $container->getDefinition('ongr_category_manager.elastic_search_driver');

        $this->assertEquals('ONGR\DDALBundle\ElasticSearch\ElasticSearchDriver', $definition->getClass());
        $this->assertArrayHasKey('node', $container->getParameter('ongr_category_manager.connection.mapping'));
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
                'index_name' => 'ongr-category-manager',
                'host' => '127.0.0.1',
                'port' => 9200
            ]
        ];
        $out[] = [
            $container,
            $config,
            9200,
            '127.0.0.1',
            'ongr-category-manager',
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
                'index_name' => 'ongr-test',
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
            'ongr-test',
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

        $extension = new ONGRCategoryManagerExtension();

        $extension->load(['ongr_category_manager' => $config], $container);

        // index
        $this->assertTrue($container->hasParameter('ongr_category_manager.connection.index_name'));
        $this->assertEquals($expectedIndex, $container->getParameter('ongr_category_manager.connection.index_name'));

        // host
        $this->assertTrue($container->hasParameter('ongr_category_manager.connection.host'));
        $this->assertEquals($expectedHost, $container->getParameter('ongr_category_manager.connection.host'));

        // port
        $this->assertTrue($container->hasParameter('ongr_category_manager.connection.port'));
        $this->assertEquals($expectedPort, $container->getParameter('ongr_category_manager.connection.port'));

        // index settings
        $this->assertTrue($container->hasParameter('ongr_category_manager.node_model_connection.settings'));
        $this->assertEquals(
            $expectedSettings,
            $container->getParameter('ongr_category_manager.node_model_connection.settings')
        );
    }
}

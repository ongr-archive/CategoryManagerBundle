<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\CategoryManagerBundle\Tests\Functional\Service;

use ONGR\CategoryManagerBundle\Service\SuggestionsManager;
use ONGR\ElasticsearchBundle\Test\ElasticsearchTestCase;

class SuggestionsManagerTest extends ElasticsearchTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getDataArray()
    {
        return [
            'default' => [
                'node' => [
                    [
                        '_id' => 'test_id_1',
                        'rootId' => 'test_root_1',
                        'path' => 'Food',
                        'weight' => 0,
                    ],
                    [
                        '_id' => 'test_id_2',
                        'rootId' => 'test_root_1',
                        'path' => 'Food / Vegetables',
                        'weight' => 1,
                    ],
                    [
                        '_id' => 'test_id_3',
                        'rootId' => 'test_root_1',
                        'path' => 'Food / Vegetables / Green',
                        'weight' => 2,
                    ],
                    [
                        '_id' => 'test_id_4',
                        'rootId' => 'test_root_2',
                        'path' => 'Food / Vegetables / Orange',
                        'weight' => 3,
                    ],
                ],
            ]
        ];
    }

    /**
     * Test to check if suggestions manager service was registered in container.
     */
    public function testServiceRegistered()
    {
        $this->assertTrue($this->getContainer()->has('ongr_category_manager.suggestions_manager'));
        $this->isInstanceOf(
            'ONGR\\CategoryManagerBundle\\Service\\SuggestionsManager',
            $this->getContainer()->get('ongr_category_manager.suggestions_manager')
        );
    }

    /**
     * Data provider for testGetSuggestions().
     *
     * @return array
     */
    public function getSuggestionsData()
    {
        $out = [];

        // Case #0 vegetables from first root, with sorting.
        $out[] = [
            'vegetables',
            // Entity title.
            'test_root_1',
            // Root node id.
            true,
            [
                'test_id_3',
                'test_id_2',
            ],
        ];
        // Expected result.

        // Case #1 vegetables from second root, with sorting.
        $out[] = [
            'vegetables',
            'test_root_2',
            true,
            ['test_id_4'],
        ];

        // Case #2 vegetables from all roots, with sorting.
        $out[] = [
            'vegetables',
            null,
            true,
            [
                'test_id_4',
                'test_id_3',
                'test_id_2',
            ],
        ];

        // Case #3 vegetables from all roots, without sorting.
        $out[] = [
            'vegetables',
            null,
            false,
            [
                'test_id_2',
                'test_id_3',
                'test_id_4',
            ],
        ];

        return $out;
    }

    /**
     * Test for getSuggestions().
     *
     * @param string $path
     * @param string $rootId
     * @param bool   $sort
     * @param array  $expected
     *
     * @dataProvider getSuggestionsData
     */
    public function testGetSuggestions($path, $rootId, $sort, $expected)
    {
        // Temporary workaround for ESB issue #34 (https://github.com/ongr-io/ElasticsearchBundle/issues/34).
        usleep(50000);
        $repository = $this->getMockBuilder('ONGR\\CategoryManagerBundle\\Repository\\CategoryRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())
            ->method('getTitlePath')
            ->willReturn($path);

        $entityManager = $this->getMock('Doctrine\\ORM\\EntityManagerInterface');
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with('ONGRCategoryManagerBundle:Category')
            ->willReturn($repository);

        $elasticManager = $this->getContainer()->get('es.manager.default');

        $suggestionsManager = new SuggestionsManager(
            $elasticManager,
            $entityManager
        );

        $result = $suggestionsManager->getSuggestions('test_entity_id', $rootId, $sort);
        $this->isInstanceOf('ONGR\\ElasticSearchBundle\\Result\\DocumentIterator', $result);

        $documentIds = [];
        foreach ($result as $document) {
            $documentIds[] = $document->getId();
        }

        if (!$sort) {
            // ES returns in whatever order, so we can't really expect the order beforehand, can we?
            sort($documentIds);
        }
        $this->assertEquals($expected, $documentIds);
    }
}

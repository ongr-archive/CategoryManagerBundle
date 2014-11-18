<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace Fox\CategoryManagerBundle\Tests\Integration\Service;

use Fox\CategoryManagerBundle\Entity\Category;
use Fox\CategoryManagerBundle\Service\SuggestionsManager;
use Fox\DDALBundle\Tests\Integration\BaseTest;

class SuggestionsManagerTest extends BaseTest
{
    /**
     * {@inheritDoc}
     */
    protected function getDocumentsData($driver)
    {
        if ($driver != 'category_manager') {
            return;
        }

        return [
            'NodeModel' => [
                [
                    '_id' => 'test_id_1',
                    'rootId' => 'test_root_1',
                    'path' => 'Food',
                    'weight' => 0
                ],
                [
                    '_id' => 'test_id_2',
                    'rootId' => 'test_root_1',
                    'path' => 'Food / Vegetables',
                    'weight' => 1
                ],
                [
                    '_id' => 'test_id_3',
                    'rootId' => 'test_root_1',
                    'path' => 'Food / Vegetables / Green',
                    'weight' => 2
                ],
                [
                    '_id' => 'test_id_4',
                    'rootId' => 'test_root_2',
                    'path' => 'Food / Vegetables / Orange',
                    'weight' => 3,
                ],
            ]
        ];
    }

    /**
     * Test to check if suggestions manager service was registered in container
     */
    public function testServiceRegistered()
    {
        $this->assertTrue($this->getContainer()->has('fox_category_manager.suggestions_manager'));
        $this->isInstanceOf(
            'Fox\\CategoryManagerBundle\\Service\\SuggestionsManager',
            $this->getContainer()->get('fox_category_manager.suggestions_manager')
        );
    }

    /**
     * Data provider for testGetSuggestions()
     *
     * @return array
     */
    public function getSuggestionsData()
    {
        $out = [];

        // Case #0 vegetables from first root, with sorting
        $out[] = [
            'vegetables', // Entity title
            'test_root_1', // Root node id
            true,
            ['test_id_3', 'test_id_2'] // Expected result
        ];

        // Case #1 vegetables from second root, with sorting
        $out[] = [
            'vegetables',
            'test_root_2',
            true,
            ['test_id_4']
        ];

        // Case #2 vegetables from all roots, with sorting
        $out[] = [
            'vegetables',
            null,
            true,
            ['test_id_4', 'test_id_3', 'test_id_2']
        ];

        // Case #3 vegetables from all roots, without sorting
        $out[] = [
            'vegetables',
            null,
            false,
            ['test_id_2', 'test_id_3', 'test_id_4']
        ];

        return $out;
    }

    /**
     * Test for getSuggestions()
     *
     * @param string $path
     * @param string $rootId
     * @param bool $sort
     * @param array $expected
     *
     * @dataProvider getSuggestionsData
     */
    public function testGetSuggestions($path, $rootId, $sort, $expected)
    {
        $repository = $this->getMockBuilder('Fox\\CategoryManagerBundle\\Repository\\CategoryRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())
            ->method('getTitlePath')
            ->willReturn($path);

        $entityManager = $this->getMock('Doctrine\\ORM\\EntityManagerInterface');
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with('FoxCategoryManagerBundle:Category')
            ->willReturn($repository);

        $suggestionsManager = new SuggestionsManager(
            $this->getModel('nodemodel', 'category_manager'),
            $entityManager
        );

        $result = $suggestionsManager->getSuggestions('test_entity_id', $rootId, $sort);
        $this->isInstanceOf('Fox\\DDALBundle\\ElasticSearch\\ResultsIterator', $result);

        $documentIds = [];
        foreach ($result as $document) {
            $documentIds[] = $document->getDocumentId();
        }

        $this->assertEquals($expected, $documentIds);
    }
}

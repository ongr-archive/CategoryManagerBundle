<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\CategoryManagerBundle\Tests\Functional\Writer;

use ONGR\CategoryManagerBundle\Entity\Category;
use ONGR\CategoryManagerBundle\Provider\CategoryProvider;
use ONGR\CategoryManagerBundle\Repository\CategoryRepository;
use ONGR\CategoryManagerBundle\Service\TransferManager;
use ONGR\CategoryManagerBundle\Tests\Functional\BaseDatabaseTest;

class MySqlCategoryWriterTest extends BaseDatabaseTest
{
    /**
     * Data provider for saveCategories
     *
     * @return array
     */
    public function getSaveCategoriesData()
    {
        $out = [];

        $result = [
            [
                'title' => 'Food',
                'left' => 1,
                'level' => 0,
                'right' => 8,
                '__children' => [
                    [
                        'title' => 'Vegetables',
                        'left' => 2,
                        'level' => 1,
                        'right' => 5,
                        '__children' => [
                            [
                                'title' => 'Carrots',
                                'left' => 3,
                                'level' => 2,
                                'right' => 4,
                                '__children' => [],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Fruits',
                        'left' => 6,
                        'level' => 1,
                        'right' => 7,
                        '__children' => [],
                    ],
                ],
            ],
        ];

        // case #0 flush count left as default
        $categories = [
            ['id' => 'vegetables_id', 'title' => 'Vegetables', 'parent' => 'food_id'],
            ['id' => 'fruits_id', 'title' => 'Fruits', 'parent' => 'food_id'],
            ['id' => 'food_id', 'title' => 'Food', 'parent' => null],
            ['id' => 'carrots_id', 'title' => 'Carrots', 'parent' => 'vegetables_id'],
        ];

        $out[] = [$categories, 'food_id', $result, []];

        //case #1 flush count changed
        $categories = [
            ['id' => 'food_id_2', 'title' => 'Food', 'parent' => null],
            ['id' => 'vegetables_id_2', 'title' => 'Vegetables', 'parent' => 'food_id_2'],
            ['id' => 'carrots_id_2', 'title' => 'Carrots', 'parent' => 'vegetables_id_2'],
            ['id' => 'fruits_id_2', 'title' => 'Fruits', 'parent' => 'food_id_2'],

        ];

        $out[] = [$categories, 'food_id_2', $result, ['flush_count' => 3]];

        return $out;
    }

    /**
     * Test for saveCategories
     *
     * @param array $categories
     * @param string $rootId
     * @param array $result
     * @param array $writerOptions
     *
     * @dataProvider getSaveCategoriesData
     */
    public function testMySqlWriter($categories, $rootId, $result, $writerOptions)
    {
        $em = $this->getEntityManager();

        $provider = new CategoryProvider('ONGR\\CategoryManagerBundle\\Tests\\Integration\\Iterator\\DummyIterator');

        $container = $this->getContainer();

        /* @var TransferManager $manager */
        $manager = $container->get('ongr_category_manager.transfer_manager');
        $manager->addProvider($provider, 'test_provider');

        $entities = [];
        foreach ($categories as $category) {
            $entity = new Category();
            $entity->setId($category['id']);
            $entity->setTitle($category['title']);
            if ($category['parent']) {
                $entity->setParent($em->getReference('ONGRCategoryManagerBundle:Category', $category['parent']));
            }

            $entities[] = $entity;
        }

        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $manager->transfer('test_provider', 'mysql', ['data' => $entities], $writerOptions, $output);

        /* @var CategoryRepository $repo */
        $repo = $em->getRepository('ONGRCategoryManagerBundle:Category');

        $rootNode = $repo->find($rootId);
        $this->assertInstanceOf('ONGR\\CategoryManagerBundle\\Entity\\Category', $rootNode);

        $tree = $repo->childrenHierarchy($rootNode, false, [], true);
        $cleanTree = $this->cleanTree($tree);

        $this->assertEquals($result, $cleanTree);
    }

    /**
     * Test for saveCategories with custom root node
     */
    public function testMysqlWriterCustomRoot()
    {
        $em = $this->getEntityManager();

        $cat1 = new Category();
        $cat1->setTitle('Tet category 1')->setId('test_cat_1');

        $provider = new CategoryProvider('ONGR\\CategoryManagerBundle\\Tests\\Integration\\Iterator\\DummyIterator');

        $container = $this->getContainer();

        /* @var TransferManager $manager */
        $manager = $container->get('ongr_category_manager.transfer_manager');
        $manager->addProvider($provider, 'test_provider');

        $repo = $em->getRepository('ONGRCategoryManagerBundle:Category');
        $rootId = '53f4590d0ccec9.39288089';

        $manager->transfer('test_provider', 'mysql', ['data' => [$cat1]], ['root_node' => $rootId]);

        $category = $repo->find('test_cat_1');
        $this->assertInstanceOf('ONGR\\CategoryManagerBundle\\Entity\\Category', $category);
        $this->assertEquals($rootId, $category->getParent()->getId());
    }

    /**
     * Test for saveCategories with invalid custom root node
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage Invalid root node provided
     */
    public function testMysqlWriterInvalidCustomRoot()
    {
        $provider = new CategoryProvider('ONGR\\CategoryManagerBundle\\Tests\\Integration\\Iterator\\DummyIterator');

        $container = $this->getContainer();

        /* @var TransferManager $manager */
        $manager = $container->get('ongr_category_manager.transfer_manager');
        $manager->addProvider($provider, 'test_provider');

        $manager->transfer('test_provider', 'mysql', ['data' => []], ['root_node' => 'test_bad_id']);
    }

    /**
     * Remove id and root values from generated tree
     *
     * @param array $tree
     * @return array
     */
    protected function cleanTree($tree)
    {
        $cleanTree = [];
        foreach ($tree as $node) {
            unset ($node['id']);
            unset ($node['root']);
            unset ($node['weight']);

            $node['__children'] = $this->cleanTree($node['__children']);
            $cleanTree[] = $node;
        }

        return $cleanTree;
    }
}

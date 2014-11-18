<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\CategoryManagerBundle\Tests\Unit\Service;

use Doctrine\ORM\EntityManagerInterface;
use ONGR\CategoryManagerBundle\Entity\Category;
use ONGR\CategoryManagerBundle\Repository\CategoryRepository;
use ONGR\CategoryManagerBundle\Service\CategoryManager;

class CategoryManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Returns entity manager mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|EntityManagerInterface
     */
    protected function getEntityManager()
    {
        return $this->getMock('Doctrine\\ORM\\EntityManagerInterface');
    }

    /**
     * Return repository mock for category entity
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|CategoryRepository
     */
    protected function getCategoryRepository()
    {
        return $this->getMockBuilder('ONGR\\CategoryManagerBundle\\Repository\\CategoryRepository')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test for getCategory()
     */
    public function testGetCategory()
    {
        $category = new Category();
        $entityManager = $this->getEntityManager();
        $entityManager->expects($this->once())->method('find')->willReturn($category);

        $manager = new CategoryManager($entityManager);
        $this->assertSame($category, $manager->getCategory('foo'));
    }

    /**
     * Test for getCategory() in case document was not found
     */
    public function testGetCategoryNull()
    {
        $manager = new CategoryManager($this->getEntityManager());
        $this->assertNull($manager->getCategory('foo'));
    }

    /**
     * Test for getCategory() in case document was not found and new is created
     */
    public function testGetCategoryNew()
    {
        $manager = new CategoryManager($this->getEntityManager());
        $category = $manager->getCategory('foo', true);

        $this->assertInstanceOf('ONGR\\CategoryManagerBundle\\Entity\\Category', $category);
        $this->assertNull($category->getId());
    }

    /**
     * Test for saveCategory()
     */
    public function testSaveCategory()
    {
        $category = new Category();
        $category->setId('foo');

        $entityManager = $this->getEntityManager();
        $entityManager->expects($this->once())->method('persist')->with($category);
        $entityManager->expects($this->once())->method('flush');

        $manager = new CategoryManager($entityManager);
        $manager->saveCategory($category);
    }

    /**
     * Test for removeCategory()
     */
    public function testRemoveCategory()
    {
        $category = new Category();
        $category->setId('foo');

        $entityManager = $this->getEntityManager();
        $entityManager->expects($this->once())->method('remove')->with($category);
        $entityManager->expects($this->once())->method('flush');

        $manager = new CategoryManager($entityManager);
        $manager->removeCategory($category);
    }

    /**
     * Test for getCategoryTree()
     */
    public function testGetCategoryTree()
    {
        $result = ['tree' => 'test'];
        $parentId = 'test_parent_id';

        $repository = $this->getCategoryRepository();
        $repository->expects($this->once())
            ->method('childrenHierarchy')
            ->with('nodeObject', true, [], true)
            ->willReturn($result);

        $entityManager = $this->getEntityManager();
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with('ONGRCategoryManagerBundle:Category')
            ->willReturn($repository);
        $entityManager->expects($this->once())
            ->method('getReference')
            ->with('ONGRCategoryManagerBundle:Category', $parentId)
            ->willReturn('nodeObject');

        $manager = new CategoryManager($entityManager);
        $this->assertEquals($result, $manager->getCategoryTree($parentId));
    }

    /**
     * Data provider for getRootNodes()
     *
     * @return array
     */
    public function getRootNodesData()
    {
        $out = [];

        $rootNode = new Category();
        $rootNode->setId('test_id');
        $rootNode->setTitle('test_title');

        $nodes = [$rootNode];

        $repository = $this->getCategoryRepository();
        $repository->expects($this->exactly(2))
            ->method('getRootNodes')
            ->willReturn($nodes);

        $entityManager = $this->getEntityManager();
        $entityManager->expects($this->exactly(3))
            ->method('getRepository')
            ->with('ONGRCategoryManagerBundle:Category')
            ->willReturn($repository);

        // case #1 without flatten
        $out[] = [$entityManager, false, $nodes];

        // case #2 with flatten
        $result = [
            'test_id' => [
                'id' => 'test_id',
                'title' => 'test_title',
                'root' => null,
                'path' => null,
            ]
        ];
        $out[] = [$entityManager, true, $result];

        return $out;
    }

    /**
     * Test for getRootNodes()
     *
     * @param \PHPUnit_Framework_MockObject_MockObject|EntityManagerInterface $entityManager
     * @param bool $flatten
     * @param array $result
     *
     * @dataProvider getRootNodesData
     */
    public function testGetRootNodes($entityManager, $flatten, $result)
    {
        $manager = new CategoryManager($entityManager);
        $this->assertEquals($result, $manager->getRootNodes($flatten));
    }

    /**
     * Data provider for moveCategoryData()
     *
     * @return array
     */
    public function getMoveCategoryData()
    {
        $out = [];
        $nodeId = 'test_node_id';
        $parentId = 'test_parent_id';

        // case #1 moved as first child
        $repository = $this->getCategoryRepository();
        $repository->expects($this->once())
            ->method('persistAsFirstChildOf')
            ->with('node', 'parent');
        $repository->expects($this->never())
            ->method('clear');

        $entityManager = $this->getEntityManager();
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with('ONGRCategoryManagerBundle:Category')
            ->willReturn($repository);
        $entityManager->expects($this->once())
            ->method('flush');
        $entityManager->expects($this->exactly(2))
            ->method('getReference')
            ->willReturnMap([
                ['ONGRCategoryManagerBundle:Category', $nodeId, 'node'],
                ['ONGRCategoryManagerBundle:Category', $parentId, 'parent'],
            ]);

        $out[] = [$entityManager, $nodeId, $parentId, 0];

        // case #2 moved as indexed child
        $index = 2;

        $repository = $this->getCategoryRepository();
        $repository->expects($this->once())
            ->method('persistAsFirstChildOf')
            ->with('node', 'parent');
        $repository->expects($this->once())
            ->method('clear');
        $repository->expects($this->once())
            ->method('moveDown')
            ->with('node', $index);

        $entityManager = $this->getEntityManager();
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with('ONGRCategoryManagerBundle:Category')
            ->willReturn($repository);
        $entityManager->expects($this->exactly(2))
            ->method('flush');
        $entityManager->expects($this->exactly(3))
            ->method('getReference')
            ->willReturnMap([
                ['ONGRCategoryManagerBundle:Category', $nodeId, 'node'],
                ['ONGRCategoryManagerBundle:Category', $parentId, 'parent'],
                ['ONGRCategoryManagerBundle:Category', $nodeId, 'node'],
            ]);

        $out[] = [$entityManager, $nodeId, $parentId, $index];

        return $out;
    }

    /**
     * Test for moveCategory()
     *
     * @param \PHPUnit_Framework_MockObject_MockObject|EntityManagerInterface $entityManager $entityManager
     * @param string $nodeId
     * @param string $rootId
     * @param int $index
     *
     * @dataProvider getMoveCategoryData
     */
    public function testMoveCategory($entityManager, $nodeId, $rootId, $index)
    {
        $manager = new CategoryManager($entityManager);
        $manager->moveCategory($nodeId, $rootId, $index);
    }
}

<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\CategoryManagerBundle\Tests\Unit\Modifier;

use ONGR\CategoryManagerBundle\Entity\Category;
use ONGR\CategoryManagerBundle\Document\Node;
use ONGR\CategoryManagerBundle\Modifier\NodeModifier;

/**
 * Class NodeModifierTest.
 */
class NodeModifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for modify().
     */
    public function testModify()
    {
        $document = new Node();

        $entity = new Category();
        $entity->setTitle('Test category');
        $entity->setId('test_id');

        $repository = $this->getMockBuilder('ONGR\\CategoryManagerBundle\\Repository\\CategoryRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())
            ->method('getTitlePath')
            ->with($entity)
            ->willReturn('Parent 1 / Parent 2 / Test category');

        $entityManager = $this->getMock('Doctrine\\ORM\\EntityManagerInterface');
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with('ONGRCategoryManagerBundle:Category')
            ->willReturn($repository);

        $modifier = new NodeModifier($entityManager);
        $modifier->modify($document, $entity);

        $this->assertEquals($entity->getId(), $document->id);
        $this->assertEquals($entity->getRoot(), $document->rootId);
        $this->assertEquals('Parent 1 / Parent 2 / Test category', $document->path);
    }
}

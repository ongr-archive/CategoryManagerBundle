<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace Fox\CategoryManagerBundle\Tests\Functional\Modifier;

use Fox\CategoryManagerBundle\Entity\Category;
use Fox\CategoryManagerBundle\Model\NodeModel;
use Fox\CategoryManagerBundle\Modifier\NodeModifier;

class NodeModifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test for modify()
     */
    public function testModify()
    {
        $document = new NodeModel();

        $entity = new Category();
        $entity->setTitle('Test category');
        $entity->setId('test_id');

        $repository = $this->getMockBuilder('Fox\\CategoryManagerBundle\\Repository\\CategoryRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())
            ->method('getTitlePath')
            ->with($entity)
            ->willReturn('Parent 1 / Parent 2 / Test category');

        $entityManager = $this->getMock('Doctrine\\ORM\\EntityManagerInterface');
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with('FoxCategoryManagerBundle:Category')
            ->willReturn($repository);

        $modifier = new NodeModifier($entityManager);
        $modifier->modify($document, $entity);

        $this->assertEquals($entity->getId(), $document->id);
        $this->assertEquals($entity->getRoot(), $document->rootId);
        $this->assertEquals('Parent 1 / Parent 2 / Test category', $document->path);
    }
}

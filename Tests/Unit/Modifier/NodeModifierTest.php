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
use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;
use ONGR\ConnectionsBundle\Pipeline\Item\ImportItem;

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

        $importItem = new ImportItem($entity, $document);

        $event = new ItemPipelineEvent($importItem);

        $modifier->modify($importItem, $event);

        $this->assertEquals($entity->getId(), $document->id);
        $this->assertEquals($entity->getRoot(), $document->rootId);
        $this->assertEquals('Parent 1 / Parent 2 / Test category', $document->path);

        $badDocument = $this->getMockForAbstractClass('ONGR\ElasticsearchBundle\Document\DocumentInterface');
        $badEntity = [];

        $itemBadDocument = new ImportItem($entity, $badDocument);
        $itemBadEntity = new ImportItem($badEntity, $document);

        $modifier->modify($itemBadDocument, $event);
        $this->assertEquals('Not a Node document', $event->getItemSkip()->getReason());

        $modifier->modify($itemBadEntity, $event);
        $this->assertEquals('Not a Category entity', $event->getItemSkip()->getReason());
    }
}

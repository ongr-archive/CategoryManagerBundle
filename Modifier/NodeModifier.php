<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CategoryManagerBundle\Modifier;

use Doctrine\ORM\EntityManagerInterface;
use ONGR\CategoryManagerBundle\Entity\Category;
use ONGR\CategoryManagerBundle\Document\Node;
use ONGR\CategoryManagerBundle\Repository\CategoryRepository;
use ONGR\ConnectionsBundle\EventListener\AbstractImportModifyEventListener;
use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;
use ONGR\ConnectionsBundle\Pipeline\Item\AbstractImportItem;
use ONGR\ConnectionsBundle\Pipeline\ItemSkipper;

/**
 * Modifier for converting category entity to node document.
 */
class NodeModifier extends AbstractImportModifyEventListener
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var CategoryRepository
     */
    protected $repository;

    /**
     * Constructor for DI.
     *
     * @param EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->entityManager = $manager;
        $this->repository = $manager->getRepository('ONGRCategoryManagerBundle:Category');
    }

    /**
     * {@inheritdoc}
     */
    public function modify(AbstractImportItem $eventItem, ItemPipelineEvent $event)
    {
        /* @var Node $document */
        $document = $eventItem->getDocument();
        if (!$document instanceof Node) {
            ItemSkipper::skip($event, 'Not a Node document');

            return;
        }

        /* @var Category $entity */
        $entity = $eventItem->getEntity();
        if (!$entity instanceof Category) {
            ItemSkipper::skip($event, 'Not a Category entity');

            return;
        }

        $document->id = $entity->getId();
        $document->rootId = $entity->getRoot();
        $document->weight = $entity->getWeight();
        $document->path = $this->repository->getTitlePath($entity);
    }
}

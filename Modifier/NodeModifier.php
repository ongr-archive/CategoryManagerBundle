<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\CategoryManagerBundle\Modifier;

use Doctrine\ORM\EntityManagerInterface;
use ONGR\CategoryManagerBundle\Entity\Category;
use ONGR\CategoryManagerBundle\Model\NodeModel;
use ONGR\CategoryManagerBundle\Repository\CategoryRepository;
use ONGR\ConnectionsBundle\DataCollector\DataCollectorInterface;
use ONGR\ConnectionsBundle\Doctrine\Modifier\ModifierInterface;
use ONGR\DDALBundle\Core\BaseModel;

/**
 * Modifier for converting category entity to node document
 */
class NodeModifier implements ModifierInterface
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
     * Constructor for DI
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
    public function modify(BaseModel $document, $entity, $type = DataCollectorInterface::TYPE_FULL)
    {
        /* @var NodeModel $document */
        /* @var Category $entity */

        $document->id = $entity->getId();
        $document->rootId = $entity->getRoot();
        $document->weight = $entity->getWeight();
        $document->path = $this->repository->getTitlePath($entity);
    }
}

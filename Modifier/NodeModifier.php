<?php

/*
 *************************************************************************
 * NFQ eXtremes CONFIDENTIAL
 * [2013] - [2014] NFQ eXtremes UAB
 * All Rights Reserved.
 *************************************************************************
 * NOTICE:
 * All information contained herein is, and remains the property of NFQ eXtremes UAB.
 * Dissemination of this information or reproduction of this material is strictly forbidden
 * unless prior written permission is obtained from NFQ eXtremes UAB.
 *************************************************************************
 */

namespace Fox\CategoryManagerBundle\Modifier;

use Doctrine\ORM\EntityManagerInterface;
use Fox\CategoryManagerBundle\Entity\Category;
use Fox\CategoryManagerBundle\Model\NodeModel;
use Fox\CategoryManagerBundle\Repository\CategoryRepository;
use Fox\ConnectionsBundle\DataCollector\DataCollectorInterface;
use Fox\ConnectionsBundle\Doctrine\Modifier\ModifierInterface;
use Fox\DDALBundle\Core\BaseModel;

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
        $this->repository = $manager->getRepository('FoxCategoryManagerBundle:Category');
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

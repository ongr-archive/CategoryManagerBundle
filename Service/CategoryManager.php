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

namespace Fox\CategoryManagerBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Fox\CategoryManagerBundle\Entity\Category;
use Fox\CategoryManagerBundle\Repository\CategoryRepository;

/**
 * Provides basic CRUD operations for category manager
 */
class CategoryManager
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Loads category from database
     *
     * @param string $categoryId
     * @param bool   $mustExist
     *
     * @return Category|object
     */
    public function getCategory($categoryId, $mustExist = false)
    {
        $category = $this->entityManager->find('FoxCategoryManagerBundle:Category', $categoryId);

        if ($category === null && $mustExist) {
            $category = new Category();
        }

        return $category;
    }

    /**
     * Saves category to database
     *
     * @param Category $category
     */
    public function saveCategory(Category $category)
    {
        $this->entityManager->persist($category);
        $this->entityManager->flush();
    }

    /**
     * Removes given category
     *
     * @param Category $category
     */
    public function removeCategory(Category $category)
    {
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

    /**
     * Returns formatted category tree
     *
     * @param string $parentId
     *
     * @return array
     */
    public function getCategoryTree($parentId)
    {
        /** @var CategoryRepository $repo */
        $repo = $this->entityManager->getRepository('FoxCategoryManagerBundle:Category');

        $rootNodeReference = $this->entityManager->getReference('FoxCategoryManagerBundle:Category', $parentId);

        // Get tree from provided parent node, with only direct children, no formatting options and include parent node
        $tree = $repo->childrenHierarchy(
            $rootNodeReference,
            true,
            [],
            true
        );

        return $tree;
    }

    /**
     * Returns all root nodes
     *
     * @param bool $flatten
     *
     * @return array
     */
    public function getRootNodes($flatten = false)
    {
        /** @var CategoryRepository $repo */
        $repo = $this->entityManager->getRepository('FoxCategoryManagerBundle:Category');

        return ($flatten) ? $this->flattenNodes($repo->getRootNodes()) : $repo->getRootNodes();
    }

    /**
     * Place category node to new place in a tree
     *
     * @param string $nodeId
     * @param string $parentId
     * @param int $index
     */
    public function moveCategory($nodeId, $parentId, $index = 0)
    {
        /** @var CategoryRepository $repo */
        $repo = $this->entityManager->getRepository('FoxCategoryManagerBundle:Category');

        $nodeReference = $this->entityManager->getReference('FoxCategoryManagerBundle:Category', $nodeId);
        $parentReference = $this->entityManager->getReference('FoxCategoryManagerBundle:Category', $parentId);

        $repo->persistAsFirstChildOf($nodeReference, $parentReference);

        if ($index) {
            // need to finish last operation, otherwise tree will loose references
            $this->entityManager->flush();
            $repo->clear();
            $nodeReference = $this->entityManager->getReference('FoxCategoryManagerBundle:Category', $nodeId);

            $repo->moveDown($nodeReference, $index);
        }

        $this->entityManager->flush();
    }

    /**
     * Returns category tree as flat list
     *
     * @param string $rootId
     * @param string|null $matchRootId
     * @param int $size
     * @param int $from
     * @param bool $flatten
     *
     * @return array
     */
    public function getPlainCategoryTree($rootId, $matchRootId = null, $size = 5, $from = 0, $flatten = false)
    {
        $dql = 'SELECT c FROM FoxCategoryManagerBundle:Category AS c ';
        if ($matchRootId) {
            $dql .= 'LEFT JOIN FoxCategoryManagerBundle:Match match1 WITH c = match1.category ' .
                'LEFT JOIN match1.matchedCategory as match1Category WITH match1Category.root = :matchRoot ' .
                'LEFT JOIN FoxCategoryManagerBundle:Match match2 WITH c = match2.matchedCategory ' .
                'LEFT JOIN match2.category as match2Category WITH match2Category.root = :matchRoot ';
        }
        $dql .= 'WHERE c.root = :rootId ';
        if ($matchRootId) {
            $dql .= 'AND match1Category IS NULL ' .
                'AND match2Category IS NULL ';
        }
        $dql .= 'ORDER BY c.weight DESC, c.left ASC';

        /* @var Query $query */
        $query = $this->entityManager
            ->createQuery($dql)
            ->setParameters(['rootId' => $rootId])
            ->setMaxResults($size)
            ->setFirstResult($from);

        if ($matchRootId) {
            $query->setParameter('matchRoot', $matchRootId);
        }

        return $flatten ? $this->flattenNodes($query->getResult(), false, true) : $query->getResult();
    }

    /**
     * Flatten category nodes to a simple array
     *
     * @param array $nodes
     * @param bool $idAsKey
     * @param bool $includePath
     *
     * @return array
     */
    protected function flattenNodes($nodes, $idAsKey = true, $includePath = false)
    {
        $out = [];

        /* @var CategoryRepository $repo */
        $repo = $this->entityManager->getRepository('FoxCategoryManagerBundle:Category');

        /** @var Category $node */
        foreach ($nodes as $node) {
            $element = [
                'id' => $node->getId(),
                'title' => $node->getTitle(),
                'root' => $node->getRoot(),
                'path' => $includePath ? $repo->getTitlePath($node) : null,
            ];

            if ($idAsKey) {
                $out[$node->getId()] = $element;
            } else {
                $out[] = $element;
            }
        }

        return $out;
    }
}

<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\CategoryManagerBundle\Writer;

use Doctrine\ORM\EntityManager;
use ONGR\CategoryManagerBundle\Iterator\CategoryIteratorInterface;
use ONGR\CategoryManagerBundle\Entity\Category;
use ONGR\CategoryManagerBundle\Repository\CategoryRepository;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MySqlCategoryWriter - implements CategoryWriterInterface.
 */
class MySqlCategoryWriter implements CategoryWriterInterface
{
    /**
     * Default number of categories to persist in a single flush operation
     */
    const CATEGORY_FLUSH_COUNT = 100;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var CategoryRepository
     */
    protected $repository;

    /**
     * @var array
     */
    protected $delayed;

    /**
     * @var array
     */
    protected $savedIds;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var ProgressHelper
     */
    protected $progress;

    /**
     * Constructor for DI.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        $this->repository = $entityManager->getRepository('ONGRCategoryManagerBundle:Category');

        $this->progress = new ProgressHelper();
    }

    /**
     * {@inheritdoc}
     */
    public function saveCategories(CategoryIteratorInterface $categories, array $options = [], $output = null)
    {
        $flushCount = (isset($options['flush_count'])) ? $options['flush_count'] : self::CATEGORY_FLUSH_COUNT;
        $rootNode = (isset($options['root_node'])) ? $options['root_node'] : null;
        $this->output = $output;

        if ($rootNode) {
            $rootNode = $this->repository->find($rootNode);
            if (!$rootNode) {
                throw new \LogicException('Invalid root node provided');
            }
        }

        $this->delayed = [];
        $this->savedIds = [];

        $counter = 0;

        $this->outputLine("Starting category import, flush count: $flushCount");
        $this->output && $this->progress->start($this->output);

        /* @var Category $category */
        foreach ($categories as $category) {
            if ($this->persistCategory($category, $rootNode)) {
                continue;
            }

            $counter++;
            $counter += $this->persistDelayedChildren($category);

            if ($counter && $counter % $flushCount == 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
                $counter = 0;
            }
        }

        if ($counter && $counter % $flushCount != 0) {
            $this->entityManager->flush();
            $this->entityManager->clear();
        }

        $this->output && $this->progress->finish();
        $this->outputLine('Categories imported: ' . count($this->savedIds));
        $this->outputLine('Finished. ' . count($this->delayed) . ' parents where missing');
    }

    /**
     * Persist category or add it to delayed list.
     *
     * @param Category $category
     * @param Category $rootNode
     *
     * @return bool
     */
    protected function persistCategory($category, $rootNode)
    {
        $parent = $category->getParent();

        if ($parent) {
            if (!in_array($parent->getId(), $this->savedIds)) {
                $this->delayed[$parent->getId()][] = $category;

                return true;
            }

            // Recreate reference as entity manager could be cleared at this moment.
            $category->setParent(
                $this->entityManager->getReference('ONGRCategoryManagerBundle:Category', $parent->getId())
            );

            $this->repository->persistAsLastChild($category);
        } elseif ($rootNode) {
            $this->repository->persistAsFirstChildOf($category, $rootNode);
        } else {
            $this->entityManager->persist($category);
        }

        $this->output && $this->progress->advance();

        $this->savedIds[] = $category->getId();

        return false;
    }

    /**
     * Persist and reconnect delayed nodes as a parent is now available.
     *
     * @param Category $category
     *
     * @return int
     */
    protected function persistDelayedChildren(Category $category)
    {
        $count = 0;

        if (!isset($this->delayed[$category->getId()])) {
            return $count;
        }

        $children = $this->delayed[$category->getId()];
        unset($this->delayed[$category->getId()]);

        /* @var Category $childCategory */
        foreach ($children as $childCategory) {
            $count++;

            $this->repository->persistAsLastChild($childCategory);

            $count += $this->persistDelayedChildren($childCategory);

            $this->savedIds[] = $childCategory->getId();

            $this->output && $this->progress->advance();
        }

        return $count;
    }

    /**
     * Writes test to output interface if available.
     *
     * @param string $text
     */
    protected function outputLine($text)
    {
        if ($this->output) {
            $this->output->writeln($text);
        }
    }
}

<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace Fox\CategoryManagerBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Fox\CategoryManagerBundle\Entity\Match;
use Fox\CategoryManagerBundle\Repository\CategoryRepository;

/**
 * Provides management of category matches
 */
class MatchManager
{
    /**
     * Default number of matches to persist in a single flush operation
     */
    const MULTI_MATCH_FLUSH_COUNT = 100;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepo;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->entityManager = $manager;
        $this->categoryRepo = $manager->getRepository('FoxCategoryManagerBundle:Category');
    }

    /**
     * Create a match between two categories, Returns matched category path
     *
     * @param string $categoryId
     * @param string $matchId
     *
     * @return string
     */
    public function match($categoryId, $matchId)
    {
        $references = [
            $categoryId => $this->entityManager->getReference('FoxCategoryManagerBundle:Category', $categoryId),
            $matchId => $this->entityManager->getReference('FoxCategoryManagerBundle:Category', $matchId),
        ];

        $matchPath = $this->categoryRepo->getTitlePath($references[$matchId]);

        // Ids are sorted that duplicate matches like a->b|b->a will not be created
        ksort($references);

        $match = new Match();
        $match->setCategory(array_shift($references));
        $match->setMatchedCategory(array_shift($references));

        $this->entityManager->persist($match);
        $this->entityManager->flush();

        return $matchPath;
    }

    /**
     * Returns provided node matches with tree based on provided root
     *
     * @param string $nodeId
     * @param string $rootId
     * @param bool $flatten
     *
     * @return array
     */
    public function getMatches($nodeId, $rootId, $flatten = false)
    {
        $dql = 'SELECT m FROM FoxCategoryManagerBundle:Match AS m ' .
            'LEFT JOIN m.category AS c ' .
            'LEFT JOIN m.matchedCategory AS mc ' .
            'WHERE ( m.category = :nodeId AND mc.root = :matchedRootId) ' .
            'OR ( m.matchedCategory = :nodeId AND c.root = :matchedRootId)';

        $matches = $this->entityManager
            ->createQuery($dql)
            ->setParameters(['nodeId' => $nodeId, 'matchedRootId' => $rootId])
            ->getResult();

        if ($flatten) {
            return $this->flatten($matches, $nodeId);
        }

        return $matches;
    }

    /**
     * Deletes existing match
     *
     * @param string $categoryId
     * @param string $matchId
     */
    public function removeMatch($categoryId, $matchId)
    {
        $dql = 'DELETE FoxCategoryManagerBundle:Match AS m ' .
            'WHERE (m.category = :categoryId AND m.matchedCategory = :matchId) ' .
            'OR (m.category = :matchId AND m.matchedCategory = :categoryId)';

        $this->entityManager
            ->createQuery($dql)
            ->setParameters(['categoryId' => $categoryId, 'matchId' => $matchId])
            ->execute();
    }

    /**
     * Add multi matches from provided iterator
     *
     * @param \Iterator $iterator
     * @param int $skipEntries
     * @param int $flushCount
     */
    public function matchMultiple($iterator, $skipEntries = 0, $flushCount = self::MULTI_MATCH_FLUSH_COUNT)
    {
        $skipped = 0;
        $persisted = 0;

        foreach ($iterator as $matchId) {
            if ($skipEntries > $skipped) {
                $skipped++;
                continue;
            }

            // Ids are sorted that duplicate matches like a->b|b->a will not be created
            sort($matchId);
            $match = new Match();
            $match->setCategory($this->entityManager->getReference('FoxCategoryManagerBundle:Category', $matchId[0]));
            $match->setMatchedCategory(
                $this->entityManager->getReference('FoxCategoryManagerBundle:Category', $matchId[1])
            );

            $this->entityManager->persist($match);
            $persisted++;

            if ($persisted && $persisted % $flushCount == 0) {
                $this->entityManager->flush();
            }
        }

        if ($persisted && $persisted % $flushCount != 0) {
            $this->entityManager->flush();
        }
    }

    /**
     * Returns flattened categories based on provided nodeId. Reference side is independent.
     *
     * @param array $matches
     * @param string $nodeId
     *
     * @return array
     */
    protected function flatten($matches, $nodeId)
    {
        $flattenedMatches = [];

        /* @var Match $match */
        foreach ($matches as $match) {
            if ($match->getCategory()->getId() == $nodeId) {
                $node = $match->getMatchedCategory();
            } else {
                $node = $match->getCategory();
            }

            $flattenedMatches[$node->getId()] = [
                'id' => $node->getId(),
                'path' => $this->categoryRepo->getTitlePath($node),
            ];
        }

        return $flattenedMatches;
    }
}

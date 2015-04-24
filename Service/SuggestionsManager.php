<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CategoryManagerBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use ONGR\CategoryManagerBundle\Repository\CategoryRepository;
use ONGR\ElasticsearchBundle\DSL\Search;
use ONGR\ElasticsearchBundle\DSL\Query;
use ONGR\ElasticsearchBundle\DSL\Filter;
use ONGR\ElasticsearchBundle\ORM\Manager;
use ONGR\ElasticsearchBundle\DSL\Sort;
use ONGR\ElasticsearchBundle\Result\DocumentIterator;

/**
 * Class SuggestionsManager. You know, for suggestions...
 */
class SuggestionsManager
{
    /**
     * @var Manager
     */
    protected $elasticManager;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var CategoryRepository
     */
    protected $repository;

    /**
     * Constructor.
     *
     * @param Manager                $elasticManager
     * @param EntityManagerInterface $entityManager
     */
    public function __construct($elasticManager, $entityManager)
    {
        $this->elasticManager = $elasticManager;
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository('ONGRCategoryManagerBundle:Category');
    }

    /**
     * Returns fuzzy matches from es.
     *
     * @param string      $categoryId
     * @param string|null $rootId
     * @param bool        $sort
     *
     * @return DocumentIterator
     */
    public function getSuggestions($categoryId, $rootId = null, $sort = true)
    {
        $reference = $this->entityManager->getReference('ONGRCategoryManagerBundle:Category', $categoryId);
        $path = $this->repository->getTitlePath($reference);

        $search = new Search();

        if ($rootId) {
            $search->addQuery(new Query\MatchQuery('rootId', $rootId));
        }

        $search->addQuery(new Query\FuzzyQuery('path', $path));

        $search->setFields(['id', 'rootId', 'path']);

        if ($sort) {
            $sorting = new Sort\Sort('weight');
            $sorting->setOrder(Sort\Sort::ORDER_DESC);
            $sorting->setMode('min');
            $search->addSort($sorting);
        }

        $elasticRepository = $this->elasticManager->getRepository('ONGRCategoryManagerBundle:Node');

        $result = $elasticRepository->execute($search);

        return $result;
    }
}

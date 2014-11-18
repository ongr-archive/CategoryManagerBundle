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
use Fox\CategoryManagerBundle\Repository\CategoryRepository;
use Fox\DDALBundle\Core\Filter;
use Fox\DDALBundle\Core\Query;
use Fox\DDALBundle\Core\SessionModel;
use Fox\DDALBundle\ElasticSearch\ResultsIterator;
use Fox\DDALBundle\Query\Filters\Sort\Sort;

class SuggestionsManager
{
    /**
     * @var SessionModel
     */
    protected $sessionModel;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var CategoryRepository
     */
    protected $repository;

    /**
     * Constructor
     *
     * @param SessionModel $sessionModel
     * @param EntityManagerInterface $entityManager
     */
    public function __construct($sessionModel, $entityManager)
    {
        $this->sessionModel = $sessionModel;
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository('FoxCategoryManagerBundle:Category');
    }

    /**
     * Returns fuzzy matches from es
     *
     * @param string $categoryId
     * @param string|null $rootId
     * @param bool $sort
     *
     * @return ResultsIterator
     */
    public function getSuggestions($categoryId, $rootId = null, $sort = true)
    {
        $reference = $this->entityManager->getReference('FoxCategoryManagerBundle:Category', $categoryId);
        $path = $this->repository->getTitlePath($reference);

        $query = new Query();

        if ($rootId) {
            $query->constraints->setTerms("rootId", $rootId);
        }

        // @TODO: Decide which fuzzy approach to use
        // $query->filter->setShould('path', $path, Filter::CONDITION_FUZZY);

        $query->filter->setFuzzyLikeThis(['path'], $path);
        $query->addFields(['id', 'rootId', 'path']);

        if ($sort) {
            $sorting = new Sort();
            $sorting->setField('weight');
            $sorting->setOrder(Sort::ORDER_DESC);
            $query->filter->sortConditions->add('weight_sort', $sorting);
        }

        $result = $this->sessionModel->findDocuments($query);

        return $result;
    }
}

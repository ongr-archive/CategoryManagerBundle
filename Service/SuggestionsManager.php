<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\CategoryManagerBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use ONGR\CategoryManagerBundle\Repository\CategoryRepository;
use ONGR\DDALBundle\Core\Filter;
use ONGR\DDALBundle\Core\Query;
use ONGR\DDALBundle\Core\SessionModel;
use ONGR\DDALBundle\ElasticSearch\ResultsIterator;
use ONGR\DDALBundle\Query\Filters\Sort\Sort;

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
        $this->repository = $entityManager->getRepository('ONGRCategoryManagerBundle:Category');
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
        $reference = $this->entityManager->getReference('ONGRCategoryManagerBundle:Category', $categoryId);
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

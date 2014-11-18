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

namespace Fox\CategoryManagerBundle\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Fox\CategoryManagerBundle\Iterator\CategoryIteratorInterface;
use Fox\CategoryManagerBundle\Iterator\EntityManagerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Universal category data provider
 */
class CategoryProvider implements CategoryProviderInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $iteratorClass;

    /**
     * Constructor
     *
     * @param string $iteratorClass
     */
    public function __construct($iteratorClass)
    {
        $this->iteratorClass = $iteratorClass;
    }

    /**
     * Sets the Container associated with this Controller.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Returns entity manager
     *
     * @return EntityManagerInterface
     * @throws \LogicException
     */
    protected function getEntityManager()
    {
        if ($this->container === null) {
            throw new \LogicException("Provider must have service container injected.");
        }

        return $this->container->get('fox_category_manager.entity_manager');
    }

    /**
     * {@inheritDoc}
     */
    public function getCategories(array $options = [])
    {
        if (!class_exists($this->iteratorClass)) {
            throw new \LogicException("Iterator class '{$this->iteratorClass}' not found.");
        }

        /** @var CategoryIteratorInterface $iterator */
        $iterator = new $this->iteratorClass;
        $iterator->setOptions($options);

        if ($iterator instanceof EntityManagerAwareInterface) {
            $iterator->setEntityManager($this->getEntityManager());
        }

        return $iterator;
    }
}

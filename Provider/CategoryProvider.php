<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CategoryManagerBundle\Provider;

use Doctrine\ORM\EntityManagerInterface;
use ONGR\CategoryManagerBundle\Iterator\CategoryIteratorInterface;
use ONGR\CategoryManagerBundle\Iterator\EntityManagerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Universal category data provider.
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
     * Constructor.
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
     * @param ContainerInterface $container A ContainerInterface instance.
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Returns entity manager.
     *
     * @return EntityManagerInterface
     * @throws \LogicException
     */
    protected function getEntityManager()
    {
        if ($this->container === null) {
            throw new \LogicException('Provider must have service container injected.');
        }

        return $this->container->get('ongr_category_manager.entity_manager');
    }

    /**
     * {@inheritdoc}
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

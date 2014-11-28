<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\CategoryManagerBundle\Tests\Unit\Provider;

use Doctrine\ORM\EntityManagerInterface;
use ONGR\CategoryManagerBundle\Iterator\CategoryIteratorInterface;
use ONGR\CategoryManagerBundle\Iterator\EntityManagerAwareInterface;

/**
 * Dummy category provider for testing purposes.
 */
class DummyProvider extends \ArrayIterator implements CategoryIteratorInterface, EntityManagerAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        // Do nothing.
    }

    /**
     * {@inheritdoc}
     */
    public function setEntityManager(EntityManagerInterface $entityManager)
    {
        // Do nothing.
    }
}

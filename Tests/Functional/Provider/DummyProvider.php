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

namespace Fox\CategoryManagerBundle\Tests\Functional\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Fox\CategoryManagerBundle\Iterator\CategoryIteratorInterface;
use Fox\CategoryManagerBundle\Iterator\EntityManagerAwareInterface;

/**
 * Dummy category provider for testing purposes
 */
class DummyProvider extends \ArrayIterator implements CategoryIteratorInterface, EntityManagerAwareInterface
{
    /**
     * {@inheritDoc}
     */
    public function setOptions(array $options)
    {
        // Do nothing
    }

    /**
     * {@inheritDoc}
     */
    public function setEntityManager(EntityManagerInterface $entityManager)
    {
        // Do nothing
    }
}

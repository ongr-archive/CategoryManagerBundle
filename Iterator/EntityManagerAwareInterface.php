<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\CategoryManagerBundle\Iterator;

use Doctrine\ORM\EntityManagerInterface;

/**
 * EntityManager aware interface
 */
interface EntityManagerAwareInterface
{
    /**
     * Sets entity manager
     *
     * @param EntityManagerInterface $entityManager
     */
    public function setEntityManager(EntityManagerInterface $entityManager);
}

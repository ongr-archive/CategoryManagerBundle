<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CategoryManagerBundle\EventListener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use ONGR\CategoryManagerBundle\Entity\Category;

/**
 * Event listener for doctrine prePersist event, used for assigning id to entity if non is available.
 */
class CategoryUpdateListener
{
    /**
     * If category entity has no id assigned assign one.
     *
     * @param LifecycleEventArgs $event
     */
    public function prePersist(LifecycleEventArgs $event)
    {
        $entity = $event->getObject();

        if ($entity instanceof Category) {
            if (!$entity->getId()) {
                $entity->setId(uniqid('', true));
            }
        }
    }
}

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

namespace Fox\CategoryManagerBundle\EventListener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Fox\CategoryManagerBundle\Entity\Category;

/**
 * Event listener for doctrine prePersist event, used for assigning id to entity if non is available
 */
class CategoryUpdateListener
{
    /**
     * If category entity has no id assigned assign one
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

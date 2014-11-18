<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\CategoryManagerBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use ONGR\CategoryManagerBundle\Entity\Category;

/**
 * Event listener for monitoring category removals
 */
class CategoryRemoveListener implements EventSubscriber
{
    /**
     * Categories that are marked for removal
     *
     * @var array
     */
    protected $removedCategories;

    /**
     * Collects id from all categories marked for removal
     *
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $unitOfWork = $args->getEntityManager()->getUnitOfWork();

        // Only interested in Category entities that was marked for removal
        foreach ($unitOfWork->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof Category) {
                $this->removedCategories[] = $entity->getId();
            }
        }
    }

    /**
     * Removes matches that relate in any side with removed categories
     *
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if (empty($this->removedCategories)) {
            return;
        }

        $dql = 'DELETE ONGRCategoryManagerBundle:Match AS m ' .
            'WHERE (m.category IN (:categories)) ' .
            'OR (m.matchedCategory IN (:categories))';

        $manager = $args->getEntityManager();
        $manager->createQuery($dql)->setParameter('categories', $this->removedCategories)->execute();

        // Clear array, because listener state is held
        $this->removedCategories = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'onFlush',
            'postFlush',
        ];
    }
}

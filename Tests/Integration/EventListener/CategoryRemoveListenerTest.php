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

namespace Fox\CategoryManagerBundle\Tests\Integration\EventListener;

use Fox\CategoryManagerBundle\Entity\Match;
use Fox\CategoryManagerBundle\Tests\Integration\BaseDatabaseTest;

class CategoryRemoveListenerTest extends BaseDatabaseTest
{
    /**
     * Test to check if matches are removed after category removal
     */
    public function testMatchRemoval()
    {
        $this->setUpMatches();

        $em = $this->getEntityManager();
        $matches = $em->getRepository('FoxCategoryManagerBundle:Match')->findAll();

        $this->assertCount(4, $matches);

        $em->remove($em->getReference('FoxCategoryManagerBundle:Category', '53f4590d0ccec9.39288089'));
        $em->flush();

        $matches = $em->getRepository('FoxCategoryManagerBundle:Match')->findAll();
        $this->assertCount(1, $matches);
        $this->assertEquals('53f45a96c733f6.75280890', $matches[0]->getCategory()->getId());
        $this->assertEquals('53f45cc07f55d2.92980246', $matches[0]->getMatchedCategory()->getId());
    }

    /**
     * Creates matches for testing
     */
    protected function setUpMatches()
    {
        $em = $this->getEntityManager();
        $entityName = 'FoxCategoryManagerBundle:Category';

        $matches = [
            '53f4590d0ccec9.39288089' => '53f45a96c733f6.75280890',
            '53f45cc07f55d2.92980246' => '53f4590d0ccec9.39288089',
            '53f45976ef75c9.78862935' => '53f45cc07f55d2.92980246',
            '53f45a96c733f6.75280890' => '53f45cc07f55d2.92980246',
        ];

        foreach ($matches as $categoryId => $matchedId) {
            $match = new Match();
            $match
                ->setCategory($em->getReference($entityName, $categoryId))
                ->setMatchedCategory($em->getReference($entityName, $matchedId));

            $em->persist($match);
        }

        $em->flush();
    }
}

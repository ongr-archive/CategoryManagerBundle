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

namespace Fox\CategoryManagerBundle\Tests\Integration\Entity;

use Fox\CategoryManagerBundle\Entity\Category;
use Fox\CategoryManagerBundle\Entity\Match;
use Fox\CategoryManagerBundle\Tests\Integration\BaseDatabaseTest;

class MatchTest extends BaseDatabaseTest
{
    /**
     * Test if we are able to persist match entity
     */
    public function testIntegration()
    {
        $category = new Category();
        $category->setId('foo');
        $category->setTitle('Foo');

        $category2 = new Category();
        $category2->setId('bar');
        $category2->setTitle('Bar');

        $match = new Match();
        $match->setCategory($category);
        $match->setMatchedCategory($category2);

        $entityManager = $this->getEntityManager();
        $entityManager->persist($category);
        $entityManager->persist($category2);
        $entityManager->persist($match);
        $entityManager->flush();

        $entityManager->refresh($match);

        $this->assertEquals('Foo', $match->getCategory()->getTitle());
    }
}

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
use Fox\CategoryManagerBundle\Tests\Integration\BaseDatabaseTest;

/**
 * Tests if DoctrineExtensions integration works
 */
class DoctrineExtensionsTest extends BaseDatabaseTest
{
    /**
     * Test if entity can be saved to database
     */
    public function testPersistEntity()
    {
        $category = new Category();
        $category->setId('foo');
        $category->setTitle('Test');

        $entityManager = $this->getEntityManager();
        $entityManager->persist($category);
        $entityManager->flush();

        $entityManager->refresh($category);
        $this->assertEquals('Test', $category->getTitle());
    }
}

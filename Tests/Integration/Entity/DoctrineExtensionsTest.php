<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
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

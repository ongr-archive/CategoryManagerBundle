<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CategoryManagerBundle\Tests\Functional\Entity;

use ONGR\CategoryManagerBundle\Entity\Category;
use ONGR\CategoryManagerBundle\Tests\Functional\AbstractDatabaseTestCase;

/**
 * Tests if DoctrineExtensions integration works.
 */
class DoctrineExtensionsTest extends AbstractDatabaseTestCase
{
    /**
     * Test if entity can be saved to database.
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

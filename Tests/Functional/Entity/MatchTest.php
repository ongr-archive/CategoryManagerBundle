<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CategoryManagerBundle\Functional\Integration\Entity;

use ONGR\CategoryManagerBundle\Entity\Category;
use ONGR\CategoryManagerBundle\Entity\Match;
use ONGR\CategoryManagerBundle\Tests\Functional\BaseDatabaseTest;

class MatchTest extends BaseDatabaseTest
{
    /**
     * Test if we are able to persist match entity.
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

<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CategoryManagerBundle\Tests\Functional\EventListener;

use ONGR\CategoryManagerBundle\Entity\Match;
use ONGR\CategoryManagerBundle\Tests\Functional\AbstractDatabaseTestCase;

class CategoryRemoveListenerTest extends AbstractDatabaseTestCase
{
    /**
     * Test to check if matches are removed after category removal.
     */
    public function testMatchRemoval()
    {
        $this->setUpMatches();

        $em = $this->getEntityManager();
        $matches = $em->getRepository('ONGRCategoryManagerBundle:Match')->findAll();

        $this->assertCount(4, $matches);

        $em->remove($em->getReference('ONGRCategoryManagerBundle:Category', '53f4590d0ccec9.39288089'));
        $em->flush();

        $matches = $em->getRepository('ONGRCategoryManagerBundle:Match')->findAll();
        $this->assertCount(1, $matches);
        $this->assertEquals('53f45a96c733f6.75280890', $matches[0]->getCategory()->getId());
        $this->assertEquals('53f45cc07f55d2.92980246', $matches[0]->getMatchedCategory()->getId());
    }

    /**
     * Creates matches for testing.
     */
    protected function setUpMatches()
    {
        $em = $this->getEntityManager();
        $entityName = 'ONGRCategoryManagerBundle:Category';

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

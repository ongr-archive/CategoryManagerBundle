<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\CategoryManagerBundle\Tests\Functional\Service;

use ONGR\CategoryManagerBundle\Entity\Category;
use ONGR\CategoryManagerBundle\Tests\Functional\BaseDatabaseTest;

class CategoryUpdateListenerTest extends BaseDatabaseTest
{
    /**
     * Data provider for prePersist()
     *
     * @return array
     */
    public function getPrePersistData()
    {
        $out = [];

        // case #0 assigned id
        $entity = new Category();
        $entity->setTitle('Test');
        $entity->setId('test_id');

        $out[] = [$entity, 'Test', 'test_id'];

        // case #1 entity without id
        $entity = new Category();
        $entity->setTitle('Test 2');
        $out[] = [$entity, 'Test 2', null];

        return $out;
    }

    /**
     * Test for IdGenerator pre persist method
     *
     * @param Category $category
     * @param string $title
     * @param string|null $id
     *
     * @dataProvider getPrePersistData
     */
    public function testPrePersist($category, $title, $id)
    {
        $em = $this->getEntityManager();

        $em->persist($category);
        $em->flush();
        $em->clear();

        $repo = $em->getRepository('ONGRCategoryManagerBundle:Category');

        $node = $repo->findOneByTitle($title);

        $this->assertInstanceOf('ONGR\\CategoryManagerBundle\\Entity\\Category', $node);

        if ($id) {
            $this->assertEquals($id, $node->getId());
        } else {
            $this->assertNotEmpty($node->getId());
        }
    }
}

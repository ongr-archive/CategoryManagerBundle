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

namespace ONGR\CategoryManagerBundle\Tests\Functional\Service;

use ONGR\CategoryManagerBundle\Entity\Category;
use ONGR\CategoryManagerBundle\Tests\Functional\AbstractDatabaseTestCase;

class CategoryUpdateListenerTest extends AbstractDatabaseTestCase
{
    /**
     * Data provider for prePersist().
     *
     * @return array
     */
    public function getPrePersistData()
    {
        $out = [];

        // Case #0 assigned id.
        $entity = new Category();
        $entity->setTitle('Test');
        $entity->setId('test_id');

        $out[] = [$entity, 'Test', 'test_id'];

        // Case #1 entity without id.
        $entity = new Category();
        $entity->setTitle('Test 2');
        $out[] = [$entity, 'Test 2', null];

        return $out;
    }

    /**
     * Test for IdGenerator pre persist method.
     *
     * @param Category    $category
     * @param string      $title
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

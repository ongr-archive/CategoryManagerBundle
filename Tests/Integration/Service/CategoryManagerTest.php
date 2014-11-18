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

namespace Fox\CategoryManagerBundle\Tests\Integration\Service;

use Fox\CategoryManagerBundle\Entity\Match;
use Fox\CategoryManagerBundle\Service\CategoryManager;
use Fox\CategoryManagerBundle\Tests\Integration\BaseDatabaseTest;

class CategoryManagerTest extends BaseDatabaseTest
{
    /**
     * Data provider for testPlainTree()
     *
     * @return array
     */
    public function getPlainCategoryTreeData()
    {
        $out = [];

        // case #0 start from first result, limit to 2 entries
        $out[] = ['53f4590d0ccec9.39288089', 0, 2, [
            [
                'id' => '53f4590d0ccec9.39288089',
                'title' => 'Kiteboarding',
                'root' => '53f4590d0ccec9.39288089',
                'path' => 'Kiteboarding',
            ],
            [
                'id' => '53f45976ef75c9.78862935',
                'title' => 'Kites',
                'root' => '53f4590d0ccec9.39288089',
                'path' => 'Kiteboarding / Kites',
            ],
        ]];

        // case #1 start from 3rd result, limit to 3 entries
        $out[] = ['53f4590d0ccec9.39288089', 2, 3, [
            [
                'id' => '53f45979139606.24866601',
                'title' => 'Kiteboards',
                'root' => '53f4590d0ccec9.39288089',
                'path' => 'Kiteboarding / Kiteboards',
            ],
            [
                'id' => '53f4597d709631.23677997',
                'title' => 'Small',
                'root' => '53f4590d0ccec9.39288089',
                'path' => 'Kiteboarding / Kiteboards / Small',
            ],
            [
                'id' => '53f45a8e831510.19801507',
                'title' => 'Large',
                'root' => '53f4590d0ccec9.39288089',
                'path' => 'Kiteboarding / Kiteboards / Large',
            ],
        ]];

        // case #2 start from 3rd result, limit to zero entries
        $out[] = ['53f4590d0ccec9.39288089', 2, 0, []];

        return $out;
    }

    /**
     * Test for getPlainCategoryTree()
     *
     * @param string $rootId
     * @param int $size
     * @param int $from
     * @param array $expectedResult
     *
     * @dataProvider getPlainCategoryTreeData
     */
    public function testPlainTree($rootId, $from, $size, $expectedResult)
    {
        /* @var CategoryManager $manager */
        $manager = $this->getContainer()->get('fox_category_manager.category_manager');

        $result = $manager->getPlainCategoryTree($rootId, null, $size, $from, true);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test for getPlainCategoryTree() result as entities array
     */
    public function testPlainTreeEntities()
    {
        /* @var CategoryManager $manager */
        $manager = $this->getContainer()->get('fox_category_manager.category_manager');

        $result = $manager->getPlainCategoryTree('53f4590d0ccec9.39288089', null, 1, 0, false);

        $this->assertCount(1, $result);
        $this->isInstanceOf($result[0], 'Fox\CategoryManagerBundle\Entity\Category');

        $this->assertEquals('53f4590d0ccec9.39288089', $result[0]->getId());
        $this->assertEquals('Kiteboarding', $result[0]->getTitle());
    }

    public function testPlainTreeFiltered()
    {
        /* @var CategoryManager $manager */
        $manager = $this->getContainer()->get('fox_category_manager.category_manager');
        $entityManager = $this->getEntityManager();

        $match = new Match();
        $match->setCategory(
            $entityManager->getReference('FoxCategoryManagerBundle:Category', '53f45976ef75c9.78862935')
        );
        $match->setMatchedCategory(
            $entityManager->getReference('FoxCategoryManagerBundle:Category', '53f45cc07f55d2.92980246')
        );

        $entityManager->persist($match);
        $entityManager->flush();

        $result = $manager->getPlainCategoryTree('53f4590d0ccec9.39288089', '53f45a96c733f6.75280890', 2, 0, true);
        $expectedResult = [
            [
                'id' => '53f4590d0ccec9.39288089',
                'title' => 'Kiteboarding',
                'root' => '53f4590d0ccec9.39288089',
                'path' => 'Kiteboarding',
            ],
            [
                'id' => '53f45979139606.24866601',
                'title' => 'Kiteboards',
                'root' => '53f4590d0ccec9.39288089',
                'path' => 'Kiteboarding / Kiteboards',
            ],
        ];

        $this->assertEquals($expectedResult, $result);
    }
}

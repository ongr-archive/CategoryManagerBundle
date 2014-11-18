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

namespace Fox\CategoryManagerBundle\Tests\Integration\Repository;

use Fox\CategoryManagerBundle\Tests\Integration\BaseDatabaseTest;
use Fox\CategoryManagerBundle\Repository\CategoryRepository;

class CategoryRepositoryTest extends BaseDatabaseTest
{
    /**
     * Data provider for testGetTitlePath
     *
     * @return array
     */
    public function getTitlePathData()
    {
        $out = [];

        // Case #0 default delimiter
        $out[] = ['53f4597d709631.23677997', null, "Kiteboarding / Kiteboards / Small"];

        // Case #1 custom delimiter
        $out[] = ['53f4597d709631.23677997', '.', "Kiteboarding.Kiteboards.Small"];

        return $out;
    }

    /**
     * Test for getTittlePath()
     *
     * @param string $id
     * @param string|null $delimiter
     * @param string $expectedResult
     *
     * @dataProvider getTitlePathData
     */
    public function testGetTitlePath($id, $delimiter, $expectedResult)
    {
        $manager = $this->getEntityManager();

        /* @var CategoryRepository $repo */
        $repo = $manager->getRepository('FoxCategoryManagerBundle:Category');

        $reference = $manager->getReference('FoxCategoryManagerBundle:Category', $id);

        if ($delimiter) {
            $result = $repo->getTitlePath($reference, $delimiter);
        } else {
            $result = $repo->getTitlePath($reference);
        }

        $this->assertEquals($expectedResult, $result);
    }
}

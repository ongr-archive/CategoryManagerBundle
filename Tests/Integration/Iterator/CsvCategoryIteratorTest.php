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

namespace Fox\CategoryManagerBundle\Tests\Integration\Iterator;

use Fox\CategoryManagerBundle\Iterator\CsvCategoryIterator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CsvCategoryIteratorTest extends WebTestCase
{
    /**
     * Returns service container
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return self::createClient()->getContainer();
    }

    /**
     * Test for iterator
     */
    public function testIterator()
    {
        $iterator = new CsvCategoryIterator();
        $iterator->setOptions(['file' => __DIR__ . '/../Fixtures/categories.csv']);
        $iterator->setEntityManager($this->getContainer()->get('fox_category_manager.entity_manager'));

        $titles = [];

        foreach ($iterator as $id => $category) {

            $this->assertInstanceOf('Fox\\CategoryManagerBundle\\Entity\\Category', $category);
            $titles[$id] = $category->getTitle();
        }

        $expectedTitles = [
            '123' => 'Test Category',
            '456' => 'Second',
            '233' => 'Child',
        ];

        $this->assertEquals($expectedTitles, $titles);
    }

    /**
     * Test for iterator with bad csv provided
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage Unsupported field
     */
    public function testInvalidIterator()
    {
        $iterator = new CsvCategoryIterator();
        $iterator->setOptions(['file' => __DIR__ . '/../Fixtures/invalid_categories.csv']);
        $iterator->setEntityManager($this->getContainer()->get('fox_category_manager.entity_manager'));

        $iterator->rewind();
    }

    /**
     * Test for setOptions() in case invalid options passed
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Option 'file' must be set
     */
    public function testSetOptionsException()
    {
        $iterator = new CsvCategoryIterator();
        $iterator->setOptions([]);
    }
}

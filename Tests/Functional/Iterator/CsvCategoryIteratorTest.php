<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\CategoryManagerBundle\Tests\Functional\Iterator;

use ONGR\CategoryManagerBundle\Iterator\CsvCategoryIterator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CsvCategoryIteratorTest extends WebTestCase
{
    /**
     * Returns service container.
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return self::createClient()->getContainer();
    }

    /**
     * Test for iterator.
     */
    public function testIterator()
    {
        $iterator = new CsvCategoryIterator();
        $iterator->setOptions(['file' => __DIR__ . '/../Fixtures/categories.csv']);
        $iterator->setEntityManager($this->getContainer()->get('ongr_category_manager.entity_manager'));

        $titles = [];

        foreach ($iterator as $id => $category) {
            $this->assertInstanceOf('ONGR\\CategoryManagerBundle\\Entity\\Category', $category);
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
     * Test for iterator with bad csv provided.
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage Unsupported field
     */
    public function testInvalidIterator()
    {
        $iterator = new CsvCategoryIterator();
        $iterator->setOptions(['file' => __DIR__ . '/../Fixtures/invalid_categories.csv']);
        $iterator->setEntityManager($this->getContainer()->get('ongr_category_manager.entity_manager'));

        $iterator->rewind();
    }

    /**
     * Test for setOptions() in case invalid options passed.
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

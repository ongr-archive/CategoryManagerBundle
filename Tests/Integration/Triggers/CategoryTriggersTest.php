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

namespace Fox\CategoryManagerBundle\Tests\Integration\Triggers;

use Fox\CategoryManagerBundle\Entity\Category;
use Fox\ConnectionsBundle\Sync\Trigger\TriggersManager;
use Fox\ConnectionsBundle\Tests\Integration\TestBase;
use Gedmo\DoctrineExtensions;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Output\NullOutput;

class CategoryTriggersTest extends TestBase
{
    /**
     * {@inheritDoc}
     */
    protected function setup()
    {
        parent::setUp();

        DoctrineExtensions::registerAnnotations();
    }

    /**
     * Test for triggers integration
     */
    public function testTriggers()
    {
        $this->prepare();

        $entityManager = $this->getEntityManager();
        $connection = $this->getConnection();

        // Insert
        $category = new Category();
        $category
            ->setId('foo')
            ->setTitle('Foo');
        $entityManager->persist($category);
        $entityManager->flush();

        // Update
        $category->setTitle('Bar');
        $entityManager->persist($category);
        $entityManager->flush();

        // Delete
        $entityManager->remove($category);
        $entityManager->flush();

        $actualRecords = $connection->fetchAll("SELECT * FROM `fox_sync_jobs`");
        $this->compareRecords(
            [
                [
                    'id' => '1',
                    'document_id' => 'foo',
                    'type' => 'C'
                ],
                [
                    'id' => '2',
                    'document_id' => 'foo',
                    'type' => 'U'
                ],
                [
                    'id' => '3',
                    'document_id' => 'foo',
                    'type' => 'U'
                ],
                [
                    'id' => '4',
                    'document_id' => 'foo',
                    'type' => 'D'
                ],
            ],
            $actualRecords,
            false
        );
    }

    /**
     * Test for triggers integration
     */
    public function testTriggers2()
    {
        $this->prepare();

        $entityManager = $this->getEntityManager();
        $connection = $this->getConnection();

        // Insert
        $foo = new Category();
        $foo->setId('foo')->setTitle('Foo');
        $entityManager->persist($foo);
        $category = new Category();
        $category->setId('bar')->setTitle('Bar')->setParent($foo);
        $entityManager->persist($category);
        $category = new Category();
        $category->setId('baz')->setTitle('Baz');
        $entityManager->persist($category);
        $entityManager->flush();

        // Update
        $foo->setTitle('Updated Foo');
        $entityManager->persist($foo);
        $entityManager->flush();

        $actualRecords = $connection->fetchAll("SELECT * FROM `fox_sync_jobs`");

        $this->compareRecords(
            [
                // Create
                ['document_id' => 'foo', 'type' => 'C'],
                ['document_id' => 'bar', 'type' => 'C'],
                ['document_id' => 'baz', 'type' => 'C'],
                // Post-create update by DoctrineExtensions
                ['document_id' => 'foo', 'type' => 'U'],
                ['document_id' => 'bar', 'type' => 'U'],
                ['document_id' => 'baz', 'type' => 'U'],
                // Real update
                ['document_id' => 'foo', 'type' => 'U'],
                ['document_id' => 'bar', 'type' => 'U'],
            ],
            $actualRecords,
            false
        );
    }

    /**
     * Prepares triggers
     */
    protected function prepare()
    {
        /** @var $triggersManager TriggersManager */
        $triggersManager = $this->getServiceContainer()->get('fox_connections.triggers_manager');
        $this->importData('triggers/job.sql');
        $this->importData('category_table.sql');
        $triggersManager->createTriggers(new ProgressHelper(), new NullOutput());
    }
}

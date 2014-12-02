<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\CategoryManagerBundle\Tests\Functional\Triggers;

use ONGR\CategoryManagerBundle\Entity\Category;
use ONGR\CategoryManagerBundle\Tests\Functional\DoctrineTriggerTestBase;
use ONGR\ConnectionsBundle\Sync\DiffProvider\Trigger\TriggersManager;
use Gedmo\DoctrineExtensions;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\NullOutput;

class CategoryTriggersTest extends DoctrineTriggerTestBase
{
    /**
     * {@inheritdoc}
     */
    protected function setup()
    {
        parent::setUp();

        DoctrineExtensions::registerAnnotations();
    }

    /**
     * Test for triggers integration.
     */
    public function testTriggers()
    {
        $this->prepare();

        $entityManager = $this->getEntityManager();
        $connection = $this->getConnection();

        // Insert.
        $category = new Category();
        $category
            ->setId('foo')
            ->setTitle('Foo');
        $entityManager->persist($category);
        $entityManager->flush();

        // Update.
        $category->setTitle('Bar');
        $entityManager->persist($category);
        $entityManager->flush();

        // Delete.
        $entityManager->remove($category);
        $entityManager->flush();

        $actualRecords = $connection->fetchAll('SELECT * FROM `ongr_sync_jobs`');
        $this->compareRecords(
            [
                [
                    'id' => '1',
                    'document_id' => 'foo',
                    'type' => 'C',
                ],
                [
                    'id' => '2',
                    'document_id' => 'foo',
                    'type' => 'U',
                ],
                [
                    'id' => '3',
                    'document_id' => 'foo',
                    'type' => 'U',
                ],
                [
                    'id' => '4',
                    'document_id' => 'foo',
                    'type' => 'D',
                ],
            ],
            $actualRecords,
            false
        );
    }

    /**
     * Test for triggers integration.
     */
    public function testTriggers2()
    {
        $this->prepare();

        $entityManager = $this->getEntityManager();
        $connection = $this->getConnection();

        // Insert.
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

        // Update.
        $foo->setTitle('Updated Foo');
        $entityManager->persist($foo);
        $entityManager->flush();

        $actualRecords = $connection->fetchAll('SELECT * FROM `ongr_sync_jobs`');

        $this->compareRecords(
            [
                // Create.
                ['document_id' => 'foo', 'type' => 'C'],
                ['document_id' => 'bar', 'type' => 'C'],
                ['document_id' => 'baz', 'type' => 'C'],
                // Post-create update by DoctrineExtensions.
                ['document_id' => 'foo', 'type' => 'U'],
                ['document_id' => 'bar', 'type' => 'U'],
                ['document_id' => 'baz', 'type' => 'U'],
                // Real update.
                ['document_id' => 'foo', 'type' => 'U'],
                ['document_id' => 'bar', 'type' => 'U'],
            ],
            $actualRecords,
            false
        );
    }

    /**
     * Prepares triggers.
     */
    protected function prepare()
    {
        /** @var $triggersManager TriggersManager */
        $triggersManager = $this->getServiceContainer()->get('ongr_connections.triggers_manager');
        $this->importData('triggers/job.sql');
        $this->importData('category_table.sql');
        if (class_exists('\Symfony\Component\Console\Helper\ProgressBar')) {
            $progress = new ProgressBar(new NullOutput());
        } else {
            // This is for backwards compatibility only.
            // @codeCoverageIgnoreStart
            $progress = new ProgressHelper();
            // @codeCoverageIgnoreEnd
        }
        $triggersManager->createTriggers($progress, new NullOutput());
    }
}
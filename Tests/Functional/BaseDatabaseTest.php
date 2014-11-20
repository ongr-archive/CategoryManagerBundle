<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\CategoryManagerBundle\Tests\Functional;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\DoctrineExtensions;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base test with database integration.
 */
abstract class BaseDatabaseTest extends WebTestCase
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Sets up required info before each test.
     */
    public function setUp()
    {
        $vendorDir = $this->getContainer()->get('kernel')->getRootDir() . '/../../vendor';
        AnnotationRegistry::registerFile(
            $vendorDir . '/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php'
        );
        DoctrineExtensions::registerAnnotations();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $connection = $entityManager->getConnection();

        $params = $connection->getParams();
        $name = $connection->getDatabasePlatform()->quoteSingleIdentifier($params['dbname']);
        unset($params['dbname']);

        $tmpConnection = DriverManager::getConnection($params);
        $tmpConnection->getSchemaManager()->dropAndCreateDatabase($name);
        $tmpConnection->close();

        $this->executeSqlFile($connection, __DIR__ . '/Fixtures/category_table.sql');
    }

    /**
     * Returns service container, creates new if it does not exist.
     *
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        if ($this->container === null) {
            $this->container = self::createClient()->getContainer();
        }

        return $this->container;
    }

    /**
     * Returns entity manager.
     *
     * @return EntityManagerInterface
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * Executes an SQL file.
     *
     * @param Connection $conn
     * @param string     $file
     */
    protected function executeSqlFile(Connection $conn, $file)
    {
        $sql = file_get_contents($file);
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    }

    /**
     * Deletes the database.
     */
    public static function tearDownAfterClass()
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::createClient()->getContainer()->get('doctrine.orm.entity_manager');
        $connection = $entityManager->getConnection();
        $connection->getSchemaManager()->dropDatabase($connection->getParams()['dbname']);
    }
}

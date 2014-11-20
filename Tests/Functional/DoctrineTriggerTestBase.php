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
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

/**
 * Proudly stolen from FOX :(. Will go to fixtures soon.
 */
abstract class DoctrineTriggerTestBase extends WebTestCase
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Sets up required info before each test.
     */
    protected function setUp()
    {
        AnnotationRegistry::registerFile(
            'vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php'
        );
        /** @var EntityManager $entityManager */
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $params = $connection->getParams();
        $name = $connection->getParams()['dbname'];
        unset($params['dbname']);
        $tmpConnection = DriverManager::getConnection($params);
        $name = $tmpConnection->getDatabasePlatform()->quoteSingleIdentifier($name);
        try {
            $tmpConnection->getSchemaManager()->dropDatabase($name);
        } catch (\Exception $ex) {
            // Pew.
        }
        $tmpConnection->getSchemaManager()->createDatabase($name);
        $tmpConnection->close();
    }

    /**
     * Deletes all data after each test.
     */
    protected function tearDown()
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $name = $connection->getParams()['dbname'];
        $name = $connection->getSchemaManager()->getDatabasePlatform()->quoteSingleIdentifier($name);
        $connection->getSchemaManager()->dropDatabase($name);
    }

    /**
     * Gets entity manager.
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->getServiceContainer()->get('doctrine')->getManager();
    }

    /**
     * Imports sql file for testing.
     *
     * @param string $file
     */
    public function importData($file)
    {
        $this->executeSqlFile($this->getConnection(), 'Tests/Functional/Fixtures/' . $file);
    }

    /**
     * Returns service container, creates new if it does not exist.
     *
     * @return ContainerInterface
     */
    protected function getServiceContainer()
    {
        if ($this->container === null) {
            $this->container = self::createClient()->getContainer();
        }

        return $this->container;
    }

    /**
     * Gets Connection from container.
     *
     * @return Connection
     */
    protected function getConnection()
    {
        /** @var $doctrine RegistryInterface */
        $doctrine = $this->getServiceContainer()->get('doctrine');

        return $doctrine->getConnection();
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
     * Compares two sets of records (suited for sync jobs data comparison).
     *
     * @param array $expectedRecords
     * @param array $actualRecords
     * @param bool  $checkAllFields
     */
    protected function compareRecords($expectedRecords, $actualRecords, $checkAllFields = true)
    {
        $ignoredFields = ['timestamp'];

        if (!$checkAllFields && isset($expectedRecords[0]) && isset($actualRecords[0])) {
            $ignoredFields = array_merge(
                $ignoredFields,
                array_diff(array_keys($actualRecords[0]), array_keys($expectedRecords[0]))
            );
        }

        // Remove ignored values.
        foreach ($actualRecords as &$actualRecord) {
            foreach ($ignoredFields as $field) {
                unset($actualRecord[$field]);
            }
        }

        $this->assertEquals($expectedRecords, $actualRecords);
    }
}

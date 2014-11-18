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

namespace Fox\CategoryManagerBundle\Tests\Functional\Command;

use Fox\CategoryManagerBundle\Command\ImportMatchesCommand;
use Fox\CategoryManagerBundle\Service\MatchManager;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ImportMatchesCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider for testCommand()
     *
     * @return array
     */
    public function getTestCommandData()
    {
        $out = [];

        // Case #0 full option list
        $out[] = [
            ['file' => 'Tests/Integration/Fixtures/matches.csv', '--flush-count' => '10', '--headless' => true],
            0,
            10,
        ];

        // Case #1 default flush count
        $out[] = [
            ['file' => 'Tests/Integration/Fixtures/matches.csv', '--headless' => true],
            0,
            MatchManager::MULTI_MATCH_FLUSH_COUNT,
        ];

        // Case #2 all default options
        $out[] = [
            ['file' => 'Tests/Integration/Fixtures/matches.csv'],
            1,
            MatchManager::MULTI_MATCH_FLUSH_COUNT,
        ];

        return $out;
    }

    /**
     * Test for category-manager:matches:import command
     *
     * @param array $options
     * @param int $skipLines
     * @param int $flushCount
     *
     * @dataProvider getTestCommandData
     */
    public function testCommand($options, $skipLines, $flushCount)
    {
        $manager = $this->getMockBuilder('Fox\\CategoryManagerBundle\\Service\\MatchManager')
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects($this->once())
            ->method('matchMultiple')
            ->with($this->isInstanceOf('\Iterator'), $skipLines, $flushCount);

        $container = new ContainerBuilder();
        $container->set('fox_category_manager.match_manager', $manager);

        $command = new ImportMatchesCommand();
        $command->setContainer($container);

        $application = new Application();
        $application->add($command);

        $commandForTesting = $application->find('fox:category-manager:matches:import');
        $commandTester = new CommandTester($commandForTesting);

        $options['command'] = $commandForTesting->getName();

        $commandTester->execute($options);
    }

    /**
     * Test for category-manager:matches:import command with no file provided
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Not enough arguments
     */
    public function testCommandWithoutFile()
    {
        $command = new ImportMatchesCommand();

        $application = new Application();
        $application->add($command);

        $commandForTesting = $application->find('fox:category-manager:matches:import');
        $commandTester = new CommandTester($commandForTesting);

        $commandTester->execute(['command' => $commandForTesting->getName()]);
    }
}

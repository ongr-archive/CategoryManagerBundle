<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CategoryManagerBundle\Tests\Unit\Command;

use ONGR\CategoryManagerBundle\Command\ImportMatchesCommand;
use ONGR\CategoryManagerBundle\Service\MatchManager;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ImportMatchesCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider for testCommand().
     *
     * @return array
     */
    public function getTestCommandData()
    {
        $out = [];

        // Case #0 full option list.
        $out[] = [
            ['file' => 'Tests/Functional/Fixtures/matches.csv', '--flush-count' => '10', '--headless' => true],
            0,
            10,
        ];

        // Case #1 default flush count.
        $out[] = [
            ['file' => 'Tests/Functional/Fixtures/matches.csv', '--headless' => true],
            0,
            MatchManager::MULTI_MATCH_FLUSH_COUNT,
        ];

        // Case #2 all default options.
        $out[] = [
            ['file' => 'Tests/Functional/Fixtures/matches.csv'],
            1,
            MatchManager::MULTI_MATCH_FLUSH_COUNT,
        ];

        return $out;
    }

    /**
     * Test for category-manager:matches:import command.
     *
     * @param array $options
     * @param int   $skipLines
     * @param int   $flushCount
     *
     * @dataProvider getTestCommandData
     */
    public function testCommand($options, $skipLines, $flushCount)
    {
        $manager = $this->getMockBuilder('ONGR\\CategoryManagerBundle\\Service\\MatchManager')
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects($this->once())
            ->method('matchMultiple')
            ->with($this->isInstanceOf('\Iterator'), $skipLines, $flushCount);

        $container = new ContainerBuilder();
        $container->set('ongr_category_manager.match_manager', $manager);

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
     * Test for category-manager:matches:import command with no file provided.
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

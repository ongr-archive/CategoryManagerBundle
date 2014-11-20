<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\CategoryManagerBundle\Tests\Unit\Command;

use ONGR\CategoryManagerBundle\Command\ImportCsvCommand;
use ONGR\CategoryManagerBundle\Writer\MySqlCategoryWriter;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ImportCsvCommandTest extends \PHPUnit_Framework_TestCase
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
            ['file' => 'test_file', '--flush-count' => 10, '--root-node' => 'test_root'],
            ['file' => 'test_file'],
            ['flush_count' => 10, 'root_node' => 'test_root'],
        ];

        // Case #1 default flush count.
        $out[] = [
            ['file' => 'test_file', '--root-node' => 'test_root'],
            ['file' => 'test_file'],
            ['flush_count' => MySqlCategoryWriter::CATEGORY_FLUSH_COUNT, 'root_node' => 'test_root'],
        ];

        // Case #2 default options.
        $out[] = [
            ['file' => 'test_file'],
            ['file' => 'test_file'],
            ['flush_count' => MySqlCategoryWriter::CATEGORY_FLUSH_COUNT, 'root_node' => null],
        ];

        return $out;
    }

    /**
     * Test for category-manager:import:csv command.
     *
     * @param array $options
     * @param array $providerOptions
     * @param array $writerOptions
     *
     * @dataProvider getTestCommandData
     */
    public function testCommand($options, $providerOptions, $writerOptions)
    {
        $manager = $this->getMock('ONGR\\CategoryManagerBundle\\Service\\TransferManager');
        $manager->expects($this->once())
            ->method('transfer')
            ->with(
                'csv',
                'mysql',
                $providerOptions,
                $writerOptions,
                $this->isInstanceOf('Symfony\Component\Console\Output\OutputInterface')
            );

        $container = new ContainerBuilder();
        $container->set('ongr_category_manager.transfer_manager', $manager);

        $command = new ImportCsvCommand();
        $command->setContainer($container);

        $application = new Application();
        $application->add($command);

        $commandForTesting = $application->find('fox:category-manager:import:csv');
        $commandTester = new CommandTester($commandForTesting);

        $options['command'] = $commandForTesting->getName();

        $commandTester->execute($options);
    }

    /**
     * Test for category-manager:import:csv command with no file provided.
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Not enough arguments
     */
    public function testCommandWithoutFile()
    {
        $command = new ImportCsvCommand();

        $application = new Application();
        $application->add($command);

        $commandForTesting = $application->find('fox:category-manager:import:csv');
        $commandTester = new CommandTester($commandForTesting);

        $commandTester->execute(['command' => $commandForTesting->getName()]);
    }
}

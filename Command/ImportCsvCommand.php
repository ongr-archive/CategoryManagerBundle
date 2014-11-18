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

namespace Fox\CategoryManagerBundle\Command;

use Fox\CategoryManagerBundle\Writer\MySqlCategoryWriter;
use Fox\CategoryManagerBundle\Service\TransferManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCsvCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('fox:category-manager:import:csv')
            ->setDescription('Import categories from CSV file')
            ->addArgument('file', InputArgument::REQUIRED, 'CSV file to import')
            ->addOption(
                'flush-count',
                'c',
                InputOption::VALUE_REQUIRED,
                'Persist operations count before a single flush',
                MySqlCategoryWriter::CATEGORY_FLUSH_COUNT
            )->addOption(
                'root-node',
                'r',
                InputOption::VALUE_REQUIRED,
                'Import all categories under provided root node id'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $providerOptions = ['file' => $input->getArgument('file')];
        $writerOptions = [
            'root_node' => $input->getOption('root-node'),
            'flush_count' => $input->getOption('flush-count'),
        ];

        /* @var TransferManager $transferManager */
        $transferManager = $this->getContainer()->get('fox_category_manager.transfer_manager');
        $transferManager->transfer('csv', 'mysql', $providerOptions, $writerOptions, $output);
    }
}

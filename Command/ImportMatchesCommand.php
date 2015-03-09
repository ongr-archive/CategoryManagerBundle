<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CategoryManagerBundle\Command;

use ONGR\CategoryManagerBundle\Service\MatchManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Import matches from CSV file.
 */
class ImportMatchesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('fox:category-manager:matches:import')
            ->setDescription('Import matches from CSV file')
            ->addArgument('file', InputArgument::REQUIRED, 'CSV file to import')
            ->addOption('headless', null, InputOption::VALUE_NONE, 'CSV file has no header')
            ->addOption(
                'flush-count',
                null,
                InputOption::VALUE_REQUIRED,
                'How many persists to do before a single flush',
                MatchManager::MULTI_MATCH_FLUSH_COUNT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fileName = $input->getArgument('file');

        $iterator = new \SplFileObject($fileName);
        $iterator->setFlags(
            \SplFileObject::READ_CSV |
            \SplFileObject::READ_AHEAD |
            \SplFileObject::DROP_NEW_LINE |
            \SplFileObject::SKIP_EMPTY
        );

        /* @var MatchManager $matchManager */
        $matchManager = $this->getContainer()->get('ongr_category_manager.match_manager');
        $matchManager->matchMultiple(
            $iterator,
            $input->getOption('headless') ? 0 : 1,
            $input->getOption('flush-count')
        );
    }
}

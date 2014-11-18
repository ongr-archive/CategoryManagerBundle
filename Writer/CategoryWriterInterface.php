<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\CategoryManagerBundle\Writer;

use ONGR\CategoryManagerBundle\Iterator\CategoryIteratorInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Category data writer interface
 */
interface CategoryWriterInterface
{
    /**
     * Save provided categories
     *
     * @param CategoryIteratorInterface $categories
     * @param array $options
     * @param OutputInterface|null $output
     */
    public function saveCategories(CategoryIteratorInterface $categories, array $options = [], $output = null);
}

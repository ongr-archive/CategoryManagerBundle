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

namespace Fox\CategoryManagerBundle\Writer;

use Fox\CategoryManagerBundle\Iterator\CategoryIteratorInterface;
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

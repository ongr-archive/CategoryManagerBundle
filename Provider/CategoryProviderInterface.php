<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\CategoryManagerBundle\Provider;

use ONGR\CategoryManagerBundle\Iterator\CategoryIteratorInterface;

/**
 * Category data provider interface
 */
interface CategoryProviderInterface
{
    /**
     * Returns provider's categories
     *
     * @param array $options
     *
     * @return CategoryIteratorInterface
     */
    public function getCategories(array $options = []);
}

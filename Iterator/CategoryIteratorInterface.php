<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace Fox\CategoryManagerBundle\Iterator;

/**
 * Category list iterator
 */
interface CategoryIteratorInterface extends \Iterator
{
    /**
     * Sets options
     *
     * @param array $options
     */
    public function setOptions(array $options);
}

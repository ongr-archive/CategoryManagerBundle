<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace Fox\CategoryManagerBundle\Repository;

use Fox\CategoryManagerBundle\Entity\Category;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * Repository class for Category entity
 */
class CategoryRepository extends NestedTreeRepository
{
    /**
     * Returns category path as a string separated by provided delimiter
     *
     * @param Category $category
     * @param string $delimiter
     *
     * @return string
     */
    public function getTitlePath($category, $delimiter = ' / ')
    {
        $pathNodes = $this->getPath($category);
        $path = [];

        /* @var Category $node */
        foreach ($pathNodes as $node) {
            $path[] = $node->getTitle();
        }

        return implode($delimiter, $path);
    }
}

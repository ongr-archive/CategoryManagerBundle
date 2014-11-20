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

namespace ONGR\CategoryManagerBundle\Repository;

use ONGR\CategoryManagerBundle\Entity\Category;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * Repository class for Category entity.
 */
class CategoryRepository extends NestedTreeRepository
{
    /**
     * Returns category path as a string separated by provided delimiter.
     *
     * @param Category $category
     * @param string   $delimiter
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

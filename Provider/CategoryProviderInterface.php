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

namespace Fox\CategoryManagerBundle\Provider;

use Fox\CategoryManagerBundle\Iterator\CategoryIteratorInterface;

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

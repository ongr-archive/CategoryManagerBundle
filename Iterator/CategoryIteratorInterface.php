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

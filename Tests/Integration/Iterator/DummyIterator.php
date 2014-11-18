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

namespace Fox\CategoryManagerBundle\Tests\Integration\Iterator;

use Fox\CategoryManagerBundle\Iterator\CategoryIteratorInterface;

/**
 * Class for a dummy category iterator
 */
class DummyIterator implements CategoryIteratorInterface
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var int
     */
    protected $index = 0;

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        $this->data = $options['data'];
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->data[$this->index];
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        ++$this->index;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->index;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return isset($this->data[$this->index]);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->index = 0;
    }
}

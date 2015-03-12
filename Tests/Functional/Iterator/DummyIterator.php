<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CategoryManagerBundle\Tests\Functional\Iterator;

use ONGR\CategoryManagerBundle\Iterator\CategoryIteratorInterface;

/**
 * Class for a dummy category iterator.
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

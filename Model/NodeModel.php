<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\CategoryManagerBundle\Model;

use ONGR\DDALBundle\Core\BaseModel;

/**
 * This class provides data structure for node model in ES
 */
class NodeModel extends BaseModel
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $rootId;

    /**
     * @var string
     */
    public $path;

    /**
     * @var float
     */
    public $weight;
}

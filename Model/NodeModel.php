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

namespace Fox\CategoryManagerBundle\Model;

use Fox\DDALBundle\Core\BaseModel;

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

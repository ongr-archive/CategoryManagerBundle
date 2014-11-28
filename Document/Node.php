<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\CategoryManagerBundle\Document;

use ONGR\ElasticsearchBundle\Document\DocumentTrait;
use ONGR\ElasticsearchBundle\Document\DocumentInterface;
use ONGR\ElasticsearchBundle\Annotation as ES;

/**
 * This class provides data structure for node model in ES.
 *
 * @ES\Document(type="node")
 */
class Node implements DocumentInterface
{
    use DocumentTrait;

    /**
     * @var string
     *
     * @ES\Property(type="string", name="root_id")
     */
    public $rootId;

    /**
     * @var string
     *
     * @ES\Property(type="string", name="path")
     */
    public $path;

    /**
     * @var float
     *
     * @ES\Property(type="float", name="weight")
     */
    public $weight;
}

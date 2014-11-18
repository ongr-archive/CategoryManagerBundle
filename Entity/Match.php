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

namespace Fox\CategoryManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity for matched categories record
 *
 * @ORM\Entity()
 * @ORM\Table(name="category_matches")
 */
class Match
{
    /**
     * @var Category
     *
     * @ORM\Id
     *
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(name="category", referencedColumnName="id")
     */
    private $category;

    /**
     * @var Category
     *
     * @ORM\Id
     *
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(name="matched_category", referencedColumnName="id")
     */
    private $matchedCategory;

    /**             
     * Sets category
     * 
     * @param Category $category
     * 
     * @return Match
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;
        
        return $this;
    }

    /**
     * Returns category
     *
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Sets matched category
     *
     * @param Category $category
     *
     * @return Match
     */
    public function setMatchedCategory(Category $category)
    {
        $this->matchedCategory = $category;

        return $this;
    }

    /**
     * Returns matched category
     *
     * @return Category
     */
    public function getMatchedCategory()
    {
        return $this->matchedCategory;
    }
}

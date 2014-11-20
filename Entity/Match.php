<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\CategoryManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity for matched categories record.
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
     * Sets category.
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
     * Returns category.
     *
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Sets matched category.
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
     * Returns matched category.
     *
     * @return Category
     */
    public function getMatchedCategory()
    {
        return $this->matchedCategory;
    }
}

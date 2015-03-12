<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CategoryManagerBundle\Iterator;

use Doctrine\ORM\EntityManagerInterface;
use ONGR\CategoryManagerBundle\Entity\Category;
use Symfony\Component\DependencyInjection\Container;

/**
 * Category iterator for CSV files.
 */
class CsvCategoryIterator implements CategoryIteratorInterface, EntityManagerAwareInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var \SplFileObject
     */
    private $iterator;

    /**
     * @var array
     */
    private $header;

    /**
     * @var array
     */
    private $customFields = ['parent'];

    /**
     * {@inheritdoc}
     */
    public function setEntityManager(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Sets options.
     *
     * @param array $options
     *
     * @throws \InvalidArgumentException
     */
    public function setOptions(array $options)
    {
        if (!isset($options['file'])) {
            throw new \InvalidArgumentException("Option 'file' must be set.");
        }

        $this->options = $options;
    }

    /**
     * Returns file iterator.
     *
     * @return \SplFileObject
     */
    protected function getIterator()
    {
        if ($this->iterator === null) {
            $this->iterator = new \SplFileObject($this->options['file']);
            $this->iterator->setFlags(
                \SplFileObject::READ_CSV |
                \SplFileObject::SKIP_EMPTY |
                \SplFileObject::READ_AHEAD
            );
        }

        return $this->iterator;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $current = $this->getIterator()->current();
        $current = $this->createEntity(array_combine($this->header, $current));

        return $current;
    }

    /**
     * Converts array to entity.
     *
     * @param array $data
     *
     * @return Category
     */
    protected function createEntity($data)
    {
        $category = new Category();

        foreach ($data as $field => $value) {
            if ($field == 'parent') {
                if (empty($value)) {
                    $value = null;
                } else {
                    $value = $this->entityManager->getReference('ONGRCategoryManagerBundle:Category', $value);
                }
            }

            $setter = 'set' . Container::camelize($field);
            $category->{$setter}($value);
        }

        return $category;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->getIterator()->next();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        $current = $this->current();

        return $current->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->getIterator()->valid() && $this->current() !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $iterator = $this->getIterator();
        $iterator->rewind();

        $fields = $iterator->current();
        $this->checkHeader($fields);

        $this->header = $fields;

        $iterator->next();
    }

    /**
     * Check if csv fields are supported.
     *
     * @param array $fields
     *
     * @throws \LogicException
     */
    protected function checkHeader($fields)
    {
        $availableFields = $this->entityManager
            ->getClassMetadata('ONGR\CategoryManagerBundle\Entity\Category')
            ->getFieldNames();
        $availableFields = array_merge($availableFields, $this->customFields);

        foreach ($fields as $headerField) {
            if (!in_array($headerField, $availableFields)) {
                throw new \LogicException("Unsupported field '{$headerField}' or no CSV header provided");
            }
        }
    }
}

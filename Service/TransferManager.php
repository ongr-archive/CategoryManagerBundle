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

namespace Fox\CategoryManagerBundle\Service;

use Fox\CategoryManagerBundle\Provider\CategoryProviderInterface;
use Fox\CategoryManagerBundle\Writer\CategoryWriterInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Service responsible for transferring categories from providers to writers
 */
class TransferManager
{
    /**
     * @var array
     */
    protected $providers = [];

    /**
     * @var array
     */
    protected $writers = [];

    /**
     * Add a category data provider
     *
     * @param CategoryProviderInterface $provider
     * @param string $id
     */
    public function addProvider(CategoryProviderInterface $provider, $id)
    {
        $this->providers[$id] = $provider;
    }

    /**
     * Add a category data writer
     *
     * @param CategoryWriterInterface $writer
     * @param string $id
     */
    public function addWriter(CategoryWriterInterface $writer, $id)
    {
        $this->writers[$id] = $writer;
    }

    /**
     * Return a category data provider by its registered id
     *
     * @param string $id
     *
     * @return CategoryProviderInterface
     *
     * @throws \InvalidArgumentException
     */
    protected function getProvider($id)
    {
        if (empty($this->providers[$id])) {
            throw new \InvalidArgumentException("Category data provider '{$id}' is not registered.");
        }

        return $this->providers[$id];
    }

    /**
     * Return a category data writer by its registered id
     *
     * @param string $id
     *
     * @return CategoryWriterInterface
     *
     * @throws \InvalidArgumentException
     */
    protected function getWriter($id)
    {
        if (empty($this->writers[$id])) {
            throw new \InvalidArgumentException("Category data writer '{$id}' is not registered.");
        }

        return $this->writers[$id];
    }

    /**
     * Transfer categories data from provider to writer
     *
     * @param string $providerId
     * @param string $writerId
     * @param array $providerOptions
     * @param array $writerOptions
     * @param OutputInterface|null $output
     *
     * @throws \InvalidArgumentException
     */
    public function transfer(
        $providerId,
        $writerId,
        array $providerOptions = [],
        array $writerOptions = [],
        $output = null
    ) {
        $provider = $this->getProvider($providerId);
        $writer = $this->getWriter($writerId);

        $categoryIterator = $provider->getCategories($providerOptions);

        $writer->saveCategories($categoryIterator, $writerOptions, $output);
    }
}

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

namespace Fox\CategoryManagerBundle\Tests\Functional\Service;

use Fox\CategoryManagerBundle\Service\TransferManager;

class TransferManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test for transfer()
     */
    public function testTransfer()
    {
        $providerOptions = [
            'test' => 'option',
        ];

        $writerOptions = [
            'test' => 'option',
        ];

        $categoryIterator = $this->getMock('Fox\\CategoryManagerBundle\\Iterator\\CategoryIteratorInterface');

        $provider = $this->getMock('Fox\\CategoryManagerBundle\\Provider\\CategoryProviderInterface');
        $provider->expects($this->once())
            ->method('getCategories')
            ->with($providerOptions)
            ->willReturn($categoryIterator);

        $writer = $this->getMock('Fox\\CategoryManagerBundle\\Writer\\CategoryWriterInterface');

        $writer->expects($this->once())
            ->method('saveCategories')
            ->with($categoryIterator, $writerOptions);

        $manager = new TransferManager();
        $manager->addProvider($provider, 'test_provider_1');
        $manager->addWriter($writer, 'test_writer_1');

        $manager->transfer('test_provider_1', 'test_writer_1', $providerOptions, $writerOptions);
    }

    /**
     * Test for transfer() with with unregistered provider
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage provider
     */
    public function testTransferInvalidProvider()
    {
        $manager = new TransferManager();
        $manager->transfer('test_provider_1', 'test_writer_1');
    }

    /**
     * Test for transfer() with with unregistered writer
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage writer
     */
    public function testTransferInvalidWriter()
    {
        $provider = $this->getMock('Fox\\CategoryManagerBundle\\Provider\\CategoryProviderInterface');

        $manager = new TransferManager();
        $manager->addProvider($provider, 'test_provider_1');

        $manager->transfer('test_provider_1', 'test_writer_1');
    }
}

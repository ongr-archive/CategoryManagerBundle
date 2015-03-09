<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CategoryManagerBundle\Tests\Unit\Service;

use ONGR\CategoryManagerBundle\Service\TransferManager;

class TransferManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for transfer().
     */
    public function testTransfer()
    {
        $providerOptions = [
            'test' => 'option',
        ];

        $writerOptions = [
            'test' => 'option',
        ];

        $categoryIterator = $this->getMock('ONGR\\CategoryManagerBundle\\Iterator\\CategoryIteratorInterface');

        $provider = $this->getMock('ONGR\\CategoryManagerBundle\\Provider\\CategoryProviderInterface');
        $provider->expects($this->once())
            ->method('getCategories')
            ->with($providerOptions)
            ->willReturn($categoryIterator);

        $writer = $this->getMock('ONGR\\CategoryManagerBundle\\Writer\\CategoryWriterInterface');

        $writer->expects($this->once())
            ->method('saveCategories')
            ->with($categoryIterator, $writerOptions);

        $manager = new TransferManager();
        $manager->addProvider($provider, 'test_provider_1');
        $manager->addWriter($writer, 'test_writer_1');

        $manager->transfer('test_provider_1', 'test_writer_1', $providerOptions, $writerOptions);
    }

    /**
     * Test for transfer() with with unregistered provider.
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
     * Test for transfer() with with unregistered writer.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage writer
     */
    public function testTransferInvalidWriter()
    {
        $provider = $this->getMock('ONGR\\CategoryManagerBundle\\Provider\\CategoryProviderInterface');

        $manager = new TransferManager();
        $manager->addProvider($provider, 'test_provider_1');

        $manager->transfer('test_provider_1', 'test_writer_1');
    }
}

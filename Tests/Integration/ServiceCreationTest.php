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

namespace Fox\CategoryBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Checks if services are created correctly
 */
class ServiceCreationTest extends WebTestCase
{
    /**
     * Tests if service are created correctly
     *
     * @param string $service
     * @param string $instance
     *
     * @dataProvider getTestServiceCreateData()
     */
    public function testServiceCreate($service, $instance)
    {
        $container = self::createClient()->getKernel()->getContainer();

        $this->assertTrue($container->has($service));
        $service = $container->get($service);

        $this->assertInstanceOf($instance, $service);
    }

    /**
     * Data provider for testServiceCreate()
     *
     * @return array[]
     */
    public function getTestServiceCreateData()
    {
        return [
            [
                'fox_category_manager.category_manager',
                'Fox\\CategoryManagerBundle\\Service\\CategoryManager'
            ],
            [
                'fox_category_manager.transfer_manager',
                'Fox\\CategoryManagerBundle\\Service\\TransferManager'
            ],
        ];
    }
}

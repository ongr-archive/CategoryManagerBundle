<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace Fox\CategoryManagerBundle\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CategoryManagerControllerIntegrationTest extends WebTestCase
{
    /**
     * Data provider for testing action response
     *
     * @return array
     */
    public function getTestActionResponseData()
    {
        return [
            ['/save/foo', '', 400],
            ['/tree', '', 400],
            ['/tree', json_encode(['id' => 'awesome']), 400],
            ['/move', '', 400],
            ['/move', 'notValidJSON', 400],
        ];
    }

    /**
     * Tests status codes retrvied from requests
     *
     * @param string $url
     * @param string $content
     * @param int $status
     * @param string $method
     *
     * @dataProvider getTestActionResponseData
     */
    public function testActionResponse($url, $content, $status, $method = 'POST')
    {
        $client = self::createClient();
        $client->request($method, $url, [], [], [], $content);

        $this->assertEquals($status, $client->getResponse()->getStatusCode(), 'Wrong status code returned');
        if ($status != 200) {
            $this->assertNotFalse(
                strpos($client->getResponse()->getContent(), Response::$statusTexts[$status]),
                'Response with not 200 status should return status text.'
            );
        }
    }
}

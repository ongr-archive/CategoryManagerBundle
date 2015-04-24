<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CategoryManagerBundle\Tests\Unit\Controller;

use ONGR\CategoryManagerBundle\Controller\MatchManagerController;
use ONGR\CategoryManagerBundle\Service\MatchManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MatchManagerControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider for matchAction().
     *
     * @return array
     */
    public function getMatchActionData()
    {
        $out = [];

        $manager = $this->getMatchManager();
        $manager->expects($this->never())
            ->method('match');

        // Case #0 empty request content.
        $request = $this->getRequest();
        $out[] = [$request, $manager, 400];

        // Case #1 not a json request content.
        $request = $this->getRequest();
        $request->expects($this->once())
            ->method('getContent')
            ->willReturn('TEST_STRING');
        $out[] = [$request, $manager, 400];

        // Case #2 incomplete parameter list in request content.
        $request = $this->getRequest();
        $request->expects($this->once())
            ->method('getContent')
            ->willReturn(
                json_encode(
                    [
                        'matchId' => 'test_id',
                    ]
                )
            );
        $out[] = [$request, $manager, 400];

        // Case #3 a proper request.
        $manager = $this->getMatchManager();
        $manager->expects($this->once())
            ->method('match')
            ->with('test_category_id', 'test_match_id')
            ->willReturn('test_path');

        $request = $this->getRequest();
        $request->expects($this->once())
            ->method('getContent')
            ->willReturn(
                json_encode(
                    [
                        'categoryId' => 'test_category_id',
                        'matchId' => 'test_match_id',
                    ]
                )
            );
        $out[] = [$request, $manager, 200];

        return $out;
    }

    /**
     * Test for matchAction().
     *
     * @param \PHPUnit_Framework_MockObject_MockObject|Request      $request
     * @param \PHPUnit_Framework_MockObject_MockObject|MatchManager $manager
     * @param int                                                   $statusCode
     *
     * @dataProvider getMatchActionData
     */
    public function testMatchAction($request, $manager, $statusCode)
    {
        $container = new ContainerBuilder();
        $container->set('ongr_category_manager.match_manager', $manager);

        $controller = new MatchManagerController();
        $controller->setContainer($container);

        /** @var Response $response */
        $response = $controller->matchAction($request);

        $this->assertEquals($statusCode, $response->getStatusCode());
        if ($statusCode != 200) {
            $this->assertEquals(Response::$statusTexts[$statusCode], $response->getContent());
        } else {
            $this->assertEquals(json_encode(['path' => 'test_path']), $response->getContent());
        }
    }

    /**
     * Data provider for matchesAction().
     *
     * @return array
     */
    public function getMatchesActionData()
    {
        $out = [];

        $manager = $this->getMatchManager();
        $manager->expects($this->never())
            ->method('match');

        // Case #0 empty request content.
        $request = $this->getRequest();
        $out[] = [$request, $manager, 400];

        // Case #1 not a json request content.
        $request = $this->getRequest();
        $request->expects($this->once())
            ->method('getContent')
            ->willReturn('TEST_STRING');
        $out[] = [$request, $manager, 400];

        // Case #2 incomplete parameter list in request content.
        $request = $this->getRequest();
        $request->expects($this->once())
            ->method('getContent')
            ->willReturn(
                json_encode(
                    [
                        'nodeId' => 'test_id',
                    ]
                )
            );
        $out[] = [$request, $manager, 400];

        // Case #3 a proper request.
        $manager = $this->getMatchManager();
        $manager->expects($this->once())
            ->method('getMatches')
            ->with('test_node_id', 'test_root_id', true);

        $request = $this->getRequest();
        $request->expects($this->once())
            ->method('getContent')
            ->willReturn(
                json_encode(
                    [
                        'nodeId' => 'test_node_id',
                        'rootId' => 'test_root_id',
                    ]
                )
            );
        $out[] = [$request, $manager, 200];

        return $out;
    }

    /**
     * Test for matchesAction().
     *
     * @param \PHPUnit_Framework_MockObject_MockObject|Request      $request
     * @param \PHPUnit_Framework_MockObject_MockObject|MatchManager $manager
     * @param int                                                   $statusCode
     *
     * @dataProvider getMatchesActionData
     */
    public function testMatchesAction($request, $manager, $statusCode)
    {
        $container = new ContainerBuilder();
        $container->set('ongr_category_manager.match_manager', $manager);

        $controller = new MatchManagerController();
        $controller->setContainer($container);

        /** @var Response $response */
        $response = $controller->matchesAction($request);

        $this->assertEquals($statusCode, $response->getStatusCode());
        if ($statusCode == 200) {
            $this->assertEquals(json_encode(['matches' => null]), $response->getContent());
        } else {
            $this->assertEquals(Response::$statusTexts[$statusCode], $response->getContent());
        }
    }

    /**
     * Returns symfony request mock.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|Request
     */
    protected function getRequest()
    {
        return $this->getMockBuilder('Symfony\\Component\\HttpFoundation\\Request')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Returns mock of match manager.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|MatchManager
     */
    protected function getMatchManager()
    {
        return $this->getMockBuilder('ONGR\\CategoryManagerBundle\\Service\\MatchManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Data provider for removeMatchAction().
     *
     * @return array
     */
    public function getRemoveMatchActionData()
    {
        $out = [];

        $manager = $this->getMatchManager();
        $manager->expects($this->never())
            ->method('removeMatch');

        // Case #0 empty request content.
        $request = $this->getRequest();
        $out[] = [$request, $manager, 400];

        // Case #1 not a json request content.
        $request = $this->getRequest();
        $request->expects($this->once())
            ->method('getContent')
            ->willReturn('TEST_STRING');
        $out[] = [$request, $manager, 400];

        // Case #2 incomplete parameter list in request content.
        $request = $this->getRequest();
        $request->expects($this->once())
            ->method('getContent')
            ->willReturn(
                json_encode(
                    [
                        'matchId' => 'test_id',
                    ]
                )
            );
        $out[] = [$request, $manager, 400];

        // Case #3 a proper request.
        $manager = $this->getMatchManager();
        $manager->expects($this->once())
            ->method('removeMatch')
            ->with('test_category_id', 'test_match_id');

        $request = $this->getRequest();
        $request->expects($this->once())
            ->method('getContent')
            ->willReturn(
                json_encode(
                    [
                        'categoryId' => 'test_category_id',
                        'matchId' => 'test_match_id',
                    ]
                )
            );
        $out[] = [$request, $manager, 200];

        return $out;
    }

    /**
     * Test for removeMatchAction().
     *
     * @param \PHPUnit_Framework_MockObject_MockObject|Request      $request
     * @param \PHPUnit_Framework_MockObject_MockObject|MatchManager $manager
     * @param int                                                   $statusCode
     *
     * @dataProvider getRemoveMatchActionData
     */
    public function testRemoveMatchAction($request, $manager, $statusCode)
    {
        $container = new ContainerBuilder();
        $container->set('ongr_category_manager.match_manager', $manager);

        $controller = new MatchManagerController();
        $controller->setContainer($container);

        /** @var Response $response */
        $response = $controller->removeMatchAction($request);

        $this->assertEquals($statusCode, $response->getStatusCode());
        if ($statusCode != 200) {
            $this->assertEquals(Response::$statusTexts[$statusCode], $response->getContent());
        }
    }
}

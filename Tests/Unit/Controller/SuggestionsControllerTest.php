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

use ONGR\CategoryManagerBundle\Controller\SuggestionsController;
use ONGR\CategoryManagerBundle\Service\SuggestionsManager;
use ONGR\CategoryManagerBundle\Tests\Functional\Iterator\DummyIterator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SuggestionsControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider for testListAction().
     *
     * @return array
     */
    public function getListActionData()
    {
        $out = [];

        $manager = $this->getSuggestionsManager();
        $manager->expects($this->never())
            ->method('getSuggestions');

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
        $iterator = new DummyIterator();
        $iterator->setOptions(['data' => []]);

        $manager = $this->getSuggestionsManager();
        $manager->expects($this->once())
            ->method('getSuggestions')
            ->with('test_node_id', 'test_root_id')
            ->willReturn($iterator);

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
     * Test for listAction().
     *
     * @param \PHPUnit_Framework_MockObject_MockObject|Request            $request
     * @param \PHPUnit_Framework_MockObject_MockObject|SuggestionsManager $manager
     * @param int                                                         $statusCode
     *
     * @dataProvider getListActionData
     */
    public function testListAction($request, $manager, $statusCode)
    {
        $container = new ContainerBuilder();
        $container->set('ongr_category_manager.suggestions_manager', $manager);

        $controller = new SuggestionsController();
        $controller->setContainer($container);

        /** @var Response $response */
        $response = $controller->listAction($request);

        $this->assertEquals($statusCode, $response->getStatusCode());
        if ($statusCode == 200) {
            $this->assertEquals(json_encode(['suggestions' => []]), $response->getContent());
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
     * Returns mock of suggestions manager.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|SuggestionsManager
     */
    protected function getSuggestionsManager()
    {
        return $this->getMockBuilder('ONGR\\CategoryManagerBundle\\Service\\SuggestionsManager')
            ->disableOriginalConstructor()
            ->getMock();
    }
}

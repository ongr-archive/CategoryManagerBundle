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

namespace Fox\CategoryManagerBundle\Tests\Functional\Controller;

use Fox\CategoryManagerBundle\Controller\CategoryManagerController;
use Fox\CategoryManagerBundle\Entity\Category;
use Fox\CategoryManagerBundle\Service\CategoryManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;

class CategoryManagerControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Return a dummy array as category root nodes
     *
     * @return array
     */
    protected function getDummyRootNodes()
    {
        return [
            'test_id_1' => [
                'title' => 'Test node 1',
                'id' => 'test_id_1',
            ],
            'test_id_2' => [
                'title' => 'Test node 2',
                'id' => 'test_id_2',
            ],
        ];
    }

    /**
     * Get symfony request mock
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
     * Test for listAction()
     */
    public function testListAction()
    {
        $templatingEngine = $this
            ->getMockBuilder('Symfony\Bundle\TwigBundle\TwigEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $templatingEngine
            ->expects($this->once())
            ->method('renderResponse')
            ->with(
                'FoxCategoryManagerBundle:CategoryManager:list.html.twig',
                [
                    'categories_data' => [
                        'root_nodes' => $this->getDummyRootNodes(),
                    ],
                ]
            )
            ->will($this->returnValue('renderedTemplate'));

        $manager = $this->getCategoryManager();
        $manager->expects($this->once())
            ->method('getRootNodes')
            ->will($this->returnValue($this->getDummyRootNodes()));

        $container = new ContainerBuilder();
        $container->set('templating', $templatingEngine);
        $container->set('fox_category_manager.category_manager', $manager);
        $container->set('router', $this->getMock('Symfony\\Component\\Routing\\RouterInterface'));

        $controller = new CategoryManagerController();
        $controller->setContainer($container);

        $this->assertEquals('renderedTemplate', $controller->listAction());
    }

    /**
     * Returns mock of category manager
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|CategoryManager
     */
    protected function getCategoryManager()
    {
        return $this->getMockBuilder('Fox\\CategoryManagerBundle\\Service\\CategoryManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Data provider for saveAction()
     *
     * @return array
     */
    public function getSaveActionData()
    {
        $out = [];

        // case #0 a proper request
        $request = $this->getRequest();
        $request->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue(json_encode(['title' => 'test_title'])));

        $categoryId = 'test_id';

        $category = new Category();
        $category->setId($categoryId);

        $manager = $this->getCategoryManager();
        $manager->expects($this->once())->method('getCategory')->with($categoryId, true)->willReturn($category);
        $manager->expects($this->once())->method('saveCategory');
        $out[] = [$categoryId, $manager, $request, 200];

        // case #1 an empty request
        $request = $this->getRequest();
        $request->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue(null));

        $manager = $this->getCategoryManager();
        $manager->expects($this->never())->method('getCategory');
        $manager->expects($this->never())->method('saveCategory');

        $out[] = [$categoryId, $manager, $request, 400];

        // case #2 an empty request
        $request = $this->getRequest();
        $request->expects($this->once())
            ->method('getContent')
            ->willReturn(json_encode(['title' => 'test_title', 'parent' => 'foo']));

        $categoryId = 'test_id';

        $category = new Category();
        $category->setId($categoryId);

        $manager = $this->getCategoryManager();
        $manager->expects($this->at(0))->method('getCategory')->with($categoryId, true)->willReturn($category);
        $manager->expects($this->at(1))->method('getCategory')->with('foo')->willReturn(new Category());
        $manager->expects($this->once())->method('saveCategory');
        $out[] = [$categoryId, $manager, $request, 200];

        return $out;
    }

    /**
     * Test for saveAction()
     *
     * @param string $categoryId
     * @param \PHPUnit_Framework_MockObject_MockObject|CategoryManager $manager
     * @param \PHPUnit_Framework_MockObject_MockObject|Request $request
     * @param int $statusCode
     *
     * @dataProvider getSaveActionData
     */
    public function testSaveAction($categoryId, $manager, $request, $statusCode)
    {
        $container = new ContainerBuilder();
        $container->set('fox_category_manager.category_manager', $manager);

        $controller = new CategoryManagerController();
        $controller->setContainer($container);
        $response = $controller->saveAction($request, $categoryId);

        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $response);
        $this->assertEquals($statusCode, $response->getStatusCode());
    }

    /**
     * Test for removeAction()
     */
    public function testRemoveAction()
    {
        $categoryId = 'test-id';

        $category = new Category();
        $category->setId($categoryId);

        $manager = $this->getCategoryManager();
        $manager->expects($this->once())->method('getCategory')->with($categoryId, false)->willReturn($category);
        $manager->expects($this->once())->method('removeCategory');

        $container = new ContainerBuilder();
        $container->set('fox_category_manager.category_manager', $manager);

        $controller = new CategoryManagerController();
        $controller->setContainer($container);
        $response = $controller->removeAction($categoryId);

        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $response);
    }

    /**
     * Data provider for treeAction()
     *
     * @return array
     */
    public function getTreeActionData()
    {
        $out = [];

        $parentId = 'test_parent_id';

        // case #1 request with category rootId
        $request = $this->getRequest();
        $request->expects($this->once())
            ->method('getContent')
            ->willReturn(json_encode(['parentId' => $parentId]));

        $manager = $this->getCategoryManager();
        $manager->expects($this->once())
            ->method('getCategoryTree')
            ->with($parentId);

        $out[] = [$request, $manager, 200];

        // case #2 empty request
        $request = $this->getRequest();
        $request->expects($this->once())
            ->method('getContent');

        $out[] = [$request, $manager, 400];

        // case #3 request with no rootId
        $request = $this->getMockBuilder('Symfony\\Component\\HttpFoundation\\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects($this->once())
            ->method('getContent')
            ->willReturn(json_encode(['otherId' => 'test']));

        $out[] = [$request, $manager, 400];

        return $out;
    }

    /**
     * Test for treeAction()
     *
     * @param \PHPUnit_Framework_MockObject_MockObject|Request $request
     * @param \PHPUnit_Framework_MockObject_MockObject|CategoryManager $manager
     * @param int $statusCode
     *
     * @dataProvider getTreeActionData
     */
    public function testTreeAction($request, $manager, $statusCode)
    {
        $container = new ContainerBuilder();
        $container->set('fox_category_manager.category_manager', $manager);

        $controller = new CategoryManagerController();
        $controller->setContainer($container);
        $response = $controller->treeAction($request);

        $this->assertEquals($statusCode, $response->getStatusCode());
    }

    /**
     * Data provider for moveAction()
     *
     * @return array
     */
    public function getMoveActionData()
    {
        $out = [];
        $nodeId = 'test_node_id';
        $parentId = 'test_parent_id';
        $index = 2;

        // case #1 a proper request
        $request = $this->getRequest();
        $request->expects($this->once())
            ->method('getContent')
            ->willReturn(json_encode([
                'node' => $nodeId,
                'parent' => $parentId,
                'index' => $index,
            ]));

        $manager = $this->getCategoryManager();
        $manager->expects($this->once())->method('moveCategory')->with($nodeId, $parentId, $index);

        $out[] = [$request, $manager, 200];

        // case #2 empty request
        $request = $this->getRequest();
        $request->expects($this->once())->method('getContent');

        $out[] = [$request, $manager, 400];

        // case #3 invalid request
        $request = $this->getRequest();
        $request->expects($this->once())->method('getContent')->willReturn('test_data');

        $out[] = [$request, $manager, 400];

        return $out;
    }

    /**
     * Test for moveAction()
     *
     * @param \PHPUnit_Framework_MockObject_MockObject|Request $request
     * @param \PHPUnit_Framework_MockObject_MockObject|CategoryManager $manager
     * @param int $statusCode
     *
     * @dataProvider getMoveActionData
     */
    public function testMoveAction($request, $manager, $statusCode)
    {
        $container = new ContainerBuilder();
        $container->set('fox_category_manager.category_manager', $manager);

        $controller = new CategoryManagerController();
        $controller->setContainer($container);
        $response = $controller->moveAction($request);

        $this->assertEquals($statusCode, $response->getStatusCode());
    }

    /**
     * Data provider for plainTreeAction()
     *
     * @return array
     */
    public function getPlainTreeActionData()
    {
        $out = [];
        $parentId = 'test_parent_id';
        $matchRootId = 'test_root_id';
        $size = 3;
        $from = 0;

        // case #1 a proper request, without filtering
        $request = $this->getRequest();
        $request->expects($this->once())
            ->method('getContent')
            ->willReturn(json_encode([
                'parentId' => $parentId,
                'size' => $size,
                'from' => $from,
            ]));

        $manager = $this->getCategoryManager();
        $manager->expects($this->once())->method('getPlainCategoryTree')->with($parentId, null, $size, $from);

        $out[] = [$request, $manager, 200];

        // case #2 empty request
        $request = $this->getRequest();
        $request->expects($this->once())->method('getContent');

        $out[] = [$request, $manager, 400];

        // case #3 invalid request
        $request = $this->getRequest();
        $request->expects($this->once())->method('getContent')->willReturn('test_data');

        $out[] = [$request, $manager, 400];

        // case #4 not full arguments list in request
        $request = $this->getRequest();
        $request->expects($this->once())
            ->method('getContent')
            ->willReturn(json_encode([
                'parentId' => $parentId,
            ]));

        $out[] = [$request, $manager, 400];

        // case #5 a proper request, with filtering
        $request = $this->getRequest();
        $request->expects($this->once())
            ->method('getContent')
            ->willReturn(json_encode([
                'parentId' => $parentId,
                'matchRootid' => $matchRootId,
                'size' => $size,
                'from' => $from,
            ]));

        $manager = $this->getCategoryManager();
        $manager->expects($this->once())->method('getPlainCategoryTree')->with($parentId, null, $size, $from);

        $out[] = [$request, $manager, 200];

        return $out;
    }

    /**
     * Test for plainTreeAction()
     *
     * @param \PHPUnit_Framework_MockObject_MockObject|Request $request
     * @param \PHPUnit_Framework_MockObject_MockObject|CategoryManager $manager
     * @param int $statusCode
     *
     * @dataProvider getPlainTreeActionData
     */
    public function testPlainTreeAction($request, $manager, $statusCode)
    {
        $container = new ContainerBuilder();
        $container->set('fox_category_manager.category_manager', $manager);

        $controller = new CategoryManagerController();
        $controller->setContainer($container);
        $response = $controller->plainTreeAction($request);

        $this->assertEquals($statusCode, $response->getStatusCode());
    }
}

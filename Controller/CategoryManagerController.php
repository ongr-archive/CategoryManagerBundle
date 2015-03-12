<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\CategoryManagerBundle\Controller;

use ONGR\CategoryManagerBundle\Entity\Category;
use ONGR\CategoryManagerBundle\Service\CategoryManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for category manager pages.
 */
class CategoryManagerController extends Controller
{
    /**
     * Returns template data for listAction.
     *
     * @return array
     */
    protected function getListActionData()
    {
        return [
            'categories_data' => [
                'root_nodes' => $this->getCategoryManager()->getRootNodes(true),
            ],
        ];
    }

    /**
     * Categories list page.
     *
     * @return Response
     */
    public function listAction()
    {
        return $this->render(
            'ONGRCategoryManagerBundle:CategoryManager:list.html.twig',
            $this->getListActionData()
        );
    }

    /**
     * Category save action.
     *
     * @param Request $request
     * @param string  $categoryId
     *
     * @return Response
     */
    public function saveAction(Request $request, $categoryId)
    {
        $content = $this->parseAjaxRequest($request, ['title']);
        if (!$content) {
            return new Response(Response::$statusTexts[400], 400);
        }

        $manager = $this->getCategoryManager();

        /** @var Category $category */
        $category = $manager->getCategory($categoryId, true);
        $category->setTitle($content['title']);

        if (isset($content['parent'])) {
            $parent = $manager->getCategory($content['parent']);
            $category->setParent($parent);
        }

        $manager->saveCategory($category);

        return new JsonResponse(['id' => $category->getId(), 'title' => $category->getTitle()]);
    }

    /**
     * Removes category by ID.
     *
     * @param string $categoryId
     *
     * @return Response
     */
    public function removeAction($categoryId)
    {
        $manager = $this->getCategoryManager();

        /** @var Category $category */
        $category = $manager->getCategory($categoryId);
        $manager->removeCategory($category);

        return new Response();
    }

    /**
     * Data provider for treeAction().
     *
     * @param string|null $parentId
     *
     * @return array
     */
    protected function getTreeActionData($parentId)
    {
        return [
            'nodes' => $this->getCategoryManager()->getCategoryTree($parentId),
        ];
    }

    /**
     * Return json response with tree nodes.
     *
     * @param Request $request
     *
     * @return JsonResponse|Response
     */
    public function treeAction(Request $request)
    {
        $content = $this->parseAjaxRequest($request, ['parentId']);
        if (!$content) {
            return new Response(Response::$statusTexts[400], 400);
        }

        return new JsonResponse(
            $this->getTreeActionData($content['parentId'])
        );
    }

    /**
     * Data provider for plainTreeAction().
     *
     * @param string      $parentId
     * @param string|null $matchRootId
     * @param int         $size
     * @param int         $from
     *
     * @return array
     */
    protected function getPlainTreeActionData($parentId, $matchRootId, $size, $from)
    {
        return [
            'nodes' => $this->getCategoryManager()->getPlainCategoryTree($parentId, $matchRootId, $size, $from, true),
        ];
    }

    /**
     * Returns json response with plain list of categories.
     *
     * @param Request $request
     *
     * @return JsonResponse|Response
     */
    public function plainTreeAction(Request $request)
    {
        $content = $this->parseAjaxRequest($request, ['parentId', 'size', 'from']);
        if (!$content) {
            return new Response(Response::$statusTexts[400], 400);
        }

        $matchRootId = isset($content['matchRootId']) ? $content['matchRootId'] : null;

        return new JsonResponse(
            $this->getPlainTreeActionData($content['parentId'], $matchRootId, $content['size'], $content['from'])
        );
    }

    /**
     * Handle category node placement in a tree.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function moveAction(Request $request)
    {
        $content = $this->parseAjaxRequest($request, ['node', 'parent', 'index']);
        if (!$content) {
            return new Response(Response::$statusTexts[400], 400);
        }

        $this->getCategoryManager()->moveCategory($content['node'], $content['parent'], $content['index']);

        return new Response();
    }

    /**
     * Parses ajax request.
     *
     * @param Request $request
     * @param array   $fields
     *
     * @return array|false
     */
    protected function parseAjaxRequest($request, $fields = [])
    {
        $content = $request->getContent();
        if (empty($content)) {
            return false;
        }

        $content = json_decode($content, true);
        if ($content === null) {
            return false;
        }

        foreach ($fields as $field) {
            if (!isset($content[$field])) {
                return false;
            }
        }

        return $content;
    }

    /**
     * Returns category manager.
     *
     * @return CategoryManager
     */
    protected function getCategoryManager()
    {
        return $this->get('ongr_category_manager.category_manager');
    }
}

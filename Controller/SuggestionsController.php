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

use ONGR\CategoryManagerBundle\Service\SuggestionsManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Suggestions controller class.
 */
class SuggestionsController extends Controller
{
    /**
     * Returns json data for listAction().
     *
     * @param string $nodeId
     * @param string $rootId
     *
     * @return array
     */
    protected function getListActionData($nodeId, $rootId)
    {
        return [
            'suggestions' => iterator_to_array($this->getSuggestionsManager()->getSuggestions($nodeId, $rootId)),
        ];
    }

    /**
     * Controller action for retrieving suggestions.
     *
     * @param Request $request
     *
     * @return JsonResponse|Response
     */
    public function listAction(Request $request)
    {
        $content = $request->getContent();

        if (empty($content)) {
            return new Response(Response::$statusTexts[400], 400);
        }

        $content = json_decode($content, true);

        if ($content === null || empty($content['nodeId']) || empty($content['rootId'])) {
            return new Response(Response::$statusTexts[400], 400);
        }

        return new JsonResponse($this->getListActionData($content['nodeId'], $content['rootId']));
    }

    /**
     * Returns suggestions manager.
     *
     * @return SuggestionsManager
     */
    protected function getSuggestionsManager()
    {
        return $this->get('ongr_category_manager.suggestions_manager');
    }
}

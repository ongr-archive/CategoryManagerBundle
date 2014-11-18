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

namespace Fox\CategoryManagerBundle\Controller;

use Fox\CategoryManagerBundle\Service\SuggestionsManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SuggestionsController extends Controller
{
    /**
     * Returns json data for listAction()
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
     * Controller action for retrieving suggestions
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
     * Returns suggestions manager
     *
     * @return SuggestionsManager
     */
    protected function getSuggestionsManager()
    {
        return $this->get('fox_category_manager.suggestions_manager');
    }
}

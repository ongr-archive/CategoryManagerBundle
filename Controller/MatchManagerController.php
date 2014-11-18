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

use Fox\CategoryManagerBundle\Service\MatchManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for category match manager actions
 */
class MatchManagerController extends Controller
{
    /**
     * Returns json data for matchAction()
     *
     * @param string $categoryId
     * @param string $matchId
     *
     * @return array
     */
    protected function getMatchActionData($categoryId, $matchId)
    {
        return ['path' =>  $this->getMatchManager()->match($categoryId, $matchId)];
    }

    /**
     * Action for matching 2 provided categories
     *
     * @param Request $request
     *
     * @return Response|JsonResponse
     */
    public function matchAction(Request $request)
    {
        $content = $request->getContent();

        if (empty($content)) {
            return new Response(Response::$statusTexts[400], 400);
        }

        $content = json_decode($content, true);

        if ($content === null || empty($content['categoryId']) || empty($content['matchId'])) {
            return new Response(Response::$statusTexts[400], 400);
        }

        return new JsonResponse($this->getMatchActionData($content['categoryId'], $content['matchId']));
    }

    /**
     * Returns json data for matchesAction()
     *
     * @param string $nodeId
     * @param string $rootId
     *
     * @return array
     */
    protected function getMatchesActionData($nodeId, $rootId)
    {
        return [
            'matches' => $this->getMatchManager()->getMatches($nodeId, $rootId, true),
        ];
    }

    /**
     * Action to get matches based on node and selected root
     *
     * @param Request $request
     *
     * @return Response|JsonResponse
     */
    public function matchesAction(Request $request)
    {
        $content = $request->getContent();

        if (empty($content)) {
            return new Response(Response::$statusTexts[400], 400);
        }

        $content = json_decode($content, true);

        if ($content === null || empty($content['nodeId']) || empty($content['rootId'])) {
            return new Response(Response::$statusTexts[400], 400);
        }

        return new JsonResponse($this->getMatchesActionData($content['nodeId'], $content['rootId']));
    }

    /**
     * Action to remove existing match
     *
     * @param Request $request
     *
     * @return Response
     */
    public function removeMatchAction(Request $request)
    {
        $content = $request->getContent();

        if (empty($content)) {
            return new Response(Response::$statusTexts[400], 400);
        }

        $content = json_decode($content, true);

        if ($content === null || empty($content['categoryId']) || empty($content['matchId'])) {
            return new Response(Response::$statusTexts[400], 400);
        }

        $this->getMatchManager()->removeMatch($content['categoryId'], $content['matchId']);

        return new Response();
    }

    /**
     * Returns match manager
     *
     * @return MatchManager
     */
    protected function getMatchManager()
    {
        return $this->get('fox_category_manager.match_manager');
    }
}

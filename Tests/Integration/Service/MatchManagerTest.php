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

namespace Fox\CategoryManagerBundle\Tests\Integration\Service;

use Fox\CategoryManagerBundle\Entity\Match;
use Fox\CategoryManagerBundle\Service\MatchManager;
use Fox\CategoryManagerBundle\Tests\Integration\BaseDatabaseTest;

class MatchManagerTest extends BaseDatabaseTest
{
    /**
     * Data provider for match()
     *
     * @return array
     */
    public function getMatchData()
    {
        $out = [];

        // Case #0 sorted ids
        $categoryId = '53f45979139606.24866601';
        $matchId = '53f45a96c733f6.75280890';
        $expectedPath = 'Wakeboarding';

        $searchId = '53f45979139606.24866601';
        $matchResult = '53f45a96c733f6.75280890';

        $out[] = [$categoryId, $matchId, $expectedPath, $searchId, $matchResult];

        // Case #1 unsorted ids
        $categoryId = '53f45cc07f55d2.92980246';
        $matchId = '53f45979139606.24866601';
        $expectedPath = 'Kiteboarding / Kiteboards';

        $searchId = '53f45979139606.24866601';
        $matchResultId = '53f45cc07f55d2.92980246';

        $out[] = [$categoryId, $matchId, $expectedPath, $searchId, $matchResultId];

        return $out;
    }

    /**
     * Test for match()
     *
     * @param string $categoryId
     * @param string $matchId
     * @param string $expectedPath
     * @param string $searchId
     * @param string $matchResultId
     *
     * @dataProvider getMatchData
     */
    public function testMatch($categoryId, $matchId, $expectedPath, $searchId, $matchResultId)
    {
        /* @var MatchManager $matchManager */
        $matchManager = $this->getContainer()->get('fox_category_manager.match_manager');
        $matchPath = $matchManager->match($categoryId, $matchId);

        $this->assertEquals($expectedPath, $matchPath);

        $em = $this->getEntityManager();
        $em->clear();

        /* @var Match $match */
        $match = $em->getRepository('FoxCategoryManagerBundle:Match')->findOneByCategory($searchId);

        $this->isInstanceOf('Fox\\CategoryManagerBundle\\Entity\\Match', $match);
        $this->assertEquals($searchId, $match->getCategory()->getId());
        $this->assertEquals($matchResultId, $match->getMatchedCategory()->getId());
    }

    /**
     * Data provider for getMatches()
     *
     * @return array
     */
    public function getMatchesData()
    {
        $out = [];

        // case #0 match will be reordered
        $out[] = [
            '53f45a96c733f6.75280890', // Category Id
            '53f45976ef75c9.78862935', // Match Id
            '53f4590d0ccec9.39288089', // Root Id
            [
                '53f45976ef75c9.78862935' => [
                    'id' => '53f45976ef75c9.78862935',
                    'path' => 'Kiteboarding / Kites',
                ],
            ],
        ];

        //case #1 match will be as inserted
        $out[] = [
            '53f45976ef75c9.78862935', // Category Id
            '53f45a96c733f6.75280890', // Match Id
            '53f45a96c733f6.75280890', // Root Id
            [
                '53f45a96c733f6.75280890' => [
                    'id' => '53f45a96c733f6.75280890',
                    'path' => 'Wakeboarding',
                ],
            ],
        ];

        return $out;
    }

    /**
     * Test for getMatches()
     *
     * @param string $categoryId
     * @param string $matchId
     * @param string $rootId
     * @param array $expectedResult
     *
     * @dataProvider getMatchesData
     */
    public function testGetMatches($categoryId, $matchId, $rootId, $expectedResult)
    {
        /* @var MatchManager $matchManager */
        $matchManager = $this->getContainer()->get('fox_category_manager.match_manager');
        $matchManager->match($categoryId, $matchId);

        $result = $matchManager->getMatches($categoryId, $rootId, true);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test for getMatches(), result as objects
     */
    public function testGetMatchesObjects()
    {
        $matchManager = $this->getContainer()->get('fox_category_manager.match_manager');
        $matchManager->match('53f45976ef75c9.78862935', '53f45a96c733f6.75280890');

        $result = $matchManager->getMatches(
            '53f45976ef75c9.78862935', // Category Id
            '53f45a96c733f6.75280890', // Root Id
            false // Return as objects
        );

        $this->assertEquals('53f45976ef75c9.78862935', $result[0]->getCategory()->getId());
        $this->assertEquals('53f45a96c733f6.75280890', $result[0]->getMatchedCategory()->getId());
    }

    /**
     * Data provider for testRemoveMatch()
     *
     * @return array
     */
    public function getRemoveMatchData()
    {
        $out = [];

        // case #0 not reversed match removal
        $out[] = [
            '53f45976ef75c9.78862935', // Initial match
            '53f45a96c733f6.75280890',
            '53f45976ef75c9.78862935', // Remove match ids
            '53f45a96c733f6.75280890',
        ];

        // case #1 reversed match removal
        $out[] = [
            '53f45976ef75c9.78862935', // Initial match
            '53f45a96c733f6.75280890',
            '53f45a96c733f6.75280890', // Remove match ids
            '53f45976ef75c9.78862935',
        ];

        return $out;
    }

    /**
     * Test for removeMatch()
     *
     * @param string $categoryId,
     * @param string $matchId
     * @param string $removeId
     * @param string $removeMatchId
     *
     * @dataProvider getRemoveMatchData
     */
    public function testRemoveMatch($categoryId, $matchId, $removeId, $removeMatchId)
    {
        /* @var MatchManager $matchManager */
        $matchManager = $this->getContainer()->get('fox_category_manager.match_manager');
        $matchManager->match($categoryId, $matchId);

        $em = $this->getEntityManager();
        $em->clear();

        $match = $em->getRepository('FoxCategoryManagerBundle:Match')->findOneByCategory($categoryId);
        $this->isInstanceOf('Fox\\CategoryManagerBundle\\Entity\\Match', $match);

        $matchManager->removeMatch($removeId, $removeMatchId);

        $em->clear();

        $match = $em->getRepository('FoxCategoryManagerBundle:Match')->findOneByCategory($categoryId);
        $this->assertNull($match);
    }

    /**
     * Data provider for testMatchMultiple
     *
     * @return array
     */
    public function getMatchMultipleData()
    {
        $out = [];

        // Case #0 flush after each persist, skip single entry
        $out[] = [1, 1, ['a' => 'b', 'c' => 'd', 'e' => 'f', 'g' => 'h']];

        // Case #1 flush after each persist, skip 3 first entries
        $out[] = [3, 1, ['e' => 'f', 'g' => 'h']];

        // Case #2 default flush behaviour, skip single entry
        $out[] = [1, MatchManager::MULTI_MATCH_FLUSH_COUNT, ['a' => 'b', 'c' => 'd', 'e' => 'f', 'g' => 'h']];

        return $out;
    }

    /**
     * Test for matchMultiple
     *
     * @param int $skipLines
     * @param int $flushCount
     * @param array $expectedResult
     *
     * @dataProvider getMatchMultipleData
     */
    public function testMatchMultiple($skipLines, $flushCount, $expectedResult)
    {
        $em = $this->getEntityManager();

        $iterator = new \SplFileObject('Tests/Integration/Fixtures/matches.csv');
        $iterator->setFlags(
            \SplFileObject::READ_CSV |
            \SplFileObject::READ_AHEAD |
            \SplFileObject::DROP_NEW_LINE |
            \SplFileObject::SKIP_EMPTY
        );

        /* @var MatchManager $matchManager */
        $matchManager = $this->getContainer()->get('fox_category_manager.match_manager');
        $matchManager->matchMultiple($iterator, $skipLines, $flushCount);

        $repo = $em->getRepository('FoxCategoryManagerBundle:Match');
        $result = $this->flattenMatches($repo->findAll());

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Returns extracted ids from array of match entities
     *
     * @param array $matches
     *
     * @return array
     */
    protected function flattenMatches($matches)
    {
        $result = [];

        /* @var Match $match */
        foreach ($matches as $match) {
            $result[$match->getCategory()->getId()] = $match->getMatchedCategory()->getId();
        }

        return $result;
    }
}

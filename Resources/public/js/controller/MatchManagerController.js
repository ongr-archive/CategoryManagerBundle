/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

angular
    .module('controller.matchManager', [])
    .controller('matchManager', ['$scope', '$http', 'matchService',
        function($scope, $http, matchService) {
            /**
             * Match service
             *
             * @type {{}}
             */
            $scope.matchService = matchService;

            /**
             * Available matches
             *
             * @type {{}}
             */
            $scope.matches = {};

            /**
             * Controller initialization
             */
            $scope.init = function() {
                $scope.matches = $scope.matchService.getMatches();
            }
        }
    ]);

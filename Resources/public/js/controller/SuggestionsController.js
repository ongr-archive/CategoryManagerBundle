/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

angular
    .module('controller.suggestions', [])
    .controller('suggestions', ['$scope', '$http', 'suggestionsService', 'matchService',
        function($scope, $http, suggestionsService, matchService) {
            /**
             * Suggestions service
             *
             * @type {{}}
             */
            $scope.suggestionsService = suggestionsService;

            /**
             * Match Service
             *
             * @type {{}}
             */
            $scope.matchService = matchService;

            /**
             * Suggestions array
             *
             * @type {[]}
             */
            $scope.suggestions = [];

            /**
             * Matched nodes array
             *
             * @type {[]}
             */
            $scope.matches = {};

            /**
             * Controller initialization
             */
            $scope.init = function() {
                $scope.suggestions = $scope.suggestionsService.getSuggestions();
                $scope.matches = $scope.matchService.getMatches();
            }

            /**
             * Can node be matched with selected node?
             *
             * @param {{}} node
             *
             * @returns {boolean}
             */
            $scope.canMatch = function(node) {
                // Is match already made
                if (node.id in $scope.matches) {
                    return false;
                }

                return true;
            }
        }
    ]);

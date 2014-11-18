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

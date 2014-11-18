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

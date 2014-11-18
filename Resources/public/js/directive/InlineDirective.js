/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

angular
    .module('directive.inline', [])
    .directive('inline', ['$http', 'asset', 'categoryService', function ($http, $asset, categoryService) {
        return {
            restrict: "A",
            scope: { node: "="},
            templateUrl: $asset.getLink('template/inline.html'),
            link: function($scope, element, attr) {

                var inputElement = angular.element(element[0].children[1].children[1])[0];
                element.addClass('inline-edit');

                /**
                 * Shows input field
                 *
                 * @param {Event} event
                 */
                $scope.edit = function(event) {
                    $scope.oldValue = $scope.node.title;
                    element.addClass('active');
                    inputElement.focus();

                    event.preventDefault();
                    event.stopPropagation();
                };

                /**
                 * Closes input field
                 *
                 * @param {Event} event
                 */
                $scope.close = function(event) {
                    event.stopPropagation();

                    $scope.node.title = $scope.oldValue;
                    element.removeClass('active');
                };

                /**
                 * Saves values with ajax request
                 *
                 * @param {Event} event
                 */
                $scope.save = function(event) {
                    event.stopPropagation();
                    element.removeClass('active');

                    requestUrl = Routing.generate('fox_category_manager_save',
                        { categoryId: $scope.node.id }
                    );

                    $http({
                        method:"POST",
                        url: requestUrl,
                        data: {
                            title: $scope.node.title
                        }
                    }).success(function(data, status) {
                        categoryService.updateRootNode($scope.node);
                    });
                };

                /**
                 * Extra shortcuts for better user experience
                 *
                 * @param e Event
                 */
                $scope.keyPress = function(e) {
                    switch(e.keyCode) {
                        case 13: //enter
                            $scope.save(e);
                            break;
                        case 27: //esc
                            $scope.close(e);
                            break;
                    }
                }
            }
        }
    }]);

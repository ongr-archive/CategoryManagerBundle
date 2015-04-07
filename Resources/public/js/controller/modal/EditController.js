/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

angular
    .module('controller.modal.edit', [])
    .controller('edit', ['$scope', '$modalInstance', '$http', 'node', 'categoryService', '$window',
        function($scope, $modalInstance, $http, node, categoryService, $window) {
            /**
             * Category node passed to modal
             *
             * @type {{}}
             */
            $scope.node = node;

            /**
             * A copy of an original node
             *
             * @type {{}}
             */
            $scope.originalNode = angular.copy(node);

            /**
             * List of locales
             *
             * @type {{}}
             */
            $scope.locales = $window.locales;

            /**
             * Default locale
             *
             * @type {{}}
             */
            $scope.defaultLocale = $window.default_locale;

            /**
             * Revert changed data back to its original form
             */
            $scope.restore = function() {
                $scope.node.title = $scope.originalNode.title;
            };

            /**
             * Restores referenced values and dismisses modal
             */
            $scope.cancel = function () {
                $scope.restore();
                $modalInstance.dismiss('cancel');
            };

            /**
             * Save changes and close modal
             */
            $scope.save = function () {
                var url = Routing.generate(
                    'ongr_category_manager_save',
                    { categoryId: $scope.node.id }
                );

                var data = {};
                for (var i=0; i<$scope.locales.length; i++) {
                    if ($scope.locales[i] == $scope.defaultLocale) {
                        data['title'] = $scope.node['title_' + $scope.locales[i]];
                    }
                    data['title_' + $scope.locales[i]] = $scope.node['title_' + $scope.locales[i]];
                }

                $http({
                    method: "POST",
                    url: url,
                    data: data
                }).error(function(data, status) {
                    $scope.restore();
                    alert("Error while saving category.");
                }).success(function(data, status) {
                    $scope.node.title = data.title;
                    categoryService.updateRootNode($scope.node);
                });

                $modalInstance.close();
            }
        }
    ]);

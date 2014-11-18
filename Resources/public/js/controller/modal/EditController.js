/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

angular
    .module('controller.modal.edit', [])
    .controller('edit', ['$scope', '$modalInstance', '$http', 'node', 'categoryService',
        function($scope, $modalInstance, $http, node, categoryService) {
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
                    'fox_category_manager_save',
                    { categoryId: $scope.node.id }
                );
                $http({method:"POST", url: url, data: {title: $scope.node.title}}).
                    error(function(data, status) {
                        $scope.restore();
                        alert("Error while saving category.");
                    }).success(function(data, status) {
                        categoryService.updateRootNode($scope.node);
                    });

                $modalInstance.close();
            }
        }
    ]);

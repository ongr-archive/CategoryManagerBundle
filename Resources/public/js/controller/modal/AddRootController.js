/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

angular
    .module('controller.modal.addRoot', [])
    .controller('addRoot', ['$scope', '$modalInstance', '$http', 'rootNodes', 'categoryService',
        function($scope, $modalInstance, $http, rootNodes, categoryService) {
            /**
             * @type {{}}
             */
            $scope.node = {id: '', title: ''};

            /**
             * @type {{}}
             */
            $scope.rootNodes = rootNodes;

            /**
             * Creates root category
             */
            $scope.save = function () {
                var url = Routing.generate(
                    'fox_category_manager_save',
                    { categoryId: ' ' }
                );

                $http({ method: "POST", url: url, data: {title: $scope.node.title} })
                    .success(function(data, status) {
                        $scope.node.id = data.id;
                        categoryService.addRootNode($scope.node);

                        $modalInstance.close();
                    }).
                    error(function(data, status) {
                        alert("Error while saving root category.");
                    });
            };

            /**
             * Closes modal
             */
            $scope.cancel = function () {
                $modalInstance.dismiss('cancel');
            };
        }
    ]);

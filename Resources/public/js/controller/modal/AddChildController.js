/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

angular
    .module('controller.modal.addChild', [])
    .controller('addChild', ['$scope', '$modalInstance', '$http', 'node',
        function($scope, $modalInstance, $http, node) {
            /**
             * @type {{}}
             */
            $scope.node = {id: '', title: '', __children: [], left: 0, right: 0};

            /**
             * @type {{}}
             */
            $scope.parentNode = node;

            /**
             * Creates root category
             */
            $scope.save = function () {
                var url = Routing.generate(
                    'fox_category_manager_save',
                    { categoryId: ' ' }
                );
                $http({ method: "POST", url: url, data: {title: $scope.node.title, parent: $scope.parentNode.id} })
                    .success(function(data, status) {
                        $scope.node.id = data.id;

                        $scope.parentNode.__children.push($scope.node);
                        $scope.parentNode.right = 2;
                        $scope.parentNode.left = 0;

                        $modalInstance.close();
                    }).
                    error(function(data, status) {
                        alert("Error while adding category.");
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

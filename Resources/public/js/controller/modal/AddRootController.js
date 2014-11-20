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
                    'ongr_category_manager_save',
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

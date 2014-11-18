/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

angular
    .module('controller.list', [])
    .controller('list', ['$scope', '$http', '$modal', 'asset', 'categoryService', 'matchService', 'suggestionsService', '$element',
        function($scope, $http, $modal, $asset, categoryService, matchService, suggestionsService, $element) {
            /**
             * Category tree holder
             *
             * @type {[]}
             */
            $scope.categoryTree = [];

            /**
             * Category tree root nodes
             *
             * @type {{}}
             */
            $scope.rootNodes = {};

            /**
             * Selected root node
             *
             * @type {String}
             */
            $scope.rootNodeId = '';

            /**
             * Tree nodes template url
             *
             * @type {String}
             */
            $scope.treeTemplate = $asset.getLink('template/treeNodes.html');

            /**
             * Plain tree nodes template url
             *
             * @type {String}
             */
            $scope.plainTreeTemplate = $asset.getLink('template/plainNodes.html');

            /**
             * Selected category node
             *
             * @type {{}}
             */
            $scope.selectedNode = null;

            /**
             * Type of controller
             *
             * @type {String}
             */
            $scope.mode = 'master';

            /**
             * Already matched nodes
             *
             * @type {{}}
             */
            $scope.matchedNodes = {};

            /**
             * Is controller in matching mode
             *
             * @type {boolean}
             */
            $scope.matchModeActive = false;

            /**
             * Match service
             *
             * @type {{}}
             */
            $scope.matchService = matchService;

            /**
             * Suggestions service
             *
             * @type {{}}
             */
            $scope.suggestionsService = suggestionsService;


            /**
             * Current category list type tree/plain
             *
             * @type {String}
             */
            $scope.listType = 'tree';

            /**
             * Are nodes currently loading
             *
             * @type {boolean}
             */
            $scope.nodesLoading = false;

            /**
             * Is plain list is loaded with filtered categories based on matches
             *
             * @type {boolean}
             */
            $scope.filteredMode = true;

            /**
             * Tree container DOM element
             *
             * @type {{}}
             */
            $scope.containerElement = {};

            $scope.scrollElements = [];

            /**
             * Controller init
             *
             * @param {String} mode
             */
            $scope.init = function (mode, containerElement) {
                $scope.mode = mode;

                $scope.containerElement = $element.find(containerElement);

                $scope.rootNodes = categoryService.getRootNodes($scope.mode);

                $scope.rootNodeId = Object.keys($scope.rootNodes)[0];

                categoryService.setRootNode($scope.mode, $scope.rootNodeId);

                $scope.matchService.registerController(this, mode);

                $scope.suggestionsService.registerListController(this, mode);

                $scope.fetchTree(false, null);

                $scope.matchedNodes = $scope.matchService.getMatches();

                $scope.containerElement.on('scroll', $scope.treeScroll);
            }

            /**
             * Checks if node has children based on its left-right values
             *
             * @param {{}} node
             *
             * @returns {boolean}
             */
            $scope.hasChildren = function (node) {
                return ((node.right - node.left) > 1);
            }

            /**
             * Should tree element be expanded or not
             *
             * @param {{}} nodeScope
             *
             * @returns {boolean}
             */
            $scope.shouldCollapse = function (nodeScope) {
                if (nodeScope.node.forceExpand) {
                    return false;
                }

                if (!$scope.hasChildren(nodeScope.node)) {
                    return false;
                }

                return true;
            }

            /**
             * Handle node expand/collapse click
             *
             * @param {{}} nodeScope
             * @param {Event} event
             */
            $scope.toggleNode = function (nodeScope, event) {
                nodeScope.toggle();

                if (!nodeScope.collapsed) {

                    newElement = {element: $(event.target).closest('li'), data: [], node: {}};
                    $scope.scrollElements.push(newElement);

                    nodeScope.node.forceExpand = true;
                    if (!nodeScope.node.loaded) {
                        $scope.fetchTree(false, nodeScope.node);
                    }
                } else {
                    nodeScope.node.forceExpand = false;
                    nodeScope.node.__children = [];
                    nodeScope.node.loaded = false;

                    for (var i = 0; i < $scope.scrollElements.length; i++) {
                        if ($scope.scrollElements[i].node.id == nodeScope.node.id) {
                            $scope.scrollElements.splice(i, 1);
                        }
                    }
                }

                event.stopPropagation();
            }

            /**
             * Callbacks for category tree
             *
             * @type {{}}
             */
            $scope.treeOptions = {
                beforeDrop: function(event) {
                    if (angular.isUndefined(event.dest.nodesScope.node)) {
                        event.source.nodeScope.$$apply = false;
                        return;
                    }

                    var nodeId = event.source.nodeScope.node.id;
                    var parentId = event.dest.nodesScope.node.id;
                    var index = event.dest.index;

                    $http({method: "POST", url: Routing.generate('fox_category_manager_move'), data: {
                        node: nodeId,
                        parent: parentId,
                        index: index
                    }}).error(function(data, status) {
                        alert("Error while moving category.");
                    });
                },
                dropped: function(event) {
                    sourceParent = event.source.nodeScope.$parentNodeScope.node;
                    destParent = event.dest.nodesScope.node;

                    if (sourceParent.__children.length == 0) {
                        sourceParent.left = sourceParent.right = 0;
                    }

                    destParent.left = 0;
                    destParent.right = 2;
                    destParent.forceExpand = true;
                }
            };

            /**
             * Loads category tree via ajax call
             *
             * @param {boolean} pushNodeUpdate
             * @param {{}} parentNode
             */
            $scope.fetchTree = function (pushNodeUpdate, parentNode)
            {
                // Should reference to rootNode be updated?
                if (pushNodeUpdate) {
                    categoryService.setRootNode($scope.mode, $scope.rootNodeId);
                    $scope.matchService.rootNodeChange($scope.mode);
                }

                // If parent node is not provided load based on rootId
                var loadId = (parentNode) ? parentNode.id : $scope.rootNodeId;

                if ($scope.listType == 'plain') {
                    return $scope.fetchPlainTree(false);
                }

                if (!loadId || loadId == 0) {
                    // Clear current tree, but don't try to fetch new one with invalid id
                    $scope.categoryTree = [];
                    return;
                }

                $scope.nodesLoading = true;

                $http({
                    method:"POST",
                    url: Routing.generate('fox_category_manager_tree'),
                    data: { parentId: loadId }
                }).success(function(data, status) {
                    if (parentNode) {
                        var remoteParent = data.nodes[0];
                        var scrollElement = $scope.scrollElements[$scope.scrollElements.length - 1];

                        scrollElement.node = parentNode;
                        scrollElement.node.__children = [];

                        scrollElement.node.left = remoteParent.left;
                        scrollElement.node.right = remoteParent.right;
                        scrollElement.node.loaded = true;

                        scrollElement.data = remoteParent.__children;

                        for (var i = 0; i < 10 && scrollElement.data.length > 0; i++) {
                            scrollElement.node.__children.push(scrollElement.data.shift());
                        }

                        if (scrollElement.data.length == 0) {
                            $scope.scrollElements.pop();
                        }

                        $scope.nodesLoading = false;
                    } else {
                        // Rebuild whole tree
                        $scope.categoryTree = data.nodes;
                        $scope.syncRootNodes();

                        if (pushNodeUpdate) {
                            $scope.matchService.updateRoot($scope.mode);
                            $scope.suggestionsService.updateRoot($scope.mode);
                        }
                    }
                }).error(function(data, status) {
                    alert("Error while retrieving category tree.");
                });
            };

            /**
             * Loads plain category tree via ajax call
             *
             * @param {boolean} append
             */
            $scope.fetchPlainTree = function(append)
            {
                $scope.nodesLoading = true;
                if (!append) {
                    $scope.categoryTree = [];
                }

                var matchRootId = null;
                if ($scope.filteredMode) {
                    matchRootId = $scope.matchService.getMatchRoot($scope.mode);
                }

                $http({
                    method:"POST",
                    url: Routing.generate('fox_category_manager_plain_tree'),
                    data: {
                        parentId: $scope.rootNodeId,
                        matchRootId: matchRootId,
                        size: 10,
                        from: $scope.categoryTree.length
                    }
                }).success(function(data, status) {
                    if (append) {
                        for (var i = 0; i < data.nodes.length; i++) {
                            $scope.categoryTree.push(data.nodes[i]);
                        }
                    } else {
                        $scope.categoryTree = data.nodes;
                    }

                    $scope.syncRootNodes();

                    // No point of future requests if this one is empty
                    if (data.nodes.length != 0) {
                        $scope.nodesLoading = false;
                    }
                });
            }

            /**
             * Replaces root node reference with actual tree elements
             */
            $scope.syncRootNodes = function() {
                for (var index = 0; index < $scope.categoryTree.length; ++index) {
                    var node = $scope.categoryTree[index];

                    if (node.id in $scope.rootNodes) {
                        node.loaded = true;
                        $scope.rootNodes[node.id] = node;
                    }
                }
            }

            /**
             * Removes category by ID
             *
             * @param {String} nodeId
             * @param {{}} nodeScope
             */
            $scope.removeCategory = function(node, nodeScope) {
                $http({
                    method: "DELETE",
                    url: Routing.generate(
                        'fox_category_manager_remove',
                        {
                            'categoryId': node.id
                        }
                    )
                }).success(function(data, status) {
                    if (node.id in $scope.rootNodes) {
                        categoryService.deleteRootNode(node);

                        if (Object.keys($scope.rootNodes).length < 1) {
                            $scope.rootNodes['0'] = categoryService.getDummyRoot();
                        }

                        $scope.rootNodeId = Object.keys($scope.rootNodes)[0];
                        categoryService.setRootNode($scope.mode, $scope.rootNodeId);

                        $scope.fetchTree();
                        return;
                    }

                    if (nodeScope.$parentNodeScope != null) {
                        parentNode = nodeScope.$parentNodeScope.node;
                        nodeScope.remove();

                        if (parentNode.__children.length == 0) {
                            parentNode.right = parentNode.left;
                        }
                    }
                }).error(function(data, status) {
                    alert("Error while deleting category.");
                });
            };

            /**
             * Fires modal window to edit a category node
             *
             * @param {{}} $node
             */
            $scope.editModal = function ($node) {

                var modalInstance = $modal.open({
                    templateUrl: $asset.getLink('template/editModal.html'),
                    controller: 'edit',
                    size: 'sm',
                    resolve: {
                        node: function () {
                            return $node;
                        }
                    }
                });
            };

            /**
             * Modal to add category node
             *
             * @param {{}} node
             */
            $scope.addChild = function (node) {

                var modalInstance = $modal.open({
                    templateUrl: $asset.getLink('template/editModal.html'),
                    controller: 'addChild',
                    size: 'sm',
                    resolve: {
                        node: function () {
                            return node;
                        }
                    }
                });
            };

            /**
             * Modal to create new root category
             */
            $scope.createRoot = function () {
                var modalInstance = $modal.open({
                    templateUrl: $asset.getLink('template/editModal.html'),
                    controller: 'addRoot',
                    resolve: {
                        rootNodes: function () {
                            return $scope.rootNodes;
                        }
                    }
                });

                modalInstance.result.then(function () {
                    if (Object.keys($scope.rootNodes).length == 1) {
                        $scope.rootNodeId = Object.keys($scope.rootNodes)[0];
                        categoryService.setRootNode($scope.mode, $scope.rootNodeId);
                        $scope.fetchTree(true, null);
                    }
                });
            };

            /**
             * Single node click handler
             *
             * @param {{}} node
             */
            $scope.selectNode = function (node) {
                if ($scope.selectedNode == node) {
                    $scope.matchService.clearNode();
                    $scope.suggestionsService.clearNode();
                } else {
                    $scope.selectedNode = node;
                    $scope.matchService.setNode(node, $scope.mode);
                    $scope.suggestionsService.setNode(node, $scope.mode);
                }
            }

            /**
             * Update matched nodes content
             */
            $scope.updateMatches = function() {
                $scope.selectedNode = null;
                $scope.matchModeActive = true;
            }

            /**
             * Clear all data regarding node matches
             */
            $scope.clearMatches = function() {
                $scope.matchModeActive = false;
                $scope.selectedNode = null;
            }

            /**
             * Returns rootId from root node
             *
             * @returns {String}
             */
            $scope.getRoot = function() {
                return $scope.rootNodeId;
            }

            /**
             * Can node be matched with selected node?
             *
             * @param {{}} node
             *
             * @returns {boolean}
             */
            $scope.canMatch = function(node) {
                if (!$scope.matchModeActive || $scope.selectedNode) {
                    return false;
                }

                // Is match already made
                if (node.id in $scope.matchedNodes) {
                    return false;
                }

                return true;
            }

            /**
             * Handler for container scroll event
             */
            $scope.treeScroll = function() {

                if ($scope.nodesLoading || $scope.scrollElements.length == 0) {
                    return;
                }

                var containerBottom, elementBottom, scrollElement;
                scrollElement = $scope.scrollElements[$scope.scrollElements.length - 1];

                containerBottom = $scope.containerElement.height();

                elementBottom = scrollElement.element.offset().top - $scope.containerElement.offset().top;
                elementBottom += scrollElement.element.height();

                if ((elementBottom - containerBottom) <= containerBottom) {
                    $scope.$apply(function () {

                        $scope.nodesLoading = true;
                        for (var i = 0; i < 10 && scrollElement.data.length > 0; i++) {
                            scrollElement.node.__children.push(scrollElement.data.shift());
                        }

                        if (scrollElement.data.length == 0) {
                            $scope.scrollElements.pop();
                        }

                        $scope.nodesLoading = false;
                    });
                }
            }

            /**
             * Clears nodes list and loads with proper format
             */
            $scope.changeListType = function() {
                $scope.categoryTree = [];
                $scope.scrollElements = [];

                if ($scope.listType == 'plain') {
                    $scope.containerElement.off('scroll', $scope.treeScroll);
                } else {
                    $scope.containerElement.on('scroll', $scope.treeScroll);
                }

                $scope.matchService.clearNode();
                $scope.suggestionsService.clearNode();
                $scope.categoryTree = [];

                $scope.fetchTree(false, null);
            }

            /**
             * Event handler for changed root node
             */
            $scope.rootChangeEvent = function() {
                if ($scope.listType == 'plain' && $scope.filteredMode) {
                    $scope.fetchPlainTree(false);
                }
            }

            /**
             * Event handler for added match
             *
             * @param {{}} node
             * @param {{}} matchedNode
             */
            $scope.matchEvent = function(node, matchedNode) {
                if ($scope.listType == 'tree' || !$scope.filteredMode) {
                    return;
                }

                for (var index = 0; index < $scope.categoryTree.length; ++index) {
                    var element = $scope.categoryTree[index];
                    if (element.id == node.id || element.id == matchedNode.id) {
                        $scope.categoryTree.splice(index, 1);
                    }
                }
            }

            /**
             * Event handler for removed match
             *
             * @param {{}} node
             */
            $scope.matchRemovedEvent = function(node) {
                if ($scope.listType == 'tree' || !$scope.filteredMode || $scope.selectedNode) {
                    return;
                }

                $scope.categoryTree.push(node);
            }

        }]);

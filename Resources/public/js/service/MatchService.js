/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

angular
    .module('service.match', [])
    .service('matchService', ['$http', 'categoryService',
        function($http, categoryService) {
            /**
             * Node on which matches are based.
             *
             * @type {{}}
             */
            this.node = null;

            /**
             * Root on which matches are based.
             *
             * @type {null}
             */
            this.rootId = null;

            /**
             * Currently active matches.
             *
             * @type {{}}
             */
            this.matches = {};

            /**
             * Back reference to controllers.
             *
             * @type {{}}
             */
            this.controllers = {master: {}, slave: {}};

            /**
             * Helper for mode inversion.
             *
             * @type {{master: String, slave: String}}
             */
            this.modeMatrix = { master: 'slave', slave: 'master' };

            /**
             * Currently active controller mode of set node.
             *
             * @type {String}
             */
            this.matchNodeProvider = null;

            /**
             *  Register a back reference to controller.
             *
             * @param {{}} controller
             * @param {String} mode
             */
            this.registerController = function(controller, mode) {
                this.controllers[mode] = controller;
            }

            /**
             * Notice controller to update matches on node selection.
             *
             * @param {{}} node
             * @param {String} mode
             */
            this.setNode = function(node, mode) {
                var controller = this.controllers[this.modeMatrix[mode]];

                this.node = node;
                this.matchNodeProvider = mode;
                this.rootId = controller.getRoot();

                if (this.rootId) {
                    this.updateMatches(controller);
                }
            }

            /**
             * Request matches from backend based on node and root.
             *
             * @param {{}} controller
             */
            this.updateMatches = function(controller) {

                var instance = this;
                $http({
                    method:"POST",
                    url: Routing.generate('ongr_category_manager_matches'),
                    data: { nodeId: this.node.id, rootId:  this.rootId}
                }).success(function(data, status) {
                    for (key in instance.matches) {
                        delete instance.matches[key];
                    }

                    for (key in data.matches) {
                        instance.matches[key] = data.matches[key];
                    }

                    // Notify controller to update its icons
                    controller.updateMatches();
                }).error(function(data, status) {
                    alert('Error retrieving category matches');
                });
            }

            /**
             * Remove all match data. Notify controllers.
             */
            this.clearNode = function()
            {
                this.node = null;
                this.matchNodeProvider = null;

                for (key in this.matches) {
                    delete this.matches[key];
                }

                for (key in this.controllers) {
                    this.controllers[key].clearMatches();
                }
            }

            /**
             * Notify on different root node selection.
             *
             * @param {String} mode
             */
            this.updateRoot = function(mode) {
                var newRootId = this.controllers[mode].getRoot();
                if (!this.node || newRootId == this.rootId) {
                    return;
                }

                if (mode == this.matchNodeProvider) {
                    this.clearNode();
                    return;
                }

                this.rootId = newRootId;
                controller = this.controllers[mode];

                this.updateMatches(controller);
            }

            /**
             * Match provided node with selected node.
             *
             * @param {{}} node
             */
            this.match = function(node) {
                if (!this.node) {
                    return;
                }

                var instance = this;
                $http({
                    method:"POST",
                    url: Routing.generate(ongr_category_managerr_match'),
                    data: { categoryId: this.node.id, matchId:  node.id }
                }).success(function(data, status) {
                    instance.matches[node.id] = {id: node.id, path: data.path};

                    for (var key in instance.controllers) {
                        instance.controllers[key].matchEvent(node, instance.node);
                    }

                }).error(function(data, status) {
                    alert('Error while matching categories');
                });
            }

            /**
             * Remove match between provided and selected nodes.
             *
             * @param {String} match
             */
            this.removeMatch = function(node) {
                if (!this.node) {
                    return;
                }

                var instance = this;
                $http({
                    method:"POST",
                    url: Routing.generateongr_category_managerer_remove_match'),
                    data: { categoryId: this.node.id, matchId:  node.id }
                }).success(function(data, status) {
                    delete instance.matches[node.id];

                    for (var key in instance.controllers) {
                        instance.controllers[key].matchRemovedEvent(node);
                    }
                }).error(function(data, status) {
                    alert('Error while removing match');
                });
            }

            /**
             * Returns reference to matches.
             *
             * @returns {{}}
             */
            this.getMatches = function() {
                return this.matches;
            }

            /**
             * Returns selected root node id from opposing controller.
             *
             * @param {String} mode
             *
             * @return {String}
             */
            this.getMatchRoot = function(mode) {
                var controller = this.controllers[this.modeMatrix[mode]];

                return controller.getRoot();
            }

            /**
             * Fires matchRootChanged event in opposing controller.
             *
             * @param {String} mode
             */
            this.rootNodeChange = function(mode) {
                var controller = this.controllers[this.modeMatrix[mode]];

                controller.rootChangeEvent();
            }
        }
    ]);

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

angular
    .module('service.category', [])
    .service('categoryService', ['DATA',
        function(DATA) {
            /**
             * Root nodes from back end
             *
             * @type {{}}
             */
            this.rootNodes = DATA.root_nodes;

            /**
             * Root nodes passed to master controller
             *
             * @type {{}}
             */
            this.masterNodes = {};

            /**
             * Root nodes passed to slave controller
             *
             * @type {{}}
             */
            this.slaveNodes = {};

            /**
             * Initialization of service
             */
            this.init = function() {
                this.syncNodes(this.masterNodes, null);
                this.syncNodes(this.slaveNodes, null);
            }

            /**
             * Returns nodes reference by controller mode
             *
             * @param {string} mode
             *
             * @returns {{}}
             */
            this.getRootNodes = function(mode) {
                var nodes = {};

                if (mode == 'master') {
                    nodes = this.masterNodes;
                } else {
                    nodes = this.slaveNodes;
                }

                return nodes;
            }

            /**
             * Invoked on active root node change
             *
             * @param {String} mode
             * @param {String} rootNode
             */
            this.setRootNode = function(mode, rootNode) {
                if (rootNode == 0) {
                    return;
                }

                if (mode == 'master') {
                    this.syncNodes(this.slaveNodes, rootNode);
                } else {
                    this.syncNodes(this.masterNodes, rootNode);
                }
            }

            /**
             * Updates controller referenced rootNodes
             *
             * @param nodes
             * @param rootNode
             */
            this.syncNodes = function(nodes, rootNode) {
                var rootId = null;

                for (var key in this.rootNodes) {
                    // Preserve root id while overwriting nodes
                    if (key in nodes) {
                        rootId = nodes[key].root;
                    } else {
                        rootId = null;
                    }

                    nodes[key] = angular.copy(this.rootNodes[key]);
                    nodes[key].root = rootId;
                }

                if (rootNode != null) {
                    delete nodes[rootNode];
                }

                if (Object.keys(nodes).length < 1) {
                    nodes['0'] = this.getDummyRoot();
                }
            }

            /**
             * Adds a new node to all objects
             *
             * @param {{}} node
             */
            this.addRootNode = function(node) {
                this.rootNodes[node.id] = node;
                this.masterNodes[node.id] = node;
                this.slaveNodes[node.id] = node;

                delete this.masterNodes['0'];
                delete this.slaveNodes['0'];
            }

            /**
             * Removes a node from all objects
             *
             * @param {{}} node
             */
            this.deleteRootNode = function(node) {
                delete this.rootNodes[node.id];
                delete this.masterNodes[node.id];
                delete this.slaveNodes[node.id];
            }

            /**
             * Sync all root nodes with new value
             *
             * @param node
             */
            this.updateRootNode = function(node) {
                localNodes = [this.rootNodes, this.masterNodes, this.slaveNodes];
                for (key in localNodes) {
                    nodes = localNodes[key];
                    if (node.id in nodes) {
                        nodes[node.id] = node;
                    }
                }
            }

            /**
             * Returns dummy root node
             *
             * @returns {{}}
             */
            this.getDummyRoot = function() {
                return {id: '0', title: 'No root nodes'};
            }

            /**
             * Init this service
             */
            this.init();
        }
    ]);

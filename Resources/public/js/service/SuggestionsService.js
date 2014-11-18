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
    .module('service.suggestions', [])
    .service('suggestionsService', ['$http',
        function($http) {
            /**
             * Back reference to list controllers
             *
             * @type {{}}
             */
            this.listControllers = {master: {}, slave: {}};

            /**
             * Helper for mode inversion
             *
             * @type {{master: String, slave: String}}
             */
            this.modeMatrix = { master: 'slave', slave: 'master' };

            /**
             * Available suggestions
             *
             * @type {{}[]}
             */
            this.suggestions = [];

            /**
             * Selected node
             *
             * @type {{}}
             */
            this.node = null;

            /**
             * Selected node provider
             *
             * @type {String}
             */
            this.nodeProvider = null;

            /**
             * Root id
             *
             * @type {String}
             */
            this.rootId = null;

            /**
             *  Register a back reference to list controller
             *
             * @param {{}} controller
             * @param {String} mode
             */
            this.registerListController = function(controller, mode) {
                this.listControllers[mode] = controller;
            }

            /**
             * Notice controller to update matches on node selection
             *
             * @param {{}} node
             * @param {String} mode
             */
            this.setNode = function(node, mode) {
                var controller = this.listControllers[this.modeMatrix[mode]];

                this.node = node;
                this.nodeProvider = mode;
                this.rootId = controller.getRoot();

                if (this.rootId) {
                    this.updateSuggestions();
                }
            }

            /**
             * Node was deselected, remove suggestions
             */
            this.clearNode = function() {
                this.suggestions.length = 0;
                this.node = null;
                this.rootid = null;
                this.nodeProvider = null;
            }

            /**
             * Notify on different root node selection
             *
             * @param {String} mode
             */
            this.updateRoot = function(mode) {
                var newRootId = this.listControllers[mode].getRoot();

                if (!this.node || newRootId == this.rootId) {
                    return;
                }

                if (mode == this.nodeProvider) {
                    this.clearNode();
                    return;
                }

                this.rootId = newRootId;
                this.updateSuggestions();
            }

            /**
             * Request suggestions from backend based on node and root
             */
            this.updateSuggestions = function() {
                var instance = this;

                $http({
                    method:"POST",
                    url: Routing.generate('fox_category_manager_suggestions'),
                    data: { nodeId: this.node.id, rootId:  this.rootId}
                }).success(function(data, status) {
                    // Cant loose pointer reference here
                    instance.suggestions.length = 0;
                    instance.suggestions.push.apply(instance.suggestions, data.suggestions);
                }).error(function(data, status) {
                    alert('Error retrieving suggestions');
                });
            }

            /**
             * Returns reference to available suggestions
             *
             * @returns {[]}
             */
            this.getSuggestions = function()
            {
                return this.suggestions;
            }
        }
    ]);

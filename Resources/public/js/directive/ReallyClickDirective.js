/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

angular
    .module('directive.reallyClick', [])
    .directive('reallyClick', [function () {
        return {
            restrict: 'A',
            link: function(scope, element, attrs) {
                element.bind('click', function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    var message = attrs.reallyMessage;
                    if (message && confirm(message)) {
                        scope.$apply(attrs.reallyClick);
                    }
                });
            }
        }
    }]);

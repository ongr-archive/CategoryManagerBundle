/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

angular
    .module('util.asset', [])
    .service('asset', ['$location', function(location) {
        /**
         * Returns a link for js asset.
         *
         * @param {String} asset
         */
        this.getLink = function(asset) {
            host = location.$$host;
            protocol = location.$$protocol;

            return protocol + '://' + host + '/bundles/ongrcategorymanager/js/' + asset;
        }
    }]);

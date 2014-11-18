/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

angular
    .module('ongr.category-manager', [
        'ui.bootstrap',
        'ui.tree',
        'infinite-scroll',
        'util.asset',
        'directive.inline',
        'directive.reallyClick',
        'service.category',
        'service.match',
        'service.suggestions',
        'controller.list',
        'controller.suggestions',
        'controller.matchManager',
        'controller.modal.edit',
        'controller.modal.addChild',
        'controller.modal.addRoot'
    ])
    .constant('DATA', categoriesData);

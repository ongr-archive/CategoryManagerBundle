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
    .module('fox.category-manager', [
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

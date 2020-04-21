/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'getloy_magento_gateway',
                component: 'Getloy_GetloyMagentoGateway/js/view/payment/method-renderer/getloy_magento_gateway'
            }
        );
        return Component.extend({});
    }
);

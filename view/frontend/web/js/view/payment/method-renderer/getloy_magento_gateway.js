/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Getloy_GetloyMagentoGateway/js/action/create-payment',
        'mage/url',
        'mage/storage',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/redirect-on-success'
    ],
    function (Component, quote, createPaymentAction, url, storage, setPaymentInformationAction, errorProcessor, fullScreenLoader, redirectOnSuccessAction) {
        'use strict';

        return Component.extend(
            {
                defaults: {
                    template: 'Getloy_GetloyMagentoGateway/payment/form',
                    redirectAfterPlaceOrder: true,
                },

                getCode: function () {
                    return 'getloy_magento_gateway';
                },

                getConfig: function () {
                    return window.checkoutConfig.payment[this.getCode()];
                },

                getTitle: function () {
                    return this.getConfig()['title'];
                },

                getPaymentMethodLogos: function () {
                    return this.getConfig()['payment_method_logos'];
                },

                placeOrder: function () {
                    this.isPlaceOrderActionAllowed(false);
                    setPaymentInformationAction(this.messageContainer, this.getData())
                    .done(
                        function ( response ) {
                            fullScreenLoader.startLoader();
                            createPaymentAction(this.getData(), this.messageContainer)
                                .done(
                                    function (payment) {
                                        !function(g,e,t,l,o,y){g.GetLoyPayments=t;g[t]||(g[t]=function(){
                                        (g[t].q=g[t].q||[]).push(arguments)});g[t].l=+new Date;o=e.createElement(l);
                                        y=e.getElementsByTagName(l)[0];o.src='https://some.getloy.com/dev/getloy.js';
                                        y.parentNode.insertBefore(o,y)}(window,document,'gl','script');
                                        gl('payload', payment[0].payload);
                                        gl('success_callback', this.afterPlaceOrder.bind(this));
                                    }.bind(this)
                                )
                                .fail(
                                    function (response) {
                                        this.isPlaceOrderActionAllowed(true);
                                        errorProcessor.process(response, this.messageContainer);
                                    }.bind(this)
                                )
                                .always(
                                    function () {
                                        fullScreenLoader.stopLoader();
                                    }
                                );
                        }.bind(this)
                    )
                    .fail(
                        function (response) {
                            this.isPlaceOrderActionAllowed(true);
                            errorProcessor.process(response, this.messageContainer);
                        }.bind(this)
                    );
                },

                afterPlaceOrder: function () {
                    if (this.redirectAfterPlaceOrder) {
                        redirectOnSuccessAction.execute();
                    }
                }    
            }
        );
    }
);
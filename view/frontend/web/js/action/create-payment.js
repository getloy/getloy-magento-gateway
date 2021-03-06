define(
    [
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/error-processor'
    ],
    function (quote, urlBuilder, storage, customer, errorProcessor) {
        'use strict';

        return function (paymentData, messageContainer) {
            var serviceUrl, payload;
            if (customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl('/getloy/payment/create', {});
                payload = {
                    cartId: quote.getQuoteId(),
                    paymentMethod: paymentData,
                    billingAddress: quote.billingAddress()
                };
            } else {
                serviceUrl = urlBuilder.createUrl(
                    '/getloy/payment/create-guest', {
                        quoteId: quote.getQuoteId()
                    }
                );
                payload = {
                    cartId: quote.getQuoteId(),
                    email: quote.guestEmail,
                    paymentMethod: paymentData,
                    billingAddress: quote.billingAddress()
                };
            }

            return storage
                .post(serviceUrl, JSON.stringify(payload))
                .fail(
                    function (response) {
                        errorProcessor.process(response, messageContainer);
                    }
                );
        };
    }
);

define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Aheadworks_Giftcard/js/model/resource-url-manager',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Ui/js/model/messageList',
        'mage/storage',
        'Magento_Checkout/js/action/get-totals',
        'mage/translate',
        'Magento_Checkout/js/model/payment/method-list',
        'Magento_Checkout/js/action/get-payment-information'
    ],
    function (
        ko,
        $,
        quote,
        urlManager,
        paymentService,
        errorProcessor,
        messageList,
        storage,
        getTotalsAction,
        $t,
        paymentMethodList,
        paymentInformationAction
        ) {
        'use strict';
        return function (giftCardCode, deferred) {
            var url = urlManager.getApplyGiftCardCodeUrl(giftCardCode);
            return storage.put(
                url,
                {},
                false
            ).done(
                function (response) {
                    if (response) {
                        var message = $t(response.message);
                        if (response.success) {
                            paymentInformationAction();
                            getTotalsAction([], deferred);
                            $.when(deferred).done(function() {
                                paymentService.setPaymentMethods(
                                    paymentMethodList()
                                );
                            });
                            messageList.addSuccessMessage({'message': message});
                        } else {
                            deferred.resolve();
                            messageList.addErrorMessage({'message': message});
                        }
                    } else {
                        deferred.resolve();
                    }
                }
            ).fail(
                function (response) {
                    deferred.reject();
                    errorProcessor.process(response);
                }
            );
        };
    }
);

define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'Aheadworks_Giftcard/js/model/customer/giftcard',
        'Aheadworks_Giftcard/js/action/apply-giftcard-code',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils'
    ],
    function ($, ko, Component, customerGiftCard, applyAction, quote, priceUtils) {
        'use strict';

        var giftCardCode = ko.observable(null);
        var isLoading = ko.observable(false);

        var addGiftCardCode = function(code) {
            isLoading(true);
            var deferred = $.Deferred();
            applyAction(code, deferred);
            $.when(deferred).done(function() {
                $('#aw-giftcard-codes-block').find('tr').each(function() {
                    if ($(this).data('giftcard-code') == code) {
                        $(this).hide();
                    }
                });
                giftCardCode('');
                isLoading(false);
            });
            $.when(deferred).fail(function() {
                isLoading(false);
            });
        };

        return Component.extend({
            defaults: {
                template: 'Aheadworks_Giftcard/payment/giftcard'
            },
            isLoading: isLoading,
            giftCardCode: giftCardCode,

            getCustomerGiftCardData: function() {
                return customerGiftCard.getGiftCards();
            },
            isCustomerGiftCardsDisplayed: function() {
                return this.getCustomerGiftCardData().length > 0;
            },
            formatPrice: function(amount) {
                return priceUtils.formatPrice(amount, quote.getPriceFormat());
            },
            apply: function() {
                if (this.validate()) {
                    addGiftCardCode(giftCardCode());
                }
            },
            applyByCode: function(giftCard) {
                addGiftCardCode(giftCard.code);
            },
            validate: function() {
                var form = '#aw-giftcard-form';
                return $(form).validation() && $(form).validation('isValid');
            }
        });
    }
);

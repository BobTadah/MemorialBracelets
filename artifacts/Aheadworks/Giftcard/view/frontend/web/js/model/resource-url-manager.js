define(
    [
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/url-builder',
        'mageUtils'
    ],
    function(customer, urlBuilder, utils) {
        "use strict";
        return {
            getApplyGiftCardCodeUrl: function(giftCardCode) {
                return urlBuilder.createUrl('/awGiftcard/apply/:giftCardCode', {giftCardCode: giftCardCode});
            },
            getRemoveGiftCardCodeUrl: function(giftCardCode, referenceId) {
                return urlBuilder.createUrl('awGiftcard/remove/:giftCardCode/:referenceId', {giftCardCode: giftCardCode, referenceId: referenceId});
            },
            getAppliedGiftCardCodesUrl: function() {
                return urlBuilder.createUrl('/awGiftcard/get-applied', {});
            }
        };
    }
);

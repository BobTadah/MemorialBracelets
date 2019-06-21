define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Aheadworks_Giftcard/js/model/quote/giftcard'
    ],
    function (Component, giftCard) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Aheadworks_Giftcard/checkout/summary/giftcard'
            },
            giftCardsData: giftCard.giftcards,
            getValue: function (amount) {
                return this.getFormattedPrice(amount);
            }
        });
    }
);

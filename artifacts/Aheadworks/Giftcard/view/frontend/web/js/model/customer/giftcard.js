define(
    ['ko'],
    function (ko) {
        'use strict';
        return {
            giftcards: window.checkoutConfig.aw_giftcards.customer,
            getGiftCards: function() {
                return this.giftcards || [];
            }
        };
    }
);

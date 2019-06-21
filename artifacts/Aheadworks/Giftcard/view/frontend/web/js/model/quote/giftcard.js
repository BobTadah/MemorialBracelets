define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'Aheadworks_Giftcard/js/action/get-applied-giftcard-codes'
    ],
    function ($, ko, quote, totals, getAppliedGiftCardCodesAction) {
        'use strict';

        var awAppliedGiftCardsData = window.checkoutConfig.aw_giftcards.applied;
        var awAppliedGiftCards = ko.observableArray(awAppliedGiftCardsData);
        var deferred;

        var giftCardQuoteModel = {
            giftcards: awAppliedGiftCards,
            getGiftCards: function() {
                return this.giftcards || [];
            },
            setGiftCards: function (giftcards) {
                awAppliedGiftCards(giftcards);
            }
        };

        var getAppliedGiftCardCodes = function(showLoader) {
            if (showLoader) {
                totals.isLoading(true);
            }
            deferred = $.Deferred();
            getAppliedGiftCardCodesAction(giftCardQuoteModel, deferred);
            $.when(deferred).done(function() {
                totals.isLoading(false);
            });
        };
        getAppliedGiftCardCodes(false);
        quote.getTotals().subscribe(function() {
            getAppliedGiftCardCodes(true);
        });

        // Bad! Need to be refactored
        totals.isLoading.subscribe(function(value) {
            if (deferred && deferred.state() == 'pending' && !value) {
                setTimeout(function() {
                    if (!totals.isLoading()) {
                        totals.isLoading(true);
                    }
                }, 10);
            }
        });

        return giftCardQuoteModel;
    }
);

define(
    [
        'ko',
        'jquery',
        'Aheadworks_Giftcard/js/model/resource-url-manager',
        'mage/storage'
    ],
    function (
        ko,
        $,
        urlManager,
        storage
        ) {

        'use strict';

        return function (quoteGiftCard, deferred) {
            return storage.get(
                urlManager.getAppliedGiftCardCodesUrl(),
                    false
                ).done(
                    function (response) {
                        quoteGiftCard.setGiftCards(response);
                        deferred.resolve();
                    }
                ).error(
                    function (response) {
                        deferred.reject();
                    }
                )
            ;
        };
    }
);

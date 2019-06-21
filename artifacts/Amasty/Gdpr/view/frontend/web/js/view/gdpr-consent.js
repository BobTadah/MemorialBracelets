define(
    [
        'ko',
        'jquery',
        'uiComponent',
        'Magento_Checkout/js/model/quote'
    ],
    function (ko, $, Component, quote) {
        'use strict';
        var checkoutConfig = window.checkoutConfig,
            gdprConfig = checkoutConfig ? checkoutConfig.amastyGdprConsent : {};

        return Component.extend({
            defaults: {
                template: 'Amasty_Gdpr/checkout/gdpr-consent'
            },
            isVisible: ko.observable(gdprConfig.isVisible),
            checkboxText: gdprConfig.checkboxText,

            initialize: function () {
                this._super();

                quote.billingAddress.subscribe(function () {
                    var country = quote.billingAddress().countryId;

                    if (!country) {
                        return;
                    }

                    var isVisible = gdprConfig.isEnabled,
                        countryFilter = gdprConfig.visibleInCountries;

                    if (countryFilter) {
                        isVisible &= countryFilter.indexOf(country) !== -1;
                    }

                    this.isVisible(isVisible);
                }.bind(this));

                return this;
            },

            initModal: function (element) {
                $(element).find('a').click(function (e) {
                    e.preventDefault();
                    $('#amprivacy-popup').modal('openModal');
                })
            }
        });
    }
);

define([
    'jquery',
    'mage/url',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'domReady!'
], function ($, url, Component, customerData) {
    'use strict';

    let self;

    const adjustMs = 1000,
        customerCookieCheck = 5,
        minCustomerLoggedCheck = 600;

    return Component.extend({
        isCustomerLogged: false,

        initialize: function (config) {
            this._super();
            this.customerCookieCheck(config);
        },

        customerCookieCheck: function (config) {
            self = this;
            setTimeout(function () {
                if (!!customerData.get('customer')().firstname) {
                    self.customerIntervalCheck(config.cookieLifetime);
                }
            }, customerCookieCheck * adjustMs);
        },

        customerIntervalCheck: function (cookieLifetime) {
            self = this;
            let intervalTime = cookieLifetime >= minCustomerLoggedCheck ? minCustomerLoggedCheck : cookieLifetime,
                logout = setInterval(function () {
                    $.ajax({
                        method: 'POST',
                        url: url.build('/logoutmessage/index/logged'),
                        dataType: 'json',
                    }).done(function (result) {
                        if (result === false) {
                            clearInterval(logout);
                        }
                    });
                }, intervalTime * adjustMs);
        }
    });
});
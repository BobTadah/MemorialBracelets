/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'mage/validation'
    ],
    function ($) {
        'use strict';
        var checkoutConfig = window.checkoutConfig,
            gdprConfig = checkoutConfig ? checkoutConfig.amastyGdprConsent : {};

        var consentInputPath = '.payment-method._active [data-role="amasty-gdpr-consent"] input';

        return {
            /**
             * Validate checkout agreements
             *
             * @returns {boolean}
             */
            validate: function() {
                if (!gdprConfig.isEnabled) {
                    return true;
                }

                if (!$(consentInputPath).is(':visible')) {
                    return true;
                }

                return $('#co-payment-form').validate({
                    errorClass: 'mage-error',
                    errorElement: 'div',
                    meta: 'validate',
                    errorPlacement: function (error, element) {
                        element.siblings('label').last().after(error);
                    }
                }).element(consentInputPath);
            }
        }
    }
);

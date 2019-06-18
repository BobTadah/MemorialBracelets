/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
        'jquery',
        'Magento_Customer/js/model/authentication-popup',
        'Magento_Customer/js/customer-data'
    ],
    function ($, authenticationPopup, customerData) {
        'use strict';

        return function (config, element) {
            function initialCheck() {
                $('.initials-container').removeClass('failed');
                var initialsBoxes= $('.initials');
                var flag = true;
                $.each(initialsBoxes, function () {
                    var initial = $(this).val();
                    if (initial == '') {
                        flag = false;
                        $(this).parent().addClass('failed');
                        $('#proceed-to-checkout-button').find('span.text').text('Enter Initials and Proceed to Checkout')
                    }
                    if(initial.length > 3) { // just in case they remove html max char limit
                        $(this).val(initial.substring(0,3));
                    }
                });

                return flag;
            }

            $('.cart-container').on('keyup', '.initials', function(event) {
                if(initialCheck()) {
                    $('#proceed-to-checkout-button').find('span.text').text('Proceed to Checkout')
                }
            });

            $(element).click(function (event) {
                var cart = customerData.get('cart'),
                    customer = customerData.get('customer');

                event.preventDefault();

                if (!customer().firstname && cart().isGuestCheckoutAllowed === false) {
                    authenticationPopup.showModal();

                    return false;
                }

                if(!initialCheck()) {
                    return false;
                }

                location.href = config.checkoutUrl;
            });

        };
    }
);

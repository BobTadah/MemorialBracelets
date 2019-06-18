/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/translate',
    'jquery/ui'
], function($, $t) {
    "use strict";

    $.widget('mage.catalogAddToCart', {

        options: {
            processStart: null,
            processStop: null,
            bindSubmit: true,
            minicartSelector: '[data-block="minicart"]',
            messagesSelector: '[data-placeholder="messages"]',
            productStatusSelector: '.stock.available',
            addToCartButtonSelector: '.action.tocart',
            addToCartButtonDisabledClass: 'disabled',
            addToCartButtonTextWhileAdding: '',
            addToCartButtonTextAdded: '',
            addToCartButtonTextDefault: ''
        },

        _create: function() {
            if (this.options.bindSubmit) {
                this._bindSubmit();
            }
        },

        _bindSubmit: function() {
            var self = this;
            this.element.mage('validation');
            this.element.on('submit', function(e) {
                e.preventDefault();
                if(self.element.valid()) {
                    self.submitForm($(this));
                }
            });
        },

        isLoaderEnabled: function() {
            return this.options.processStart && this.options.processStop;
        },

        /**
         * Handler for the form 'submit' event
         *
         * @param {Object} form
         */
        submitForm: function (form) {
            var hasEngraving = false;

            // This for the scenario where the PDP has different tyeps of engravings.
            // That's why we have to check the .lastActive class in charge of displaying the correct set of engraving lines.
            if ($("#product-options-wrapper > .lastActive").length > 0) {
                //Iterate over the active group of inputs to check their values.
                $("#product-options-wrapper > .lastActive > #engraving-wrapper").find('.combo-container > input').each(function () {
                    if ($(this).val()) {
                        hasEngraving = true;
                        return false;
                    }
                });
            } else {
                //In this case we only have one set of engraving lines. So can simply avoid the .lastActive selector.
                $("#engraving-wrapper").find('.combo-container > input').each(function () {
                    if ($(this).val()) {
                        hasEngraving = true;
                        return false;
                    }
                });
            }

            //If doesn't has engraving, show error.
            //By checking for $("#engraving-wrapper").length > 0 we know if product has a set of engraving lines that must be validated.
            if (!hasEngraving && $("#engraving-wrapper").length > 0) {
                window.addToCartActionStatus = "error";
                this.options.addToCartButtonTextAdded = 'Failed';
                $('.ajax-messages').html('Please specify at least one engraving line text.');
                $('.ajax-messages').removeClass('message success').addClass('message error');
            } else {
                var addToCartButton, self = this;

                if (form.has('input[type="file"]').length && form.find('input[type="file"]').val() !== '') {
                    self.element.off('submit');
                    // disable 'Add to Cart' button
                    addToCartButton = $(form).find(this.options.addToCartButtonSelector);
                    addToCartButton.prop('disabled', true);
                    addToCartButton.addClass(this.options.addToCartButtonDisabledClass);
                    form.submit();
                } else {
                    self.ajaxSubmit(form);
                }
            }
        },

        ajaxSubmit: function(form) {
            var self = this;
            $(self.options.minicartSelector).trigger('contentLoading');
            self.disableAddToCartButton(form);

            $.ajax({
                url: form.attr('action'),
                data: form.serialize(),
                type: 'post',
                dataType: 'json',
                beforeSend: function() {
                    if (self.isLoaderEnabled()) {
                        $('body').trigger(self.options.processStart);
                    }
                },
                success: function(res) {
                    if (self.isLoaderEnabled()) {
                        $('body').trigger(self.options.processStop);
                    }

                    if (res.backUrl) {
                        window.location = res.backUrl;
                        return;
                    }
                    if (res.messages) {
                        //without formatting messages look weird, so let PHP handle it
                        $('.ajax-messages').html(res.messages);
                    }
                    if (res.minicart) {
                        $(self.options.minicartSelector).replaceWith(res.minicart);
                        $(self.options.minicartSelector).trigger('contentUpdated');
                    }
                    if (res.product && res.product.statusText) {
                        $(self.options.productStatusSelector)
                            .removeClass('available')
                            .addClass('unavailable')
                            .find('span')
                            .html(res.product.statusText);
                    }
                    if (res.status ) {
                        if (res.status == 'error') {
                            window.addToCartActionStatus = "error";
                            self.options.addToCartButtonTextAdded = 'Failed';
                            $('.ajax-messages').removeClass('message success')
                                .addClass('message error')

                        }
                        else {
                            window.addToCartActionStatus = "success";
                            self.options.addToCartButtonTextAdded = 'Added';
                            $('.ajax-messages').removeClass('message error')
                                .addClass('message success')
                        }
                    }
                    self.enableAddToCartButton(form);
                }
            });
        },

        disableAddToCartButton: function(form) {
            var addToCartButtonTextWhileAdding = this.options.addToCartButtonTextWhileAdding || $t('Adding...');
            var addToCartButton = $(form).find(this.options.addToCartButtonSelector);
            addToCartButton.addClass(this.options.addToCartButtonDisabledClass);
            addToCartButton.find('span').text(addToCartButtonTextWhileAdding);
            addToCartButton.attr('title', addToCartButtonTextWhileAdding);
        },

        enableAddToCartButton: function(form) {
            var addToCartButtonTextAdded = this.options.addToCartButtonTextAdded || $t('Added');
            var self = this,
                addToCartButton = $(form).find(this.options.addToCartButtonSelector);

            addToCartButton.find('span').text(addToCartButtonTextAdded);
            addToCartButton.attr('title', addToCartButtonTextAdded);

            setTimeout(function() {
                var addToCartButtonTextDefault = self.options.addToCartButtonTextDefault || $t('Add to Cart');
                addToCartButton.removeClass(self.options.addToCartButtonDisabledClass);
                addToCartButton.find('span').text(addToCartButtonTextDefault);
                addToCartButton.attr('title', addToCartButtonTextDefault);
            }, 1000);
        }
    });

    return $.mage.catalogAddToCart;
});

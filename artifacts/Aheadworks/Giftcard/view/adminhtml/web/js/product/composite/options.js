define([
    'jquery',
    'jquery/validate',
    'jquery/ui'
], function($){
    'use strict';

    $.widget("awgc.giftCardCompositeOptions", {
        options: {
        },
        _create: function() {
            this._bind();
            this._initValidators();
            this.amountChange();
        },
        destroy: function() {
            this._unbind();
        },
        _bind: function() {
            $(this.options.amountsSelector).on('change', $.proxy(this.amountChange, this));
        },
        _unbind: function() {
            $(this.options.amountsSelector).off('change');
        },
        amountChange: function() {
            if ($(this.options.amountsSelector).val() == 'custom') {
                $("[data-show=custom]")
                    .show()
                    .addClass('required')
                    .addClass('_required')
                ;
                $("[data-show=custom] input").removeAttr('disabled');
                $(this.options.amountsSelector).removeAttr('price').find('option').removeAttr('price');
                $(this.options.customAmountSelector).on('change', $.proxy(this.customAmountChange, this));
            } else {
                $("[data-show=custom]")
                    .hide()
                    .removeClass('required')
                    .removeClass('_required')
                ;
                $("[data-show=custom] input").attr('disabled', 'disabled');
                $(this.options.amountsSelector)
                    .attr('price', $(this.options.amountsSelector).val())
                    .find('option')
                    .removeAttr('price')
                ;
                $(this.options.amountsSelector)
                    .find('option:selected')
                    .attr('price', $(this.options.amountsSelector).val())
                ;
                $(this.options.customAmountSelector)
                    .removeAttr('price')
                    .off('change')
                ;
            }
        },
        customAmountChange: function() {
            var customAmount = $(this.options.customAmountSelector);
            customAmount.attr('price', customAmount.val());
        },
        _initValidators: function() {
            $.validator.addMethod(
                'aw-gc-min-amount',
                $.proxy(this._validateMinAmount, this),
                'Entered amount is too low'
            );
            $.validator.addMethod(
                'aw-gc-max-amount',
                $.proxy(this._validateMaxAmount, this),
                'Entered amount is too high'
            );
        },
        _validateMinAmount: function(value, element, params) {
            return value >= $(element).data('min-value');
        },
        _validateMaxAmount: function(value, element, params) {
            return value <= $(element).data('max-value');
        }
    });

    return $.awgc.giftCardCompositeOptions;
});
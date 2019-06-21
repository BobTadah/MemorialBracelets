define([
    'jquery',
    'jquery/validate',
    'jquery/ui'
], function($){
    'use strict';

    $.widget("awgc.productAllowOpenAmountControl", {
        options: {
        },
        _create: function() {
            this._bind();
            this._initValidators();
            this.checkboxClick();
        },
        destroy: function() {
            this._unbind();
        },
        _bind: function() {
            $(this.element).on('click', $.proxy(this.checkboxClick, this));
        },
        _unbind: function() {
            $(this.element).off('click');
        },
        _initValidators: function() {
            $.validator.addMethod(
                'aw-gc-max-amount',
                $.proxy(this._validateMaxAmount, this),
                this.options.errorMessageMaxAmount
            );
        },
        _validateMaxAmount: function(value, element, params) {
            var minValue = $.mage.parseNumber($(this.options.openAmountMinSelector).val());
            var maxValue = $.mage.parseNumber(value);
            return !(!isNaN(minValue) && !isNaN(maxValue) && minValue >= maxValue);
        },
        checkboxClick: function() {
            if ($(this.element).prop('checked')) {
                this._enableInput($(this.options.openAmountMinSelector));
                this._enableInput($(this.options.openAmountMaxSelector));
                $(this.options.openAmountMaxSelector).addClass('aw-gc-max-amount');
                $(this.options.valueSelector).val(1);
            } else {
                this._disableInput($(this.options.openAmountMinSelector));
                this._disableInput($(this.options.openAmountMaxSelector));
                $(this.options.openAmountMaxSelector).removeClass('aw-gc-max-amount');
                $(this.options.valueSelector).val(0);
            }
        },
        _enableInput: function(element) {
            element
                .removeAttr('disabled')
                .addClass('required-entry')
                .addClass('_required')
            ;
            $('.field-' + element.attr('id')).addClass('required');
        },
        _disableInput: function(element) {
            element
                .attr('disabled', 'disabled')
                .removeClass('required-entry')
                .removeClass('_required')
                .val('')
            ;
            $('.field-' + element.attr('id')).removeClass('required');
        }
    });

    return $.awgc.productAllowOpenAmountControl;
});
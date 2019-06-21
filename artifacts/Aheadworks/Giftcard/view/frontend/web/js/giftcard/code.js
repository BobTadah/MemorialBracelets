define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";

    $.widget('awgc.code', {
        options: {
        },
        _create: function () {
            this.giftcardCodeCode = $(this.options.giftcardCodeSelector);
            this.checkGiftcard = $(this.options.checkGiftcardSelector);
            this._bind();
        },
        destroy: function() {
            this._unbind();
        },
        _bind: function() {
            $(this.options.applyButton).on('click', $.proxy(this._apply, this));
            $(this.options.checkButton).on('click', $.proxy(this._check, this));
        },
        _unbind: function() {
            $(this.options.applyButton).off('click');
            $(this.options.checkButton).off('click');
        },
        _apply: function() {
            this.giftcardCodeCode.attr('data-validate', '{required:true}');
            this.checkGiftcard.attr('value', '0');
            $(this.element).validation().submit();
        },
        _check: function() {
            this.giftcardCodeCode.attr('data-validate', '{required:true}');
            this.checkGiftcard.attr('value', '1');
            $(this.element).validation().submit();
        }
    });

    return $.awgc.code;
});
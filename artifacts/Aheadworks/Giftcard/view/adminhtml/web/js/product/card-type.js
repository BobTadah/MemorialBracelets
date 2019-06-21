define([
    'jquery',
    'jquery/ui'
], function($){
    'use strict';

    $.widget("awgc.productCardTypeControl", {
        options: {
            tabsSelector: '[data-form=edit-product] [data-role=tabs]',
            infoTabsSelector: '#product_info_tabs'
        },
        _create: function() {
            $(this.options.weightSwitcherSelector).hide();
            this.weight = $(this.options.weightSelector);
            this._bind();
            this.typeChange();
        },
        destroy: function() {
            this._unbind();
        },
        _bind: function() {
            $(this.element).on('change', $.proxy(this.typeChange, this));
            $(this.options.tabsSelector).on('contentUpdated', $.proxy(this.typeChange, this));
            $(this.options.infoTabsSelector).on("beforePanelsMove tabscreate tabsactivate", $.proxy(this.typeChange, this));
        },
        _unbind: function() {
            $(this.element).off('change');
            $(this.options.tabsSelector).off('contentUpdated', $.proxy(this.typeChange, this));
            $(this.options.infoTabsSelector).off("beforePanelsMove tabscreate tabsactivate", $.proxy(this.typeChange, this));
        },
        typeChange: function() {
            if ($.inArray($(this.element).val(), this.options.allowWeightValues) != -1) {
                this._enableInput(this.weight);
            } else {
                this._disableInput(this.weight);
            }
        },
        _enableInput: function(element) {
            element
                .removeClass('ignore-validate')
                .prop('disabled', false)
                .removeAttr('data-locked')
            ;
        },
        _disableInput: function(element) {
            element
                .addClass('ignore-validate')
                .prop('disabled', true)
                .attr('data-locked', 'locked')
            ;
        }
    });

    return $.awgc.productCardTypeControl;
});
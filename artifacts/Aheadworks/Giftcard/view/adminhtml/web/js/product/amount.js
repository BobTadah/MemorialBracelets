define([
    'jquery',
    'mage/template',
    'jquery/validate',
    'jquery/ui'
], function($, mageTemplate){
    'use strict';

    $.widget("awgc.productAmountControl", {
        itemsCount: 0,
        options: {
        },
        validatedAmounts: [],
        _create: function() {
            this.template = mageTemplate(this.options.amountsRowTemplate);
            var me = this;
            $.each(this.options.values, function() {
                me.addItem(this.website_id, this.price);
            });
            this._bind();
            this._initValidators();
        },
        destroy: function() {
            this._unbind();
        },
        _bind: function() {
            $(this.options.addBtnSelector).on('click', $.proxy(this.addBtnClick, this));
            $(this.element).on('click', 'button.delete', $.proxy(this.deleteItem, this));
        },
        _unbind: function() {
            $(this.options.addBtnSelector).off('click');
            $(this.element).off('click');
        },
        _initValidators: function() {
            $(this.options.formSelector).on('beforeSubmit', $.proxy(this._validateAmountsReset, this));
            $.validator.addMethod(
                'aw-gc-amounts-duplicate',
                $.proxy(this._validateAmountsDuplicate, this),
                this.options.errorMessageDuplicateAmount
            );
        },
        _validateAmountsDuplicate: function(value, element, params) {
            var websiteId = $(this.options.rowsContainerSelector).find('select[data-type=website][data-index=' + $(element).data('index') + ']').val();
            var amount = $(element).val();
            var amountRow = [websiteId, amount].join('-');
            var result = $.inArray(amountRow, this.validatedAmounts) === -1;
            this.validatedAmounts.push(amountRow);
            return result;
        },
        _validateAmountsReset: function() {
            this.validatedAmounts = [];
        },
        addBtnClick: function() {
            this.addItem();
        },
        addItem : function () {
            var data = {
                website_id: this.options.defaultWebsiteId,
                price: '',
                readOnly: false,
                index: this.itemsCount++
            };
            if (arguments.length > 0) {
                data.website_id = arguments[0];
                data.price = arguments[1];
            }
            $(this.options.rowsContainerSelector).append(this.template({data: data}));
            $("#aw_gc_amount_row_" + data.index + "_website [value='" + data.website_id + "']").attr('selected', 'selected');
        },
        deleteItem: function(event) {
            var tr = $(event.target).closest('tr');
            if (tr) {
                tr.find('.delete').val(1);
                tr.find('input').removeAttr('class').hide();
                tr.find('select').removeAttr('class').hide();
                tr.attr('data-deleted', '1').hide();
            }
        }
    });

    return $.awgc.productAmountControl;
});
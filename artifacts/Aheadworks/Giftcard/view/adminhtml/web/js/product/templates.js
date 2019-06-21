define([
    'jquery',
    'mage/template',
    'aheadworksGCImageUploader',
    'jquery/validate',
    'jquery/ui'
], function($, mageTemplate, imageUploader){
    'use strict';

    $.widget("awgc.productTemplatesControl", {
        itemsCount: 0,
        options: {
        },
        _create: function() {
            this.fieldContainer = $(this.options.fieldContainerSelector);
            this.template = mageTemplate(this.options.templatesRowTemplate);
            var self = this;
            $.each(this.options.values, function() {
                self.addItem(this.template_id, this.image, this.image_url, this.store_id);
            });
            this._bind();
            this.typeChange();
        },
        destroy: function() {
            this._unbind();
        },
        _bind: function() {
            $(this.options.addBtnSelector).on('click', $.proxy(this.addBtnClick, this));
            $(this.options.typeSelect).on('change', $.proxy(this.typeChange, this));
            $(this.element).on('click', 'button.delete', $.proxy(this.deleteItem, this));
        },
        _unbind: function() {
            $(this.options.addBtnSelector).off('click');
            $(this.options.typeSelect).off('change');
            $(this.element).off('click');
        },
        typeChange: function() {
            if ($(this.options.typeSelect).val() == this.options.fieldHideValue) {
                this.fieldContainer.hide();
            } else {
                this.fieldContainer.show();
            }
        },
        addBtnClick: function() {
            this.addItem();
        },
        addItem : function () {
            var data = {
                index: this.itemsCount++
            };
            if (arguments.length > 0) {
                data.template_id = arguments[0];
                data.image = arguments[1];
                data.image_url = arguments[2];
                data.store_id = arguments[3];
            }
            $(this.options.rowsContainerSelector).append(this.template({data: data}));
            this._getElement(data.index, 'template')
                .find("[value='" + data.template_id + "']")
                .attr('selected', 'selected');
            this._getElement(data.index, 'store')
                .find("[value='" + data.store_id + "']")
                .attr('selected', 'selected');
            if (data.image) {
                this._getElement(data.index, 'image').val(data.image);
            }
            imageUploader(
                {
                    'imageTemplate': this.options.templatesImageTemplate,
                    'imageValue': this._getElement(data.index, 'image'),
                    'imageLoaded': data.image_url ? data.image_url : false
                },
                this._getElement(data.index, 'image_container')
            );

        },
        deleteItem: function(event) {
            var tr = $(event.target).closest('tr');
            if (tr) {
                tr.find('.delete').val(1);
                tr.find('select').removeAttr('class').hide();
                tr.attr('data-deleted', '1').hide();
            }
        },
        _getElement: function(index, type) {
            return $("#aw_gc_template_row_" + index + "_" + type);
        }
    });

    return $.awgc.productTemplatesControl;
});
/*jshint browser:true */
define([
    'jquery',
    'mage/template',
    'jquery/ui',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'mage/adminhtml/grid'
], function ($, mageTemplate) {
   'use strict';

    $.widget('mage.nameProduct', {
        /**
         * Create the widget
         * @private
         */
        _create: function() {
            this.$grid = this.element.find('[data-role=name-product-grid]');
            this.$grid.sortable({
                distance: 8,
                items: '[data-role=row]',
                tolerance: 'pointer',
                cancel: ':input',
                update: $.proxy(function() {
                    this.element.trigger('resort');
                }, this)
            });

            this.productTmpl = mageTemplate('#name-product-template');

            $.each(
                this.$grid.data('products'),
                $.proxy(function (index, product) {
                    this._add(null, product);
                }, this)
            );

            this._on({
                'add': '_add',
                'resort': '_resort',
                'click [data-column=actions] [data-role=delete]': '_remove'
            });

            this._bindDialog();
            this._updateGridVisibility();
        },

        /**
         * Add product to grouped grid
         * @param {EventObject} event
         * @param {Object} product
         * @private
         */
        _add: function (event, product) {
            console.log('add');

            var tmpl,
                productExists;

            productExists = this.$grid.find('[data-role=id]')
                .filter(function (index, element) {
                    return $(element).val() == product.id;
                }).length;

            if (!productExists) {
                tmpl = this.productTmpl({data: product});

                $(tmpl).appendTo(this.$grid.find('tbody'));
            }
        },

        _remove: function (event) {
            $(event.target).closest('[data-role=row]').remove();
            this.element.trigger('resort');
            this._updateGridVisibility();
        },

        _resort: function() {
            this.element.find('[data-role=position]').each($.proxy(function (index, element) {
                $(element).val(index + 1);
            }, this));
        },

        _bindDialog: function () {
            var widget = this,
                selectedProductList = {},
                popup = $('[data-role=add-product-dialog]');

            popup.modal({
                type: 'slide',
                innerScroll: true,
                title: $.mage.__('Add Products to Name Group'),
                modalClass: 'name',
                open: function () {
                    $(this).addClass('admin__scope-old');
                },
                buttons: [{
                    id: 'name-product-dialog-apply-button',
                    text: $.mage.__('Add Selected Products'),
                    'class': 'action-primary action-add',
                    click: function() {
                        $.each(selectedProductList, function (index, product) {
                            widget._add(null, product);
                        });
                        widget._resort();
                        widget._updateGridVisibility();
                        popup.modal('closeModal');
                    }
                }]
            });

            popup.on('click', '[data-role=row]', function(event) {
                var target = $(event.target);

                if (!target.is('input')) {
                    target.closest('[data-role=row]')
                        .find('[data-column=entity_ids] input')
                        .prop('checked', function (element, value) {
                            return !value;
                        })
                        .trigger('change');
                }
            });

            popup.on('change', '[data-role=row] [data-column=entity_ids] input', $.proxy(function (event) {
                var element = $(event.target),
                    product = {};

                if (element.is(':checked')) {
                    product.id = element.val();
                    element.closest('[data-role=row]')
                        .find('[data-column]')
                        .each(function (index, element) {
                            product[$(element).data('column')] = $.trim($(element).text());
                        });
                    selectedProductList[product.id] = product;
                } else {
                    delete selectedProductList[element.val()];
                }
            }, this));

            var gridPopup = $(this.options.gridPopup).data('gridObject');

            $('[data-role=add-product]').on('click', function (event) {
                event.preventDefault();
                popup.modal('openModal');
                gridPopup.reload();
                selectedProductList = {};
            });

            $('#' + gridPopup.containerId)
                .on('gridajaxsettings', function (event, ajaxSettings) {
                    var ids = widget.$grid.find('[data-role=id]').map(function (index, element) {
                        return $(element).val();
                    }).toArray();
                    ajaxSettings.data.filter = $.extent(ajaxSettings.data.filter || {}, {
                        'entity_ids': ids
                    });
                })
                .on('gridajax', function (event, ajaxRequest) {
                    ajaxRequest.done(function () {
                        popup.find('[data-role=row] [data-column=entity_ids] input')
                            .each(function (index, element) {
                                var $element = $(element);
                                $element.prop('checked', !!selectedProductList[$element.val()]);
                            });
                    });
                });
        },

        _updateGridVisibility: function() {
            var showGrid = this.element.find('[data-role=id]').length > 0;
            this.element.find('.grid-container').toggle(showGrid);
            this.element.find('.no-products-message').toggle(!showGrid);
        }
    });

    return $.mage.nameProduct;
});

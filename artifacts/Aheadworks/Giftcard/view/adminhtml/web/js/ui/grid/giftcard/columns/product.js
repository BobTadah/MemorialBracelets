define([
    'Magento_Ui/js/grid/columns/column'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Aheadworks_Giftcard/ui/grid/giftcard/cells/product'
        },
        hasProduct: function(row) {
            return row[this.index + '_name'];
        },
        getProductName: function(row) {
            return row[this.index + '_name'];
        },
        getProductUrl: function(row) {
            return row[this.index + '_url'];
        }
    });
});

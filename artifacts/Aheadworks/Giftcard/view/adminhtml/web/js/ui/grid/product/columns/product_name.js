define([
    'Magento_Ui/js/grid/columns/column'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Aheadworks_Giftcard/ui/grid/product/cells/product_name'
        },
        getName: function(row) {
            return row[this.index];
        },
        getProductUrl: function(row) {
            return row[this.index + '_url'];
        }
    });
});

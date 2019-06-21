define([
    'Magento_Ui/js/grid/columns/column'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Aheadworks_Giftcard/ui/grid/giftcard/cells/order'
        },
        hasOrder: function(row) {
            return row[this.index + '_name'];
        },
        getOrderName: function(row) {
            return row[this.index + '_name'];
        },
        getOrderUrl: function(row) {
            return row[this.index + '_url'];
        }
    });
});

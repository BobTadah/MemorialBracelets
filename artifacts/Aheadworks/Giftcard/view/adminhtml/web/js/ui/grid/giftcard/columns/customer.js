define([
    'Magento_Ui/js/grid/columns/column'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Aheadworks_Giftcard/ui/grid/giftcard/cells/customer'
        },
        hasCustomer: function(row) {
            return  row[this.index + '_url'];
        },
        getCustomerName: function(row) {
            return row[this.index];
        },
        getCustomerUrl: function(row) {
            return row[this.index + '_url'];
        }
    });
});

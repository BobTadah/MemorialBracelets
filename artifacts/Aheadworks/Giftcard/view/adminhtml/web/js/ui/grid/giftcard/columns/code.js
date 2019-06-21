define([
    'Magento_Ui/js/grid/columns/column'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Aheadworks_Giftcard/ui/grid/giftcard/cells/code'
        },
        getCode: function(row) {
            return row[this.index];
        },
        getCodeUrl: function(row) {
            return row[this.index + '_url'];
        }
    });
});

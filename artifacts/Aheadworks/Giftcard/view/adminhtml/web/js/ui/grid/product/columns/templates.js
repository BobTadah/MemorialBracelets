define([
    'Magento_Ui/js/grid/columns/column'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Aheadworks_Giftcard/ui/grid/product/cells/templates'
        },
        getTemplateNames: function(row) {
            return row[this.index + '_names'];
        }
    });
});

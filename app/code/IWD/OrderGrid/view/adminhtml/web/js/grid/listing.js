define([
    'Magento_Ui/js/grid/listing'
], function (Component) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'IWD_OrderGrid/grid/listing'
        },
        getColor: function (row) {
            if (row["color"] !== "" && row["color"] !== "undefined") {
                return "background-color: #" + row["color"] + ";";
            }
            return false;
        }
    });
});
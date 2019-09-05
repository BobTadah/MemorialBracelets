define([
    'Magento_Ui/js/grid/columns/column',
    'ko'
], function (Column, ko) {
    'use strict';
    return Column.extend({
        initialize: function () {
            this.itemVisible = ko.observableArray([]);
            this._super();
        },
        getItems: function (record) {
            var data = record[this.index];
            if (data instanceof Object) {
                return data;
            }
            return false;
        },
        isButton: function (record) {
            return (record[this.index] !== null && record[this.index].length > 4) ? true : false;
        },
        toggleVisible: function (rowIndex) {
            if (!this.getVisible(rowIndex)) {
                this.itemVisible.push(rowIndex);
            } else {
                this.itemVisible.remove(rowIndex);
            }
        },
        getVisible: function (rowIndex) {
            var index = this.itemVisible.indexOf(rowIndex);
            return (index == '-1') ? false : true;
        }
    });
});
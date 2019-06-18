define([], function () {
    'use strict';

    return function (targetModule) {
        return targetModule.extend({
            updateTemplateForType: function (type, template) {
                template = this._super();

                if (type == 'swatches') {
                    return 'ns=' + this.ns +
                        ', dataScope=' + this.parentScope +
                        ', index=' + type;
                }
                return template;
            }
        });
    }
});
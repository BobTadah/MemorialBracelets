define([
    'underscore',
    'uiRegistry'
], function (_, registry) {
    'use strict';

    return function (targetModule) {
        return targetModule.extend({
            updateComponents: function (currentValue, isInitialization) {
                var currentGroup = this.valuesMap[currentValue];

                if (currentGroup !== this.previousGroup) {
                    _.each(this.indexesMap, function (groups, index) {
                        var template = this.filterPlaceholder + ', index = ' + index,
                            visible = groups.indexOf(currentGroup) !== -1,
                            component;

                        // THE MODIFICATION - Extract this functionality to be extendible
                        template = this.updateTemplateForType(index, template);

                        /*eslint-disable max-depth */
                        if (isInitialization) {
                            registry.async(template)(
                                function (currentComponent) {
                                    currentComponent.visible(visible);
                                }
                            );
                        } else {
                            component = registry.get(template);

                            if (component) {
                                component.visible(visible);

                                /*eslint-disable max-depth */
                                if (_.isFunction(component.clear)) {
                                    component.clear();
                                }
                            }
                        }
                    }, this);

                    this.previousGroup = currentGroup;
                }

                return this;
            },

            updateTemplateForType: function (type, template) {
                switch (type) {
                    case 'container_type_static':
                    case 'values':
                        template = 'ns=' + this.ns +
                                ', dataScope=' + this.parentScope +
                                ', index=' + type;
                        break;
                }
                return template;
            }
        });
    }
});
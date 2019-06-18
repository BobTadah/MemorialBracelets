define(['jquery', 'uiClass'], function ($, Class) {
    var PreviewOptions = {
        PART_NONE: 'none',
        PART_MATERIAL_COLOR: 'material_color',
        PART_PARACORD: 'paracord_color',
        PART_TOP_ICON: 'icon_top',
        PART_LEFT_ICON: 'icon_left',
        PART_RIGHT_ICON: 'icon_right',
        PART_LEFT_CHARM: 'charm_left',
        PART_RIGHT_CHARM: 'charm_right',
        PART_ENGRAVING: 'engraving',
        PART_ENGRAVING_STYLE: 'engraving_style',
        PART_MATERIAL: 'material',
        PART_SIZE: 'size',
        PART_CHARM: 'charm',
        PART_THREAD_COLOR: 'text_color'
    };

    return Class.extend({
        defaults: {
            listeners: {},
            optionIdToPart: {},
            node: null,
            parts: {},
            partsDisplaying: {}
        },
        initialize: function (config, node) {
            if (!node) { return; }
            console.log('initialize', arguments);

            this._super();
            this.node = node;
            this.createReverseMap(config);
            this.createPartMap(node);
            this.attachProductChangeListener();
            this.attachResetListener();
            return this;
        },

        /**
         * Responsible for turning a config of [part => [id, id, id]] into [id => part, id => part, id => part]
         * @param config
         * @returns {exports}
         */
        createReverseMap: function (config) {
            for (var part in config) {
                for (var i in config[part]) {
                    this.optionIdToPart[config[part][i]] = part;
                }
            }
            return this;
        },

        /**
         * Loops through all the different available parts, looks for their match in the bracelet preview HTML
         * and then puts it in the parts object.
         * @param node
         * @returns {exports}
         */
        createPartMap: function (node) {
            var $node = $(node);
            for (var part in PreviewOptions) {
                var partIdent = PreviewOptions[part];
                this.parts[partIdent] = $node.find('[data-part=' + partIdent + ']').first();
            }
            return this;
        },

        /**
         * Checks if we're a name product or not, attaches listeners to switch what the preview is listening to if we
         * are, and simply runs the initial bind if we're not (or if one is selected)
         */
        attachProductChangeListener: function () {
            var productSelector = $('#product_name_selection_control');
            if (productSelector.length) {
                var selector = productSelector.data('selector');
                var input = $('[name="' + selector + '"]');

                input.on('change', (function () {
                    // On product selection, detach from previous and attach to current
                    var selected = input.filter(':checked');
                    var value = selected.val();

                    this.attachToId(value);
                }).bind(this));

                var selectedVal = input.filter(':checked').val();
                if (selectedVal) {
                    this.attachToId(selectedVal);
                }
            } else {
                this.attach($('#product-options-wrapper'));
            }
        },

        /**
         * Attaches the reset implementation to the reset button
         */
        attachResetListener: function () {
            var $node = $(this.node), self = this;
            $node.find('[data-role=bracelet-reset-button]').on('click', function() {
                self.reset();
            });
        },

        /**
         * Given a product ID, attach the preview listeners to that product's options
         * @param productId
         */
        attachToId: function (productId) {
            var node = $('.product_selection_fieldset[data-productid=' + productId + ']');
            this.attach(node);
        },

        /**
         * Given a node representing a product form, attach the preview listeners to that product's options
         *
         * This will also reset the previously attached options, detach the listeners from them, and clear out the
         * preview.
         *
         * Do not use lightly
         *
         * @param node
         */
        attach: function (node) {
            this.reset();
            this.detach();
            this.clearAll();

            var $node = $(node), self = this;
            $node.find('.product-custom-option').each(function () {
                var name = $(this).attr('name');
                var optionId = parseInt(name.split('[')[1].split(']')[0], 10);

                var part = self.getPart(optionId);
                switch (part) {
                    case 'charm_left':
                    case 'charm_right':
                        self.attachCharm(optionId, self.getPartNode(part), part);
                        break;

                    case 'icon_left':
                    case 'icon_right':
                    case 'icon_top':
                        self.attachIcon(optionId, self.getPartNode(part), part);
                        break;

                    case 'material_color':
                        self.attachBackground(optionId, self.getPartNode(part), part);
                        break;

                    case 'engraving':
                        self.attachEngraving(optionId, self.getPartNode(part), part);
                        break;
                }
            });
            this.decideToShowHide();
        },

        /**
         * Boilerplate for attachCharm, attachIcon, attachBackground, etc.
         *
         * Performs the tedious task of updating whether or not a part should be displaying, of triggering the test
         * to show/hide the bracelet preview, of setting up the listener in the array, etc.
         *
         * @param $node Node to listen for changes on
         * @param part Bracelet Piece
         * @param valueSet Callback for when a value is set
         * @param valueUnset Callback for when a value is unset
         */
        genericAttach: function ($node, part, valueSet, valueUnset) {
            var self = this;

            var updateFunction = function (value) {
                var ourUnset = function () {
                    delete self.partsDisplaying[part];
                    valueUnset();
                };

                if (value) {
                    self.partsDisplaying[part] = true;
                    valueSet(value, ourUnset); // allow errors to run the unset
                } else {
                    ourUnset();
                }
                self.decideToShowHide();
            };

            var listenFunc = function () {
                updateFunction($(this).val());
            };

            $node.on('change', listenFunc);
            this.listeners[part] = {node: $node, callback: listenFunc};
            updateFunction($node.val());
        },

        /**
         * Specific functionality for showing/hiding charm selections
         *
         * @see genericAttach
         * @param optionId
         * @param $node
         * @param part
         */
        attachCharm: function (optionId, $node, part) {
            var $select = $('.product-custom-option[name="options[' + optionId + ']"]');
            if (!$select.is('select') || !$node) {
                return; // Cannot attach
            }

            var getCharmForId = function(id) {
                return $select.closest('.control').find('.charm-option[data-charm-id=' + id + ']');
            };

            this.genericAttach(
                $select,
                part,
                function (valueId) {
                    var $charm = getCharmForId(valueId);

                    var newOptionImage = $charm.css('background-image')
                        .replace('swatch_image', 'swatch_thumb')
                        .replace('30x20', '110x90');
                    var newOptionColor = $charm.css('background-color');
                    $node.removeClass('no-display')
                        .css('background-image', newOptionImage)
                        .css('background-color', newOptionColor)
                        .css('background-size', 'contain');
                    $node.parent().find('.line').removeClass('no-display');
                },
                function (valueId) {
                    $node.addClass('no-display');
                    $node.parent().find('.line').addClass('no-display');
                }
            );
        },

        /**
         * Specific functionality for showing/hiding icon selections
         *
         * @see genericAttach
         * @param optionId
         * @param $node
         * @param part
         */
        attachIcon: function (optionId, $node, part) {
            var $select = $('.product-custom-option[name="options[' + optionId + ']"]');
            if (!$select.is('select') || !$node) {
                return; // Cannot attach
            }

            var getIconForId = function (id) {
                return $select.closest('.control').find('.opticn-option[data-icon-id=' + id + ']');
            };

            this.genericAttach(
                $select,
                part,
                function (valueId) {
                    var $icon = getIconForId(valueId);

                    var newOptionImage = $icon.css('background-image')
                        .replace('swatch_image', 'swatch_thumb')
                        .replace('30x20', '110x90');
                    var newOptionColor = $icon.css('background-color');
                    $node.removeClass('no-display')
                        .css('background-image', newOptionImage)
                        .css('background-color', newOptionColor)
                        .css('background-size', 'contain');
                },
                function (valueId) {
                    $node.addClass('no-display');
                }
            );
        },

        /**
         * Specific functionality for listening to a swatch custom option and setting the background based on it
         *
         * @see genericAttach
         * @param optionId
         * @param $node
         * @param part
         */
        attachBackground: function (optionId, $node, part) {
            var $select = $('.product-custom-option[name="options[' + optionId + ']"]');
            if (!$select.is('select') || !$node) {
                return; // Cannot attach
            }

            var getSwatchForId = function (id) {
                return $select.closest('.control').find('.swatch-option[data-swatch-id=' + id + ']');
            };

            this.genericAttach(
                $select,
                part,
                function (valueId) {
                    var $swatch = getSwatchForId(valueId);

                    var newOptionImage = $swatch.css('background-image')
                        .replace('swatch_image', 'swatch_thumb')
                        .replace('30x20', '110x90');
                    var newOptionColor = $swatch.css('background-color');
                    $node.css('background-image', newOptionImage)
                        .css('background-color', newOptionColor)
                        .css('background-repeat', 'repeat');
                },
                function (valueId) {
                    $node.css('background', '');
                }
            );
        },

        /**
         * Specific functionality for showing/hiding the engraving on the bracelet preview
         *
         * @see genericAttach
         * @param optionId
         * @param $node
         * @param part
         */
        attachEngraving: function (optionId, $node, part) {
            var $textArea = $('.product-custom-option[name="options[' + optionId + ']"]'),
                self = this;

            var unsetEngraving = function() {
                $node.html('').addClass('no-display');
            };

            this.genericAttach(
                $textArea,
                part,
                function (value, valueUnset) {
                    try {
                        var decodedVal = JSON.parse(value);
                        if (decodedVal && decodedVal.hasOwnProperty('text') && decodedVal.text) {
                            var lineDom = document.createDocumentFragment(),
                                lines = decodedVal.text.trim().split("\n");

                            for (var i in lines) {
                                var p = $('<p class="text"></p>');
                                p[0].innerText = lines[i];
                                $(lineDom).append(p);
                            }
                            $node.html('').append(lineDom).removeClass('no-display');
                        } else {
                            valueUnset();
                        }
                    } catch (e) {
                        valueUnset();
                    }
                },
                unsetEngraving
            );
        },

        /**
         * Clear all styling on the bracelet preview
         * @see clearPart
         */
        clearAll: function () {
            for(var part in this.parts) {
                var $node = this.parts[part];

                this.clearPart(part, $node);
            }
        },

        /**
         * Given a bracelet piece type and a node, clear the bracelet piece type's styling from that node
         *
         * @see clearAll
         * @param part
         * @param $node
         */
        clearPart: function (part, $node) {
            switch(part) {
                case 'charm_left':
                case 'charm_right':
                    $node.addClass('no-display')
                        .css('background', '')
                        .parent().find('.line')
                        .addClass('no-display');
                    break;

                case 'icon_left':
                case 'icon_right':
                case 'icon_top':
                    $node.addClass('no-display')
                        .css('background', '');
                    break;

                case 'material_color':
                    $node.css('background', '');
                    break;

                case 'engraving':
                    $node.html('').addClass('no-display');
                    break;
            }
        },

        /**
         * Helper function to show/hide the preview based on whether or not any parts have been styled.
         *
         * Utilizes the partsDisplaying object, then calls a function to show or hide
         *
         * @see showPreview
         * @see hidePreview
         */
        decideToShowHide: function () {
            if (Object.keys(this.partsDisplaying).length) {
                this.showPreview();
            } else {
                this.hidePreview();
            }
        },

        /**
         * Helper function to return all the nodes that we should toggle the show/hide classes on
         *
         * @see decideToShowHide
         * @returns {*|jQuery}
         */
        getPreviewVisibilityNodes: function () {
            return $(this.node)
                .find('[data-role=bracelet-preview-bracelet], [data-role=bracelet-preview-reset-button-holder]');
        },

        /**
         * Makes the Bracelet Preview visible
         * @see decideToShowHide
         */
        showPreview: function () {
            this.getPreviewVisibilityNodes().removeClass('no-display');
        },

        /**
         * Hides the Bracelet Preview
         * @see decideToShowHide
         */
        hidePreview: function () {
            this.getPreviewVisibilityNodes().addClass('no-display');
        },

        /**
         * Turns off all bracelet preview listeners (using the listeners object), and removes the parts detached from
         * the "partsDisplaying" object (for use with decideToShowHide)
         */
        detach: function () {
            for (var part in this.listeners) {
                var listener = this.listeners[part];
                $(listener.node).off('change', listener.callback);
                delete this.listeners[part];
                delete this.partsDisplaying[part];
            }
        },

        /**
         * Resets all options to their default state.  If the option is a select, we reset it to it's default value.
         * If the option is the engraving, we push the "Reset Text" button.
         */
        reset: function () {
            for (var part in this.listeners) {
                var listener = this.listeners[part];
                var $node = $(listener.node);

                // Reset select boxes to their default state (Swatch, Icon, Charm)
                if ($node.is('select')) {
                    var htmlSelectedVal = $node.find('[selected]').val();
                    $node.val(htmlSelectedVal).trigger('change');
                }
                // Reset Engraving textarea to default state
                if (part == PreviewOptions.PART_ENGRAVING) {
                    $node.closest('[id=engraving-wrapper]').find('[id=reset-message-btn]').trigger('click');
                }
            }
        },

        /**
         * Returns the jQuery(element) or NULL matching the requested bracelet part
         * @param part
         * @returns {null}
         */
        getPartNode: function (part) {
            return this.parts.hasOwnProperty(part) ? this.parts[part] : null;
        },

        /**
         * Returns the part identifier for a given optionId
         *
         * @param optionId
         * @returns {null}
         */
        getPart: function (optionId) {
            return this.optionIdToPart.hasOwnProperty(optionId) ? this.optionIdToPart[optionId] : null;
        }
    });
});

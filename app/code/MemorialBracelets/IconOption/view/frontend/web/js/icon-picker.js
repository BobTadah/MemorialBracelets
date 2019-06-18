define([
    "jquery"
], function($) {
    "use strict";

    $.widget('memb.IconOptionRendererTooltip', {
        options: {
            delay: 200,
            tooltipClass: 'swatch-option-tooltip'
        },

        _init: function() {
            var $widget = this,
                $this = this.element,
                $element = $('.' + $widget.options.tooltipClass),
                timer,
                type = parseInt($this.data('option-type'), 10),
                label = $this.data('option-label'),
                thumb = $this.data('option-tooltip-thumb'),
                value = $this.data('option-tooltip-value'),
                $image,
                $title,
                $corner;

            if (!$element.size()) {
                $element = $('<div class="swatch-option-tooltip"><div class="image"></div><div class="title"></div><div class="corner"></div></div>')
                $('body').append($element);
            }

            $image = $element.find('.image');
            $title = $element.find('.title');
            $corner = $element.find('.corner');

            //Flag for IOS devices to implement workaround for hover issue.
            var iOSDevice = !!navigator.platform.match(/iPhone|iPod|iPad/);

            $this.hover(function() {
                timer = setTimeout(function() {
                    var leftOpt = null,
                        leftCorner = 0,
                        left,
                        $window;

                    $image.css({
                        'background': 'url("' + thumb + '") no-repeat center',
                        'background-size': 'contain'
                    });
                    $image.show();

                    $title.text(label);

                    leftOpt = $this.offset().left;
                    left = leftOpt + $this.width() / 2 - $element.width() / 2;
                    $window = $(window);

                    if (left < 0) {
                        left = 5;
                    } else if (left + $element.width() > $window.width()) {
                        left = $window.width() - $element.width() - 5;
                    }

                    leftCorner = 0;
                    if ($element.width() < $this.width()) {
                        leftCorner = $element.width() / 2 - 3;
                    } else {
                        leftCorner = (leftOpt > left ? leftOpt - left : left - leftOpt) + $this.width() / 2 - 6;
                    }

                    $corner.css({
                        left: leftCorner
                    });
                    $element.css({
                        left: left,
                        top: $this.offset().top - $element.height() - $corner.height() - 18
                    }).show();

                    //Workaround for IOS.
                    if (iOSDevice) { $this.trigger('click'); }
                }, $widget.options.display);
            }, function() {
                $element.hide();
                clearTimeout(timer);
            });

            $(document).on('tap', function() {
                $element.hide();
                clearTimeout(timer);
            });

            $this.on('tap', function (event) {
                event.stopPropagation();
            });
        }
    });

    $.widget('memb.IconOptionRenderer', {
        options: {
            classes: {
                attributeClass: 'opticn-attribute',
                attributeLabelClass: 'opticn-attribute-label',
                attributeSelectedOptionLabelClass: 'opticn-selected-label',
                attributeOptionsWrapper: 'opticn-picker',
                optionClass: 'opticn-option',
                selectClass: 'product-custom-option',
                loader: 'swatch-option-loading'
            },

            enableControlLabel: true
        },

        _init: function() {
            var $this = this.element,
                $widget = this;

            $this.find('.' + this.options.classes.optionClass).IconOptionRendererTooltip();

            $this.on('click', '.' + this.options.classes.optionClass, function() {
                return $widget._onClick($(this), $widget);
            });

            var $parents = $this.parents('.' + $widget.options.classes.attributeClass);
            // Attach on change listener
            $parents.find('.' + $widget.options.classes.selectClass)
                .on('change', function () {
                    return $widget._onChange($(this), $widget);
                });
            // Update the selection for the default value if one is set
            $parents.find('.' + $widget.options.classes.selectClass).trigger('change');
        },

        _onClick: function($this, $widget) {
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                $input = $parent.find('.' + $widget.options.classes.selectClass);

            if ($this.hasClass('selected')) {
                $input.val('');
            } else {
                $input.val($this.data('icon-id'));
            }

            $input.trigger('change');
        },

        _onChange: function($this, $widget) {
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                $label = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                $selectionElements = $parent.find('.' + $widget.options.classes.optionClass),
                $val = $this.val(),
                $selectedElement = $val ? $selectionElements.filter('[data-icon-id=' + $this.val() + ']') : null,
                $notSelectedElements = $val ? $selectionElements.not('[data-icon-id=' + $this.val() + ']') : $selectionElements;

            if ($selectedElement) {
                $selectedElement.addClass('selected');
            }
            $notSelectedElements.removeClass('selected');
            $label.text($selectedElement && $selectedElement.length ? $selectedElement.data('option-label') : '');
        }
    });

    return $.memb.IconOptionRenderer;
});

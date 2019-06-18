require(['jquery', 'jquery/ui', 'priceBox', 'mage/validation', 'js/validation-custom'], function ($) {
    $(function () {

        /**
         * This will handle applying/removing the active class on the engraving buttons
         * on the pdp.
         */
        $('#engraving-wrapper .button-set a').click(function () {
            var wrapperParent = $(this).parent().parent().parent();
            if (!$(this).hasClass('active')) {
                $(this).addClass('active');
                wrapperParent.find('.button-set a').not(this).removeClass('active');
                if ($(this).is('#custom-text-btn')) {
                    if (!wrapperParent.find('#custom-text-control').hasClass('active')) {
                        wrapperParent.find('#custom-text-control').addClass('active');
                        wrapperParent.find('#select-message-control').removeClass('active');
                        wrapperParent.find('#select-message-control').removeClass('active');
                    }
                }
                if ($(this).is('#select-message-btn')) {
                    if (!wrapperParent.find('#select-message-control').hasClass('active')) {
                        wrapperParent.find('#select-message-control').addClass('active');
                        wrapperParent.find('#custom-text-control').removeClass('active');
                    }
                }
            }
        });

        $(document).ready(function () {
            /**
             * this will handle populating the hidden text area with
             * the typed in text.
             *
             * todo: deal with auto fill.
             */

            $('#engraving-wrapper #custom-text-control').find('input:text').bind("blur change", function () {
                setFont($(this).closest('#engraving-wrapper'));
                var comboContainer = $(this).parent();
                //When text entered, set select to ''
                comboContainer.find('select').val('');
                var lineContainer = comboContainer.parent();
                var parent = lineContainer.parent();
                updateMessageAndPrice(parent);
                return;
            });

            $('#engraving-wrapper #custom-text-control').find('input:text').bind("keyup", function () {
                setFont($(this).closest('#engraving-wrapper'));
                var comboContainer = $(this).parent();
                //When text entered, set select to ''
                comboContainer.find('select').val('');
                var lineContainer = comboContainer.parent();
                var parent = lineContainer.parent();
                updateMessageAndPrice(parent);
                var lines = parent.find('input:text');
                _.each(lines, function (item) {
                    if (!$.validator.validateSingleElement($(item))) {
                        result = false;
                    }
                });
                return;
            });

            $('#engraving-wrapper #custom-text-control').find('input:text').bind("keypress paste", function (e) {
                setFont($(this).closest('#engraving-wrapper'));
                // This Function applies to direct user input, enforce the custom max length on
                // typing and pasting, but allow longer values from Name text and supportive messages

                e = e || window.event;
                var charCode = e.keyCode || e.which;
                var charStr = String.fromCharCode(charCode);
                var currentInput = charStr;
                if (typeof(e.originalEvent.clipboardData) != "undefined") {
                    currentInput = e.originalEvent.clipboardData.getData('text');
                }

                var maxlength = $(this).attr('custom-maxlength');
                if ((currentInput.length + $(this).val().length) > maxlength) {
                    var comboContainer = $(this).parent();
                    //When text entered, set select to ''
                    comboContainer.find('select').val('');
                    var lineContainer = comboContainer.parent();
                    var parent = lineContainer.parent();
                    var wrapperParent = parent.parent();
                    wrapperParent.find('.note').clearQueue();
                    wrapperParent.find('.note').css('background-color', 'white');
                    wrapperParent.find('.note').effect("highlight", {color: "#FFFF99"}, 500);
                    return false;
                }
            });

            $('#engraving-wrapper select').change(function () {
                setFont($(this).closest('#engraving-wrapper'));
                var comboContainer = $(this).parent();
                // When a Select changes, add its text to the input.
                comboContainer.find('input').val($(this).val());
                var lineContainer = comboContainer.parent();
                var parent = lineContainer.parent();
                updateMessageAndPrice(parent);
                var lines = parent.find('input:text');
                _.each(lines, function (item) {
                    if (!$.validator.validateSingleElement(item)) {
                        result = false;
                    }
                });
                return;
            });

            $('#engraving-wrapper .button-set a').click(function () {
                if ($(this).is('#custom-text-btn')) {
                    var wrapperParent = $(this).parent().parent().parent();
                    var parent = wrapperParent.find('#custom-text-control');
                    updateMessageAndPrice(parent);
                    return;
                }
                if ($(this).is('#select-message-btn')) {
                    var wrapperParent = $(this).parent().parent().parent();
                    var parent = wrapperParent.find('#select-message-control');
                    updateMessageAndPrice(parent);
                    return;
                }
            });
            $('#engraving-wrapper #reset-message-btn').click(function () {
                var wrapperParent = $(this).closest('.field.engraving');
                var parent = wrapperParent.find('#custom-text-control');
                if ($('.name-information').find('textarea').length > 0) {
                    var info = $('.name-information').find('textarea').val();
                    var nameLines = info.split("\n");
                    var eng_wrapper = wrapperParent;
                    var lines = parent.find('input:text');
                    for (var i = 0; i < lines.length; i++) {
                        var line_number = i + 1;
                        var stringValue = (nameLines[i] ? nameLines[i] : '');
                        nameLines[i]
                        if (eng_wrapper.find('#custom-text-control').find('select.line-' + line_number).length > 0) {
                            eng_wrapper.find('#custom-text-control').find('input:text.line-' + line_number).val(stringValue).trigger('change');
                        }
                    }

                    updateLabel(parent.closest('.field'), parent.closest('.field').find('.engraving-type-title'), 'name');

                    jsonObj = JSON.stringify({type: 'name', text: info});
                    eng_wrapper.find('#custom-text-control').find('textarea').val(jsonObj).trigger('change');
                    eng_wrapper.find('#custom-text-control').find('input:text').keyup();
                    eng_wrapper.find('#engraving-type-control').find('input:text').val('name').trigger('change');
                    setToCustom(eng_wrapper);
                    return;
                }
                else { //If no Name found, Clear the text
                    var lines = parent.find('input:text');
                    var eng_wrapper = wrapperParent;
                    for (var i = 0; i < lines.length; i++) {
                        var line_number = i + 1;
                        if (eng_wrapper.find('#custom-text-control').find('select.line-' + line_number).length > 0) {
                            eng_wrapper.find('#custom-text-control').find('select.line-' + line_number).val('').trigger('change');
                            eng_wrapper.find('#custom-text-control').find('input:text.line-' + line_number).val('').trigger('change');
                        }
                    }
                    eng_wrapper.find('#custom-text-control').find('textarea').val('').trigger('change');
                    eng_wrapper.find('#custom-text-control').find('input:text').keyup();
                    eng_wrapper.find('input[type="text"]').val('').trigger('change');
                    eng_wrapper.find('.product-custom-option').val('').trigger('change');
                    eng_wrapper.find('#custom-text-control').find('select').val('').trigger('change');
                    parent.closest('.field').find('.engraving-type-title').text('');
                    setToCustom(eng_wrapper);
                    return;
                }
            });
            // on init run checks and show preview by triggering keyUp
            if (($('#engraving-wrapper .product-custom-option').val() != '') && ( $('#engraving-wrapper .product-custom-option').val() != undefined)) {
                window.setTimeout(onInit, 1500);
            }
        });
    });

    /**
     * This function will display the basics for the preview overlay.
     */
    function onInit() {
        $('#engraving-wrapper').find('#custom-text-control').find('input:text').keyup();
    }

    /**
     * This function will display the basics for the preview overlay.
     */
    function updateMessageAndPrice(parent) {
        var lines = parent.find('input:text');
        var selectLines = parent.find('select');
        var checkForBlank = '';
        var fullText = '';
        var fullTextArray = [];
        var engravingType = 'supportive';
        var selectArray = [];
        setFont($(parent).closest('#engraving-wrapper'));
        toggleInputLines('pre-check');

        $(selectLines[0]).find('option').each(function (index, element) {
            selectArray.push(element.value)
        });
        if (lines.length == 0) {
            $.each(selectLines, function (k, v) {
                var theValue = v.value;
                fullTextArray.push(theValue);
                if (theValue) {
                    fullText += theValue + '\r\n';
                }
                checkForBlank += theValue;
            });
        }
        else {
            $.each(lines, function (k, v) {
                var theValue = v.value;
                if (!selectArray.includes(theValue)) {
                    engravingType = 'custom';
                }
                fullTextArray.push(theValue);
                if (theValue) {
                    fullText += theValue + '\r\n';
                }
                checkForBlank += theValue;
            });
        }

        //Filter possible blanks.
        fullText = fullText.trim();

        if (checkForBlank == '') {
            fullText = '';
            parent.parent().find('.product-custom-option').val(fullText).trigger('change');
            parent.closest('.field').find('.engraving-type-title').text('');
            toggleInputLines('post-check');
            return '';
        }
        else {
            //Check for name
            if ($('.name-information').find('textarea').length > 0) {
                var info = $('.name-information').find('textarea').val();
                var nameLines = info.split("\n");
                var lines = fullTextArray;

                //For doing third line validation, first we make sure both strings (engraving and original engraving) have the same spacing.
                var defaultSpacing = '  ';
                var extendedSpacing = '          ';
                var thirdLineMatches = false;
                if (lines[2] && nameLines[2]) {
                    //Use same spacing for both strings for comparison.
                    var thirdLine           = lines[2].replace(extendedSpacing, defaultSpacing);
                    var thirdOriginalLine   = nameLines[2].replace(extendedSpacing, defaultSpacing);
                    if (thirdLine === thirdOriginalLine) {
                        thirdLineMatches = true;
                    }
                }

                var match = true;
                var readArray = [];
                for (var i = 0; i < lines.length; i++) {
                    var line_number = i + 1;
                    if ((lines[i] != nameLines[i])) {
                        if ((lines[i] != '') || (JSON.stringify(readArray) != JSON.stringify(nameLines))) {
                            //If it's the third line, then we use validation we calculated before.
                            if (i == 2) {
                                match = thirdLineMatches;
                            } else {
                                match = false;
                            }
                        }
                    }
                    else {
                        readArray.push(lines[i]);
                    }
                }
                if (match == true) {
                    jsonObj = JSON.stringify({type: 'name', text: fullText})
                    parent.parent().find('.product-custom-option').val(jsonObj).trigger('change');
                    parent.parent().find('#engraving-type-control').find('input:text').val('name');
                    var price = parent.parent().find('.name-engraving-price .price-wrapper').attr('data-price-amount');

                    var $field = parent.closest('.field');
                    updateLabel($field, $field.find('.engraving-type-title'), 'name');

                    var optionName = parent.parent().find(' .product-custom-option').attr('name');
                    var changes = {};
                    changes[optionName] = {
                        finalPrice: {amount: price},
                        basePrice: {amount: price},
                        oldPrice: {amount: price}
                    };
                    $('.price-box').trigger('updatePrice', changes);

                    //remove the normal maxlength validator
                    var lines = parent.find('input:text');
                    lines.removeAttr('maxLength');
                    var maxlength = $(lines[0]).attr('custom-maxlength');
                    lines.removeClass('validate-length maximum-length-' + maxlength);
                    lines.removeClass('mage-error');

                    toggleInputLines('post-check');
                    return;
                }
                toggleInputLines('post-check');
            }
            if (engravingType == 'supportive') {
                jsonObj = JSON.stringify({type: 'supportive', text: fullText})
                parent.parent().find('.product-custom-option').val(jsonObj).trigger('change');
                var price = parent.parent().find('.supportive-message-price .price-wrapper').attr('data-price-amount')

                var $field = parent.closest('.field');
                updateLabel($field, $field.find('.engraving-type-title'), 'supportive');

                var optionName = parent.parent().find('.product-custom-option').attr('name')
                var changes = {};
                changes[optionName] = {
                    finalPrice: {amount: price},
                    basePrice: {amount: price},
                    oldPrice: {amount: price}
                };
                $('.price-box').trigger('updatePrice', changes);
                parent.parent().find('#engraving-type-control').find('input:text').val('supportive');

                //remove the normal maxlength validator
                var lines = parent.find('input:text');
                lines.removeAttr('maxLength');
                var maxlength = $(lines[0]).attr('custom-maxlength')
                lines.removeClass('validate-length maximum-length-' + maxlength);
                lines.removeClass('mage-error');
            }
            else {
                // Not a name or supportive message, so it is a custom text.
                jsonObj = JSON.stringify({type: engravingType, text: fullText});
                parent.parent().find('.product-custom-option').val(jsonObj).trigger('change');
                parent.parent().find('#engraving-type-control').find('input:text').val('custom');

                var $field = parent.closest('.field');
                updateLabel($field, $field.find('.engraving-type-title'), 'custom');

                var lines = parent.find('input:text');
                var maxlength = $(lines[0]).attr('custom-maxlength');

                //Adds validation to specific modified lines that are different from the original wordings or the pre-built lines.
                for (var j = 0; j < lines.length; j++) {
                    var validate = false;

                    if (nameLines) {
                        if (!selectArray.includes(fullTextArray[j]) && !nameLines.includes(fullTextArray[j])) {
                            validate = true;
                        }
                    } else {
                        if (!selectArray.includes(fullTextArray[j])) {
                            validate = true;
                        }
                    }

                    if (validate) {
                        //Means that it's NOT a pre-built or original wording, so need to validate.
                        $(lines[j]).addClass('validate-length maximum-length-' + maxlength);
                        $(lines[j]).attr('maxlength', maxlength);
                    } else {
                        //Means that it's a pre-built or original wording, so NO need to validate.
                        $(lines[j]).removeAttr('maxLength');
                        $(lines[j]).removeClass('validate-length maximum-length-' + maxlength);
                        $(lines[j]).removeClass('mage-error');
                    }
                }
            }
            toggleInputLines('post-check');
        }
        return;
    }

    function toggleInputLines(action) {
        var previewLines = $('#overlay-preview-container').find('.text-lines > .text');

        if (previewLines.length) {
            if (action === 'pre-check') {
                $(previewLines).removeClass('no-text');
            } else { // post-check
                $.each(previewLines, function (index, value) {
                    var tt1 = $(value).text();
                    if (!$(value).text().trim().length) {
                        $(value).addClass("no-text");
                    }
                });
            }
        }
    }

    function updateLabel($field, $label, type) {
        var $control = $field.find('[id=engraving-type-control]');
        var price = parseInt($control.data(type + '-cost'), 10);
        var label = $control.data(type + '-label');
        if (price > 0) {
            label = label + ' +' + $control.data(type + '-cost-format');
        }

        $label.text(label);
    }

    /**
     * This function will display the basics for the preview overlay.
     */
    function setToCustom(eng_wrapper) {
        // Set to Custom Text Entry
        var customButton = eng_wrapper.find('.button-set #custom-text-btn');
        if (!customButton.hasClass('active')) {
            customButton.addClass('active');
            eng_wrapper.find('.button-set a').not(customButton).removeClass('active');
            if (!eng_wrapper.find('#custom-text-control').hasClass('active')) {
                eng_wrapper.find('#custom-text-control').addClass('active');
                eng_wrapper.find('#select-message-control').removeClass('active');
                eng_wrapper.find('#select-message-control').removeClass('active');
            }
        }
    }

    /**
     * this function will handle the font changes
     * @param engravingElement
     */
    function setFont(engravingElement) {
        // remove any custom font classes
        var previewLines = $('#overlay-preview-container')
            .find('.text-lines')
            .removeClass(); // erase all prior classes

        // add new class
        var fontClass = $(engravingElement).find('.line-container').data('fontclass');
        $(previewLines).addClass('text-lines').addClass(fontClass);
    }
});

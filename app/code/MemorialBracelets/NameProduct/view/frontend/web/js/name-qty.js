require([
    'jquery',
    'mage/mage',
    "matchMedia",
    'Magento_Catalog/js/price-box'
], function ($, priceBox) {
    /* When the user clicks one of the products attached to a name product as the product selection, then we need to do
     * a couple things.
     *
     * 1. Display the custom options for that selection and disable the options for everything else (to prevent it from
     *    appearing in post), update the prices
     *
     * 2. Update the name attribute on the quantity field so that the quantity is attached to the simple product.
     *
     * 3. Switch out the images in the gallery
     */
    $('.name_product_selection').on('change', function () {
        // Step 1
        var control = $('#product_name_selection_control');
        var childSelector = control.data('selector');


        var productId = $('[name="' + childSelector + '"]:checked').val();
        var priceBoxSelector = '[data-role=priceBox]';

        // a .change() is triggered on our selector by Magento during PDP load.  If none of the options are selected,
        // however, then we probably shouldn't go through the motions of switching to this option.
        if (typeof productId == 'undefined') return;

        var wrapper = $('#product-options-wrapper');

        var oldFieldsets = wrapper.find('.product_selection_fieldset.lastActive');

        oldFieldsets.hide()
            .removeClass('lastActive')
            .find('input, select, textarea')
            .attr('disabled', true)
            .each(function() {
                var $this = $(this);
                var selector = $this.data('selector');
                var updateArray = {};
                updateArray[selector] = {"basePrice": {"amount":0, adjustments:[]}, "finalPrice": {"amount":0, adjustments:[]}, "oldPrice": {"amount":0, adjustments:[]}};
                $(priceBoxSelector).trigger('updatePrice', updateArray);
            });

        //Before clearing out, save the old text
        var oldInfo = ''
        if (oldFieldsets.length > 0) {
            if ($(oldFieldsets[0]).find('textarea').val()) {
                if ($(oldFieldsets[0]).find('textarea').val().length > 0) {
                    var oldInfo = JSON.parse($(oldFieldsets[0]).find('textarea').val()).text;
                    localStorage.setItem("currentInfo", oldInfo);
                }
            }
        } else { //No previous selections, so currentInfo is blank.
            localStorage.setItem("currentInfo", '');
        }


        // We clear out these fields and trigger a 'change' so that the pricing resets - if we didn't do this,
        // the price would stack when you change the product you're configuring!  Oh no!
        oldFieldsets.find('input[type!=radio][type!=checkbox], select, textarea').val('').trigger('change');
        oldFieldsets.find('input[type=radio], input[type=checkbox]').removeAttr('checked').removeAttr('selected').trigger('change');

        // TODO It's a possibility to remove the code unselecting options and do a price reset so that each selection
        // keeps it's stored values, preventing a user from having to re-select them.

        wrapper.find('.product_selection_fieldset[data-productid =' + productId + ']')
            .addClass('lastActive')
            .show()
            .find('input, select, textarea')
            .attr('disabled', false)
            .filter('select')
            .each(function() {
                // Reset select boxes to their default values
                var $this = $(this);
                var $selected = $this.find('[selected]');
                if ($selected.length) {
                    $this.val(typeof $selected.attr('value') === 'undefined' ? $selected.text() : $selected.attr('value'))
                        .trigger('change');
                }
            });

        if (wrapper.find('.product_selection_fieldset[data-productid =' + productId + ']').find('#engraving-wrapper').find('textarea').length > 0){
            // At this point we fill in the engraving.  Initially fill in the name.  If the last selection
            // has an engraving, keep it if possible.
            var currentInfo = localStorage.getItem("currentInfo");
            if (currentInfo.length > 0) {
                var info = currentInfo;
            } else {
                var info = $('.name-information').find('textarea').val();
            }
            var lines = info.split('\n');
            var eng_wrapper = wrapper.find('.product_selection_fieldset[data-productid =' + productId + ']').find('#engraving-wrapper')

            //FieldSet wrapper selector.
            var fieldSetWrapper = wrapper.find('.product_selection_fieldset[data-productid =' + productId + ']');

            //Get the parent event.
            var parentEvent = fieldSetWrapper.data('parentProductEvent');

            //Determine if event belongs to vietnams events.
            var parentEventIsVietnam = parentEvent.toLowerCase().indexOf('vietnam') !== -1;

            //Get the child productttype.
            var childType = fieldSetWrapper.data('childProductType');

            for (var i = 0; i < lines.length; i++) {
                var line_number = i+1;
                if (eng_wrapper.find('#custom-text-control').find('select.line-'+line_number).length > 0) {
                //Type specific engraving customization.
                    var lineValue = null;
                    if (i === 2) {
                        //Original line.
                        var thirdLineText = lines[i];

                        //Position of last word.
                        var lastWordPosition = thirdLineText.lastIndexOf(' ') + 1;

                        //Strip out last word of line.
                        var lastWordText = thirdLineText.substr(lastWordPosition);

                        //Prepare line to be updated.
                        var thirdLineTextWithoutLastWord = thirdLineText.substr(0, lastWordPosition).trim();

                        //Check lastWordPosition greater than zero. This means we have at least 2 words in the line.
                        if (lastWordPosition > 0) {
                            if (parentEventIsVietnam && (childType === 'Dog Tag' || childType === 'Paracord' || childType === 'Black Leather Bracelet')) {
                                //Modify the last piece of line to have extra (10) spaces.
                                lastWordText = '          ' + lastWordText;
                            } else if (parentEventIsVietnam) {
                                //Strip out spaces. In Vietnams we use 2 spaces as default word separator.
                                lastWordText = '  ' + lastWordText.trim();
                            } else {
                                //Strip out spaces. For all other products we use 1 space as word separator.
                                lastWordText = ' ' + lastWordText.trim();
                            }
                        }

                        //Update line value.
                        lineValue = thirdLineTextWithoutLastWord + lastWordText;
                    } else {
                        //Update line value.
                        lineValue = lines[i];
                    }

                    //Set line value.
                    eng_wrapper.find('#custom-text-control').find('input:text.line-' + line_number).val(lineValue);
                }
            }
            jsonObj = JSON.stringify({type:'name', text:info});
            wrapper.find('.product_selection_fieldset[data-productid =' + productId + ']').find('#engraving-wrapper').find('#custom-text-control').find('textarea').val(jsonObj);
            eng_wrapper.find('#custom-text-control').find('input:text').keyup();
            eng_wrapper.find('#engraving-type-control').find('input:text').val('name');
            eng_wrapper.find('.active-price').removeClass('active-price');
            eng_wrapper.find('.name-engraving-price').addClass('active-price');
        }

        // Step 2
        $('#qty').attr('name', $(this).data('postname')).trigger('change');

        // Step 3: Switch out the images in the gallery
        var images = $(this).data('images');
        $('[data-gallery-role=gallery-placeholder]').data('gallery').updateData(images);

        // Need to recalculate the sticky Product media so it shows correctly.
        // Check the media so it does not use stick on mobile view.
        mediaCheck({
            media: '(max-width: 768px)',
            entry: $.proxy(function () {
                // Work around to disable sticky behavior.
                // Set the container element to a height of 0, and it will not scroll.
                if (!$( ".sticky-helper" ).length) {
                    $('.product.media').parent().append("<div class='sticky-helper' style='height:0px'></div>")
                }
                $('.media-floater').mage('sticky',{
                    container: '.sticky-helper'
                });
            }, this),
            exit: $.proxy(function () {
                $('.media-floater').mage('sticky',{
                    container:'.product.media'
                });
            }, this)
        });

        /** Show price, add to cart, and social links. */
        $('.product-info-price').show();
        $('.product-options-bottom').show();
        $('.product-social-links').show();
    });

    /**
     * This will handle the name sub product selection highlighting.
     */
    $('.grouped.product-selection .admin__field-option').click(function () {
        $(this).addClass('selected');
        $('.grouped.product-selection .admin__field-option').not(this).removeClass('selected');
    });
    $('.grouped.product-selection label.admin__field-label').click(function () {
        $(this).addClass('selected');
        $('.grouped.product-selection label.admin__field-label').not(this).removeClass('selected');
    });

});

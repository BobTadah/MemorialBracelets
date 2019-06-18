require([
    'jquery',
    'jquery/ui',
    "matchMedia",
    'mage/mage'
], function ($) {
    $(document).ready(function () {
        /** hide the review from on page load. */
        $('#review-form').hide();

        $('.product-options-wrapper').on('click', '#size-toggle', function() {
            if (!$(this).next().hasClass('active')) {
                $(this).next().addClass('active');
            } else {
                $(this).next().removeClass('active');
            }
        });

        $('body').on('click', function (event) {
            var sizeBlock = $('.size-wrapper');
            if (!$(event.target).closest('.size-wrapper').length &&
                event.target.id != 'size-toggle' &&
                $(sizeBlock).hasClass('active')) {
                $(sizeBlock).removeClass('active');
            }
        });

        // Initialize sticky Product media so it shows correctly.
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
                    container: '.product.media'
                });
            }, this),
            exit: $.proxy(function () {
                $('.media-floater').mage('sticky',{
                    container:'.product.media'
                });
            }, this)
        });

        /**
         * once clicked, the review form will display and the user will be taken
         * to that section of the page in a smooth motion.
         */
        $('.product-reviews-summary a.action.add').click(function (event) {
            event.preventDefault();
            // display review form.
            $('#review-form').show();

            // get the target and fire the scroll-to function.
            var target = $(this).attr('href');
            target = target.substr(target.indexOf('#'));
            if (target.length) {
                reviewScrollTo(target);
            }
        });
    });



    /**
     * this function takes a element id parameter and will scroll the page to the id element.
     *
     * @param id
     */
    function reviewScrollTo(id) {
        $('html,body').animate({scrollTop: $(id).offset().top}, 'slow');
    }

});
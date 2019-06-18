require([
    'jquery'
], function ($) {
    $(document).ready(function () {
        $('body').on('click', function (event) {
            var quantityBlock = $('.quantity-discount-wrapper .discount-content');
            if ($(quantityBlock.length)) {
                if (event.target.id === 'discount-anchor' && !quantityBlock.hasClass('active')) {
                    $(quantityBlock).addClass('active');
                } else {
                    $(quantityBlock).removeClass('active');
                }
            }
        });
    });
});

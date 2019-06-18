require([
    'jquery',
    'OwlCarousel'
], function ($) {
    $(document).ready(function () {
        $("#home-hero-slider").owlCarousel({
            "singleItem":true,
            "pagination": true,
            "paginationNumbers": false,
            "autoPlay": 5000,
            "stopOnHover": true
        });
    });
});
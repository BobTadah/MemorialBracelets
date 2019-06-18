define([
    'jquery'
], function($, fullScreenLoader) {
    'use strict';

    function main() {
        var self = this;
        var form = $("#product_addtocart_form");

        $(document).on('click', "#product-addtowishlist-action", function() {
            $.ajax({
                url: 'wishlist/index/add',
                data: form.serialize(),
                type: 'post',
                dataType: 'json',
                showLoader: true,
            }).always(
                function(response) {
                    window.location.href = "wishlist"
                }
            );
        });
    }
    return main;
});
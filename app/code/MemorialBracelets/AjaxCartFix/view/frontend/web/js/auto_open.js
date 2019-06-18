define([
    "jquery/ui",
    "jquery"
], function(Component, $){
    return function(config, element){
        var minicart = $(element);
        minicart.on('contentLoading', function () {
            minicart.on('contentUpdated', function () {
                if (window.addToCartActionStatus === 'success') {
                    // extend normal functionality to pop open the mini-cart on successful update.
                    minicart.find('[data-role="dropdownDialog"]').dropdownDialog("open");
                }
            });
        });
    }
});
define([
    "jquery/ui",
    "jquery"
], function(Component, $) {
    return function(config, element) {
        $('body').trigger('processStart');
        var productSelectionList = $(element);

        //In order to fix some ux issues with the timing of the js, we display the product option once gallery is fully loaded.
        $('[data-gallery-role=gallery-placeholder]').on('gallery:loaded', function () {
            productSelectionList.show();
            $('body').trigger('processStop');
        });
    }
});
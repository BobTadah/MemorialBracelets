define([
    "jquery/ui",
    "jquery",
    "Magento_Customer/js/customer-data",
    "domReady!"
], function (Component, $, customerData) {
    return function (config, element) {
        //Force refresh of customer data. These is necessary for example after customer logs in to sync it's cart.
        customerData.reload(['customer'], false);
    }
});
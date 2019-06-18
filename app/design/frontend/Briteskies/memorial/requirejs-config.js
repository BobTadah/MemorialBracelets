var config = {
    map: {
        '*': {
            'Magento_Checkout/js/proceed-to-checkout':'js/proceed-to-checkout',
            'validation':'js/validation-custom'
        }
    },
    config: {
        mixins: {
            'Magento_Ui/js/view/messages': {
                'js/messages': true
            }
        }
    }
};

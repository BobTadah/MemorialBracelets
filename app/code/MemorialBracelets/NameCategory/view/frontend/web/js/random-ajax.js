define([
    'jquery',
    'underscore',
    'mage/template'
], function (
    $,
    _,
    template
) {
    function main(config, element) {
        var $element = $(element);
        var YOUR_URL_HERE = config.AjaxUrl;
        var categoryId = config.CategoryId;
        $(document).on('click','#random_name_btn',function() {
            var param = 'ajax=1';
            if (categoryId) {
               param += "&cat=" + categoryId;
            }
            $("#loading-random").height($("#random_name_btn").height());
            $('#loading-random').show();
            $('#random_name_btn').prop('disabled', true);
            $("#random_name_btn").prop('value', 'Searching');
            $.ajax({
                url: YOUR_URL_HERE,
                data: param,
                type: "POST",
                dataType: 'json'
            }).done(function (data) {
                $('#loading-random').hide();
                $('#random_name_btn').prop('disabled', false);
                $("#random_name_btn").prop('value', 'Get New Name');
                $('.random-name-product-row').html(data);
            });
        });
    };
    return main;
});
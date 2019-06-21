define([
    'jquery',
    'jquery/ui',
    'mage/validation/validation'
], function ($) {
    'use strict';

    $.widget("mage.validation", $.mage.validation, {
        options: {
            errorElement: 'span',
            ignore: ':hidden[name!=aw_gc_template]',
            errorPlacement: function (error, element) {
                element.after(error);
            },
            highlight: function (element, errorClass) {
                if ($(element).is('input[type=hidden]')) {
                    $("[data-highlight=" + $(element).attr('id') + "]").addClass(errorClass);
                } else {
                    $(element).addClass(errorClass);
                }
            },
            unhighlight: function (element, errorClass) {
                if ($(element).is('input[type=hidden]')) {
                    $("[data-highlight=" + $(element).attr('id') + "]").removeClass(errorClass);
                } else {
                    $(element).removeClass(errorClass);
                }
            }
        }
    });
    return $.mage.validation;
});
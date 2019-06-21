define(
    [
        'jquery',
        'ko'
    ],
    function ($, ko) {
        'use strict';

        var giftCardModel =  {
            amounts: ko.observableArray([]),
            maxCustomAmount: ko.observable(0),
            minCustomAmount: ko.observable(0),
            templates: ko.observableArray([]),
            isLoggedIn: ko.observable(false),

            dataLoaded: ko.observable(false),

            init: function() {
                var self = this;
                $.ajax({
                    url: this.optionsLoadUrl,
                    method: 'get'
                }).success(function(response){
                    for (var key in response) {
                        if (typeof self[key] !== "undefined") {
                            self[key](response[key]);
                        }
                    }
                    self.dataLoaded(true);
                });
            },
            "Aheadworks_Giftcard/js/model/product/giftcard": function(options) {
                giftCardModel.optionsLoadUrl = options.optionsLoadUrl;
            }
        };
        return giftCardModel;
    }
);

define([
    'jquery',
    'ko',
    'uiComponent',
    'Aheadworks_Giftcard/js/model/product/giftcard',
    'Aheadworks_Giftcard/js/model/product/data',
    'mage/translate',
    'jquery/ui'
], function ($, ko, Component, giftCardModel, productData, $t) {
    'use strict';

    return Component.extend({

        /* Amounts data */
        amounts: ko.observableArray([]),
        allowCustomAmount: ko.observable(false),
        maxCustomAmountValue: ko.observable(0),
        minCustomAmountValue: ko.observable(0),
        hasAmounts: ko.observable(false),
        amount: ko.observable(0),
        amountCustom: ko.observable(''),
        isCustomAmount: ko.observable(false),
        customAmountHint: ko.observable(''),
        customAmountOption: {
            value: 'custom',
            label: $t('Other Amount...')
        },

        /* Templates data */
        templates: ko.observableArray([]),
        templateValue: ko.observable(''),

        /* Email data */
        isLoggedIn: ko.observable(false),
        recipientName: ko.observable(''),
        recipientEmail: ko.observable(''),
        senderName: ko.observable(''),
        senderEmail: ko.observable(''),
        headline: ko.observable(''),
        message: ko.observable(''),

        sectionsShow: ko.observable(false),
        isLoading: ko.observable(true),

        /* UI */
        popup: false,
        popupPosition: {
            my: "center",
            at: "center",
            of: window
        },
        popupWidth: 0,
        popupHeight: 0,
        overlay: '.ui-widget-overlay',

        initialize: function () {
            this._super();
            this._initSubscribers();
            giftCardModel.init();
            this._initValidators();
        },
        _initValues: function() {
            var amountValue = productData.get('amount', false);
            if (amountValue) {
                var isCustom = true;
                $.each(this.amounts(), function() {
                    if (this.value == amountValue) {
                        isCustom = false;
                    }
                });
                this.amount(isCustom ? this.customAmountOption.value : amountValue);
                if (isCustom) {
                    this.amountCustom(amountValue);
                }
            }
            this.templateValue(productData.get('templateValue'), '');
            this.recipientName(productData.get('recipientName'), '');
            this.recipientEmail(productData.get('recipientEmail'), '');
            this.senderName(productData.get('senderName'), '');
            this.senderEmail(productData.get('senderEmail'), '');
            this.headline(productData.get('headline'), '');
            this.message(productData.get('message'), '');
        },

        /* Preview popup */
        showPreview: function(popup) {
            var self = this;
            var minInitWidth = 768;
            var minInitHeight = 768;
            self.popup = $(popup).dialog({
                dialogClass: "aw-gc-preview",
                width: self.calcPopupWidth($(window).width() < minInitWidth ? minInitWidth : $(window).width() * 0.7),
                height: self.calcPopupHeight($(window).height() < minInitHeight ? minInitHeight : $(window).height() * 0.7),
                position: self.popupPosition,
                resizable: true,
                modal: true,
                buttons: [
                    {
                        text: $t("OK"),
                        click: function() {
                            self.hidePreview();
                        }
                    }
                ]
            });
            $(self.overlay).on('click', $.proxy(this.hidePreview, this));
            $(window).on('resize', $.proxy(this.adjustPreview, this));
            $(window).on('scroll', $.proxy(this.adjustPreview, this));
        },
        hidePreview: function() {
            if (this.popup) {
                $(this.popup).dialog("close");
                this.popup = false;
            }
            $(this.overlay).off('click');
            $(window).off('resize', $.proxy(this.adjustPreview, this));
            $(window).off('scroll', $.proxy(this.adjustPreview, this));
        },
        adjustPreview: function(event) {
            if (this.popup && (!$(event.target).prop('nodeType') || event.type == 'scroll')) {
                $(this.popup).dialog('option', 'position', this.popupPosition);
                $(this.popup).dialog('option', 'width', this.calcPopupWidth());
                $(this.popup).dialog('option', 'height', this.calcPopupHeight());
            }
        },
        calcPopupWidth: function() {
            if (arguments.length > 0) {
                this.popupWidth = arguments[0];
            } else {
                this.popupWidth = $(this.popup).dialog('option', 'width');
            }
            return this.popupWidth < $(window).width() ? this.popupWidth : $(window).width();
        },
        calcPopupHeight: function() {
            if (arguments.length > 0) {
                this.popupHeight = arguments[0];
            } else {
                this.popupHeight = $(this.popup).dialog('option', 'height');
            }
            return this.popupHeight < $(window).height() ? this.popupHeight : $(window).height();
        },

        /* Subscribers */
        _initSubscribers: function() {
            giftCardModel.amounts.subscribe($.proxy(this.updateAmounts, this));
            giftCardModel.maxCustomAmount.subscribe($.proxy(this.updateMaxCustomAmount, this));
            giftCardModel.minCustomAmount.subscribe($.proxy(this.updateMinCustomAmount, this));
            giftCardModel.templates.subscribe($.proxy(this.updateTemplates, this));
            giftCardModel.isLoggedIn.subscribe($.proxy(this.updateIsLoggedIn, this));
            giftCardModel.dataLoaded.subscribe($.proxy(this.dataLoaded, this));

            this.amount.subscribe($.proxy(this._amountChange, this));
        },
        updateAmounts: function(amounts) {
            this.amounts(amounts);
            this.hasAmounts(amounts.length > 0);
        },
        updateMaxCustomAmount: function(maxCustomAmount) {
            if (maxCustomAmount) {
                this.maxCustomAmountValue(maxCustomAmount.value);
                this.allowCustomAmount(true);
                if (this.amounts().length == 0) {
                    this.amount(this.customAmountOption.value);
                }
                this.amounts.push(this.customAmountOption);
                this._updateCustomAmountHint();
            } else {
                this.allowCustomAmount(false);
            }
        },
        updateMinCustomAmount: function(minCustomAmount) {
            if (minCustomAmount) {
                this.minCustomAmountValue(minCustomAmount.value);
                this._updateCustomAmountHint();
            }
        },
        _updateCustomAmountHint: function() {
            this.customAmountHint('(' + this.minCustomAmountValue() + '–' + this.maxCustomAmountValue() +')');
        },
        updateTemplates: function(templates) {
            this.templates(templates);
        },
        updateIsLoggedIn: function(isLoggedIn) {
            this.isLoggedIn(isLoggedIn);
        },
        dataLoaded: function(dataLoaded) {
            this._initValues();
            this.sectionsShow(dataLoaded);
            this.isLoading(false);
        },
        _amountChange: function(amount) {
            this.isCustomAmount(amount == this.customAmountOption.value);
        },

        /* UI event handlers */
        templateSelect: function(value) {
            this.templateValue(value);
        },
        previewClick: function(url, formSelector, popupSelector) {
            var self = this;
            $.ajax({
                url: url,
                data: $(formSelector).serializeArray(),
                method: 'post',
                context: $('body'),
                showLoader: true
            }).success(function(response) {
                if (typeof response.success != "undefined") {
                    if (response.success) {
                        $(popupSelector).html(response.content);
                        $(popupSelector).find("a[href]").attr('target', '_blank');
                        self.showPreview(popupSelector);
                    } else {
                        alert(response.content);
                    }
                }
            });

        },

        /* Validators */
        _initValidators: function() {
            $.validator.addMethod(
                'aw-gc-min-amount',
                $.proxy(this._validateMinAmount, this),
                $t('Entered amount is too low')
            );
            $.validator.addMethod(
                'aw-gc-max-amount',
                $.proxy(this._validateMaxAmount, this),
                $t('Entered amount is too high')
            );
        },
        _validateMinAmount: function(value, element, params) {
            var amountValue = parseFloat(value);
            var minValue = parseFloat($(element).attr('min_value'));
            return !isNaN(amountValue) && !isNaN(minValue) && (amountValue >= minValue);
        },
        _validateMaxAmount: function(value, element, params) {
            var amountValue = parseFloat(value);
            var maxValue = parseFloat($(element).attr('max_value'));
            return !isNaN(amountValue) && !isNaN(maxValue) && (amountValue <= maxValue);
        }
    });
});

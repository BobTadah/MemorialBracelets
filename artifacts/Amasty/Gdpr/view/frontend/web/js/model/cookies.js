define([
    'uiComponent',
    'jquery',
    'mage/cookies'
], function (Component, $) {
    'use strict';

    return Component.extend({
        defaults: {
            noticeType: 1,
            template: 'Amasty_Gdpr/notice',
            disallowLink: '/',
            allowLink: '/'
        },
        initObservable: function () {
            this._super()
                .observe({
                    showDisallowButton: this.noticeType === 2
                });

            return this;
        },
        disallowCookies: function () {
            location.href = this.disallowLink;
        },
        allowCookies: function () {
            location.href = this.allowLink;
        },
        isShowNotificationBar: function () {
            if (this.noticeType === 0 || $.mage.cookies.get('am_allow_cookies') !== null) {
                return false;
            }

            return true;
        }
    });
});

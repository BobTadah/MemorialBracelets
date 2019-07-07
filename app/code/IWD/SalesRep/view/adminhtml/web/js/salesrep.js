define([
    'jquery',
    'ko',
    'uiRegistry',
    'Magento_Ui/js/lib/validation/validator',
    'Magento_Ui/js/modal/modal'
], function ($, ko, registry, validator, modal) {
    $.widget('iwd.salesrep', {
        _create: function () {
            var self = this;
            self.assignCustomerHandler();
            self.commissionTypeChangeHandler();
            self.updateCommissionHandler();
        },
        updateCommissionHandler: function () {
            var self = this;
            $(document).on('click', '.iwdsr-update-commission', function (e) {
                e.stopPropagation();
                e.preventDefault();
                var _this = $(this);
                if (_this.closest('tr').hasClass('iwdsr-disabled')
                    || _this.hasClass('disabled')
                )
                    return;

                $.ajax({
                    showLoader: true,
                    url: self.options.commissionBlockUrl,
                    data: {salesrep_id: self.options.salesrepId, customer_id: _this.data('customerId')},
                    dataType: 'html',
                    success: function (response) {
                        response = JSON.parse(response);
                        var date = Date.now(),
                            uniqForm = 'iwdsr-' + date,
                            uniqComponent = 'salesrep_user_commission_form' + date;
                        $('<div />').html(response.html.replace(/salesrep_user_commission_form/g, uniqComponent))
                            .modal({
                                modalClass: 'iwdsr-commission-modal ' + uniqForm,
                                title: 'Commission Settings',
                                autoOpen: true,
                                buttons: [{
                                    text: 'Save',
                                    class: 'action primary' + (!response.res ? ' disabled' : ''),
                                    click: function (e) {
                                        var source = registry.get(uniqComponent + '.commission_form_data_source');
                                        if (source && typeof source.data === 'object') {
                                            self.submitSaveCommission(source.data, uniqForm, uniqComponent);
                                        }
                                    }
                                }]
                            }).trigger('contentUpdated').applyBindings();

                        var timer = setTimeout(function(){
                            var commissionTypes = $('.' + uniqForm).find('[name*="commission_type"]');
                            if(commissionTypes.length) {
                                commissionTypes.trigger('change');
                                clearTimeout(timer);
                            }
                        }, 500);
                    }
                });

            });
        },
        submitSaveCommission: function (data, form, component) {
            var self = this,
                fieldset = registry.get(component + '.' + component + '.general'),
                optionsGrid = registry.get(component + '.' + component + '.general.container_tiered_options_grid');
            var _modal = $('.' + form), _modalContent = _modal.find('.modal-content');
            if (!self.options.commissionPostUrl || data.length === 0) {
                return;
            }

            if(!self.validateComponent(fieldset) || !self.validateComponent(optionsGrid)) {
                return;
            }

            $.ajax({
                url: self.options.commissionPostUrl,
                showLoader: true,
                data: data,
                dataType: 'json',
                success: function (response) {
                    if (response.res == true && response.customerId) {
                        $('#id_' + response.customerId).closest('tr').find('td.col-salesrep_action').html(response.html); // update grid value
                        _modal.find('[data-role=closeBtn]').click(); // close popup
                    } else {
                        self.showMessage(response.message, _modalContent);
                    }
                },
                error: function (response) {
                    self.showMessage(response.statusText, _modalContent);
                }
            });
        },
        validateComponent: function(options) {
            var isValid = true;

            $.each(options.elems(), function (i, row) {
                if(row.elems) {
                    $.each(row.elems(), function (j, field) {
                        if (field.validate && !field.validate().valid) {
                            isValid = false;
                        }
                    });
                }
                else if (row.validate && !row.validate().valid) {
                    isValid = false;
                }
            });

            return isValid;
        },
        showMessage: function (text, modalContent) {
            var msg = $('<p>')
                .css({color: 'red'})
                .attr('class', 'iwdsr-delayed-exit')
                .text(text);
            modalContent.append(msg);
            setTimeout(function () {
                msg.hide(function (e) {
                    $(this).remove();
                });
            }, 2000);
        },
        assignCustomerHandler: function () {
            var self = this;
            // disable click on checkbox loader
            $(document).on('click', '.iwd-sr-loader', function (e) {
                e.preventDefault();
                e.stopPropagation();
                return;
            });
            $(document).on('change', '#iwd_salesrep_customers .iwd-salesrep-assign [type=checkbox]', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var _checkBox = $(this);
                if (_checkBox.parent().hasClass('iwd-sr-loader'))
                    return;
                var customerId = _checkBox.val();
                var _checked = +(_checkBox.is(':checked'));
                _checkBox.wrap('<span class="fa fa-circle-o-notch fa-spin iwd-sr-loader"></span>');

                // disable Update btn for commission
                if (!_checked) {
                    _checkBox.closest('tr').find('.iwdsr-update-commission').addClass('disabled');
                }
                $.ajax({
                    url: self.options.attachUrl,
                    method: 'post',
                    data: {
                        customer_id: customerId,
                        salesrep_id: self.options.salesrepId,
                        attach: _checked,
                    },
                    dataType: 'json',
                    success: function (response) {
                        delete window.iwdsrxhr;
                        _checkBox.unwrap();
                        if (response.res == true) {
                            var actionCell = _checkBox.closest('tr').find('.col-salesrep_action');
                            actionCell.html(response.actionHtml);
                        } else {
                            _checkBox.prop("checked", _checkBox.prop('checked') ? false : true);
                            alert(response.message);
                        }
                    }
                });
            });
        },
        commissionTypeChangeHandler: function () {
            $(document).on('change', '[name*="commission_type"]', function (e) {
                var _this = $(this);
                var applyWhenSelect = _this.closest('.admin__dynamic-rows').length ?
                    _this.closest('.data-row').find('[name*="commission_apply"]') :
                    _this.closest('fieldset').find('[name="commission_apply"]');
                applyWhenSelect.prop('disabled', _this.val() == 'fixed');
            });
        }
    });

    return $.iwd.salesrep;
});
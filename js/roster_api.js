RosterApi = {
    formValid: false,
    waiverRead: false,
    duesAmount: jsNamespace.duesAmount,
    extraItemsTotal: 0,
    paypalItemList: [],
    validator: null,
    init: function () {
        this._setupDialog();
        this._setListeners();
        this._chooseAction(true);
    },
    paypalSuccess: function () {
        this._doAjax('roster_api_update', 'member_update');
    },
    getTotal: function () {
        return parseFloat(this.duesAmount);
    },
    getPaymentInfo: function () {
    // TODO: Implement this for additional items
        var payment = {
            payment: {
                intent: "sale",
                payer: {
                    payment_method: "paypal"
                }
            },
        };
        payment['transactions'] = [{
            amount: {
                total: this._getTotal(),
                currency: "USD"
            }
        }];
        return payment;
    },
    _chooseAction: function (isNew) {
        var formId = (isNew) ? '#member_update' : '#member_verify';
        var formCallback = (isNew) ? this._validateNewMember : this._validateRenewal;
        //this.validator =  Object.create(Validate);
        Validate.init({
            formId: formId,
            caller: this,
            callback: formCallback
        });

        this._toggleProcessType(isNew);
        
        if (! isNew) {
            this._enablePayPalButton();
        }
        jQuery('#rapi_form, #waiver_wrapper').toggle(isNew);
        jQuery('#rapi_renew').toggle(!isNew);
    },
    _doAjax: function (action, formId) {
        var self = this;
        var formData = jQuery('#' + formId).serialize();
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: jsNamespace.ajaxUrl,
            data: {
                action: action,
                data: formData
            },
            success: function (response) {
                if (response.action) {
                    if (response.action === 'verify') {
                        self._handleVerifyResponse(response);
                    }
                    if (response.action === 'update') {
                        self._handleUpdateResponse(response);
                    }
                } else {
                    console.log(response);
                }
            },
            error: function (response) {
                console.log(response);
            }
        });
    },
    _enablePayPalButton: function() {
        var valid = (this.waiverRead && this.formValid);

        jQuery('#paypal_wrapper').css({
            'opacity': (valid) ? '1.0' : '0.5',
            'pointer-events': (valid) ? 'auto' : 'none'
        });
    },
    _enableVerifyButton: function () {
        var self = this;
        jQuery('#existing_member')
            .prop('disabled', false)
            .off()
            .on('click', function (evt) {
                evt.preventDefault();
                self._doAjax('roster_api_verify', 'member_verify');
            });
    },
    _getItemList: function () {
        return this.paypalItemList;
    },
    _handleVerifyResponse: function (response) {
        jQuery('.form-error').remove();
        jQuery('#member_verify_message').html(response.data).show();
        jQuery('#rapi_form').show();
        jQuery('#member_fields, #existing_member, #rapi_choice, #required_message').hide();
        this._enablePayPalButton();
    },
    _handleUpdateResponse: function (response) {
        if (response.success) {
            document.location = jsNamespace.confirmationPage;
        } else {
            if (response.data) {
                if (response.data.errors !== undefined) {
                    this._showErrors(response.data.errors);
                }
            }
        }
    },
    _setupDialog: function () {
        var self = this;

        jQuery('#waiver_modal').dialog({
            dialogClass: 'wp-dialog',
            autoOpen: false,
            draggable: true,
            width: '50%',
            modal: true,
            resizable: false,
            closeOnEscape: true,
            position: {
                my: "center",
                at: "top",
                of: window
            },
            open: function (evt, ui) {
                var top = jQuery('html').offset().top;
                var height = jQuery(window).height();
                jQuery(this).css({
                    'top': top,
                    'height': (height * .7)
                    });
            },
            close: function () {
                jQuery('label.waiver').css('color', 'black');
                jQuery('#waiver')
                    .prop('disabled', false)
                    .prop('checked', true);
                self.waiverRead = true;
                self._enablePayPalButton();
            },
            create: function () {
                // Style fix for WordPress admin
                jQuery('.ui-dialog-titlebar-close').addClass('ui-button');
            },
            buttons: {
                'Close': function () {
                    jQuery(this).dialog('close');
                }
            }
        });
    },
    _setListeners: function () {
        var self = this;

        jQuery('[name="type_choice"]').on('click', function () {
            var $this = jQuery(this);
            var isNew = ($this.val() === '0');

            self._chooseAction(isNew);
        });

        jQuery('[id^="amount_"]').on('click', function () {
            self._writeItemList();
        });

        jQuery('#waiver_link, #waiver_wrapper').on('click', function (evt) {
            evt.preventDefault();
            self._showWaiver();
        });

        jQuery('#waiver').on('click', function (evt) {
            self.waiverRead = jQuery(this).prop('checked');
        });

        jQuery('#test_button').on('click', function (evt) {
            evt.preventDefault();
            self._doAjax('roster_api_update', 'member_update');
        });

    },
    _showWaiver: function () {
        jQuery('#waiver_modal').dialog('open');
    },
    _toggleProcessType: function (isNew) {
        var type = (isNew) ? 'new_member' : 'renewal';

        jQuery('[name="process_type"]').val(type);
    },
    _validateNewMember: function (self, isValid) {
        self.formValid = isValid;
        self._enablePayPalButton();
    },
    _validateRenewal: function (self, isValid) {
        if (isValid) {
            self.formValid = isValid;
            self.waiverRead = true;
            self._enableVerifyButton();
        }
    },
    _writeItemList: function () {
        var self = this;
        var items = [];
        var itemList = [];

        this.paypalItemList = [];
        this.extraItemsTotal = 0;

        itemList['Dues'] = parseFloat(this.duesAmount);

        jQuery('[id^="amount_"]').each(function () {
            var $this = jQuery(this);
            var amount = $this.val();
            var label = $this.attr('data-label')
            if ($this.prop('checked')) {
                itemList[label] = parseFloat(amount);
                self.extraItemsTotal += amount;
            }
        });

        for (var label in itemList) {
            if (itemList.hasOwnProperty(label)) {
                var item = {
                    name: label.replace(' ', '_').toLowerCase(),
                    description: label,
                    quantity: "1",
                    price: itemList[label],
                    sku: "",
                    currency: "USD"
                }
                items.push(item);
            }
        }
        this.paypalItemList = items;
    }
};

jQuery('.imgedit-menu').ready(function ($) {
    jsNamespace.rosterApi = Object.create(RosterApi);
    jsNamespace.rosterApi.init();
});
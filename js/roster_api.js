RosterApi = {
    waiverRead: false,
    duesAmount: jsNamespace.duesAmount,
    extraItemsTotal: 0,
    paypalItemList: [],
    validRequired: {},
    init: function () {
        this._setupDialog();
        this._loadValidations();
        this._setListeners();
        jQuery('#rapi_form').show();
    },
    paypalSuccess: function () {
        self._doAjax('roster_api_update', 'member_update');
    },
    getTotal: function () {
        return parseFloat(this.duesAmount);
    },
    getPaymentInfo: function () {
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

//        if (this.extraItemsTotal > 0) {
//            payment['transactions']['item_list'] = this._getItemList();
//        }
        return payment;
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
                data: formData,
            },
            success: function (response) {
                if (response.action) {
                    if (response.action === 'fetch') {
                        self._handleFetchResponse(response);
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
    _getItemList: function () {
        return this.paypalItemList;
    },
    _handleFetchResponse: function (response) {
        jQuery('.form-error').remove();
        jQuery('#member_fetch_message').html(response.data).show();
        jQuery('#rapi_form').show();
        jQuery('#member_fields, #existing_member, #rapi_choice').hide();
    },
    _handleUpdateResponse: function (response) {
        if (response.success) {
        } else {
            if (response.data.errors !== undefined) {
                this._showErrors(response.data.errors);
            }
        }
    },
    _loadValidations: function () {
        var self = this;
        // Set all required fields to false to begin with
        jQuery('#member_update *').filter(':input').each(function () {
            self._setValid(jQuery(this), false);
        });
    },
    _setValid: function ($this, truth) {
        var value = $this.val();
        var fieldName = $this.attr('name');
        if ($this.hasClass('required')) {
            this.validRequired[fieldName] = truth;
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
                at: "center",
                of: window
            },
            open: function (evt, ui) {
                jQuery(this).css('height', '300px');
            },
            close: function () {
                jQuery('label.waiver').css('color', 'black');
                jQuery('#waiver').prop('disabled', false);
                self.waiverRead = true;
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

            //jQuery('#rapi_choice').hide();
            jQuery('#rapi_form').toggle(isNew);
            jQuery('#rapi_renew').toggle(!isNew);
        });

        jQuery('[id^="amount_"]').on('click', function () {
            self._writeItemList();
        });

        jQuery('#waiver_link').on('click', function (evt) {
            evt.preventDefault();
            self._showWaiver();
        });

        jQuery('#waiver').on('click', function (evt) {
            self.waiverRead = jQuery(this).prop('checked');
        });

        jQuery('#existing_member').on('click', function (evt) {
            evt.preventDefault();
            self._doAjax('roster_api_fetch', 'member_fetch');
        });

        jQuery('#member_update *').filter(':input').on('keyup change', function (evt) {
            var $this = jQuery(this);
            var isValid = true;

            self._setValid(jQuery(this), ($this.val() !== ''));

            for  (var field in self.validRequired) {
                if (self.validRequired.hasOwnProperty(field)) {
                    if (!self.validRequired[field]) {
                        isValid = false;
                    }
                }
            }

            this._toggleValidation(isValid);
        });
    },
    _showErrors: function (errors) {
        jQuery('.form-error').remove();
        for (var key in errors) {
            if (errors.hasOwnProperty(key)) {
                var $field = jQuery('[name="' + key + '"]');
                $field.after('<div class="form-error">' + errors[key] + '</div>');
            }
        }
    },
    _showWaiver: function () {
        jQuery('#waiver_modal').dialog('open');
    },
    _toggleValidation: function (isValid) {
        if (isValid) {
            
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
RosterApi = {
    waiverRead: false,
    init: function() {
        this._setListeners();
    },
    paypalSuccess: function() {
        self._doAjax('roster_api_update', 'member_update');
    },
    _doAjax: function(action, formId) {
        var self = this;
        var formData = jQuery('#' + formId).serialize();
        jQuery.ajax({
            type : "post",
            dataType : "json",
            url : jsNamespace.ajaxUrl,
            data : {
                action: action,
                data: formData,
            },
            success: function(response) {
                if (response.action) {
                    if (response.action == 'fetch') {
                        self._handleFetchResponse(response);
                    }
                    if (response.action == 'update') {
                        self._handleUpdateResponse(response);
                    }
                } else {
                    console.log(response);
                }
            },
            error: function(response) {
                console.log(response);
            }
        });
    },
    _handleFetchResponse: function(response) {
        jQuery('.form-error').remove();
        jQuery('#member_fetch_message').html(response.data).show();
        jQuery('#rapi_form').show();
        jQuery('#member_fields, #existing_member, #rapi_choice').hide();
    },
    _handleUpdateResponse: function(response) {
        if (response.success) {
        } else {
            if (response.data.errors !== undefined) {
                this._showErrors(response.data.errors);
            }
        }
    },
    _setupDialog: function() {
        jQuery('#crop_modal').dialog({
            dialogClass: 'wp-dialog',
            autoOpen: false,
            draggable: true,
            width: 'auto',
            modal: true,
            resizable: false,
            closeOnEscape: true,
            position: {
                my: "center",
                at: "center",
                of: window
            },
            close: function() {
            },
            create: function () {
                // Style fix for WordPress admin
                $('.ui-dialog-titlebar-close').addClass('ui-button');
            },
            buttons: {
                'Close': function() {
                    $(this).dialog('close');
                }
            }
        })
    },
    _setListeners: function() {
        var self = this;

        jQuery('[name="type_choice"').on('click', function() {
            var $this = jQuery(this);
            var isNew = ($this.val() == '0');

            jQuery('#rapi_choice').hide();
            jQuery('#rapi_form').toggle(isNew);
            jQuery('#rapi_renew').toggle(!isNew);
        });

        jQuery('[id^="amount_"').on('click', function() {
            jQuery('[id^="amount_"').each(function() {
                var $this = jQuery(this);
            });
        });

        jQuery('#waiver_link').on('click', function(evt) {
            evt.preventDefault();
            this.waiverRead = true;
            self._showWaiver();
        });

        jQuery('#existing_member').on('click', function(evt) {
            evt.preventDefault();
            self._doAjax('roster_api_fetch', 'member_fetch');
        });

    },
    _showErrors: function(errors) {
        jQuery('.form-error').remove();
        for (var key in errors) {
            if (errors.hasOwnProperty(key)) {
                var $field = jQuery('[name="' + key + '"]');
                $field.after('<div class="form-error">' + errors[key] + '</div>');
            }
        }
    },
    _showWaiver: function() {
        jQuery('#waiver_modal').dialog();
    }
};

jQuery('.imgedit-menu').ready(function ($) {
    jsNamespace.rosterApi = Object.create(RosterApi);
    jsNamespace.rosterApi.init();
});
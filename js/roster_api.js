RosterApi = {
    init: function() {
        this._setListeners();
    },
    _doAjax: function(action, formId) {
        var self = this;
        var formData = jQuery('#' + formId).serialize();
        jQuery.ajax({
            type : "post",
            dataType : "json",
            url : ajax_params.ajax_url,
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
        var data = response.data;

        jQuery('.form-error').remove();
        if (data !== null) {
            // Show the form and hide the 'renewing' fields
            jQuery('[name="email"]')
                .prop('disabled', true)
                .addClass('disable');

            jQuery('#rapi_form').show();
            jQuery('#rapi_renew').hide();
            this._populateForm('#member_update', data);
        } else {
            jQuery('#member_fetch_error').show();
        }
    },
    _handleUpdateResponse: function(response) {
        if (response.success) {
        } else {
            if (response.data.errors !== undefined) {
                this._showErrors(response.data.errors);
            }
        }
    },
    _populateForm: function(formId, data) {
        jQuery.each(data, function(key, value){
            jQuery('[name=' + key + ']', formId).val(value);
        });
    },
    _setListeners: function() {
        var self = this;

        jQuery('[name="type_choice"').on('click', function() {
            var $this = jQuery(this);
            var isNew = ($this.val() == '0');

            jQuery('#rapi_form').toggle(isNew);
            jQuery('#rapi_renew').toggle(!isNew);
        });

        jQuery('#existing_member').on('click', function(evt) {
            evt.preventDefault();
            self._doAjax('roster_api_fetch', 'member_fetch');
        });

        jQuery('#save_member').on('click', function(evt) {
            evt.preventDefault();
            self._doAjax('roster_api_update', 'member_update');
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
    }
};

jQuery('.imgedit-menu').ready(function ($) {
    var rosterApi = Object.create(RosterApi);
    rosterApi.init();
});
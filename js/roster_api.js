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
            url : '/wordpress/wp-admin/admin-ajax.php',
            data : {
                action: action,
                data: formData,
            },
            success: function(response) {
                if (response.success) {
                    if (response.action == 'fetch') {
                        var data = response.data;
                        if (data.length !== 0) {
                            jQuery('#rapi_form').show();
                            jQuery('#rapi_renew').hide();
                            self._populateForm('#member_update', data);
                        }
                    }
                    if (response.action == 'save') {
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
    }
};

jQuery('.imgedit-menu').ready(function ($) {
    var rosterApi = Object.create(RosterApi);
    rosterApi.init();
});
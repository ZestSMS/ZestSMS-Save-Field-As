(function ($) {
    ZestSMSSaveFieldAsSettings = {

        _init: function () {
            this._bindEvents();
        },

        _bindEvents: function () {
            $('body').delegate('.fl-builder-settings .zestsms-create-save-field-as-button,.fl-builder-settings .zestsms-edit-save-field-as-button', 'click', this._showSettingsForm);
            $('body').delegate('.fl-builder-settings .zestsms-remove-save-field-as-button', 'click', this._removeSaveFieldAs);
            $('body').delegate('.fl-builder-settings .zestsms-trash-save-field-as-button', 'click', this._deleteSaveFieldAs);
            $('body').delegate('.zestsms-save-field-as-settings .fl-builder-settings-save', 'click', this._saveSettingsFormClicked);
            $('body').delegate('.zestsms-save-field-as-settings .fl-builder-settings-cancel', 'click', this._cancelSettingsFormClicked);
        },

        _showSettingsForm: function () {
            var link = $(this),
                field = $(this).closest('.fl-field'),
                val = field.find('.zestsms-save-field-as-value').val(),
                type = link.attr('data-type'),
                helper = FLBuilder._moduleHelpers[type],
                settings = null;

            if ('' !== val) {
                settings = JSON.parse(val);
            }

            var lightbox = FLBuilder._openNestedSettings({
                className: 'fl-builder-lightbox zestsms-save-field-as-settings'
            });

            field.addClass('zestsms-save-field-as-editing');

            FLBuilder.ajax({
                action: 'render_settings_form',
                settings: settings,
                type: 'zestsms_save_field_as'
            }, function (response) {

                var data = JSON.parse(response);

                lightbox._node.find('.fl-lightbox-content').html(data.html);
                lightbox._node.find('form.fl-builder-settings').attr('data-type', type);
                FLBuilder._initSettingsForms();

                if ( typeof helper !== 'undefined' ) {
                    FLBuilder._initSettingsValidation( helper.rules );
                    helper.init();
                }
            });
        },

        _saveSettingsFormClicked: function () {
            var form = $('.zestsms-save-field-as-settings form'),
                settings = FLBuilder._getSettings(form),
                field = $('.zestsms-save-field-as-editing'),
                wrapper = field.find('.zestsms-save-field-as-controls'),
                value = field.find('.zestsms-save-field-as-value'),
                oldSettings = value.val().replace(/&#39;/g, "'"),
                valid = true;


            form.find('label.error').remove();
            form.validate().hideErrors();
            valid = form.validate().form();

            if( valid ) {
                if ( '' != oldSettings ) {
                    settings = $.extend( JSON.parse( oldSettings ), settings );
                }

                value.val(JSON.stringify(settings)).trigger('change');
                wrapper.attr('data-saved', 'true');
                field.removeClass('zestsms-save-field-as-editing');

                FLBuilder._closeNestedSettings();

                return true;
            }
            else {
                FLBuilder._toggleSettingsTabErrors();
                return false;
            }

        },

        _removeSaveFieldAs: function () {
            var field = $(this).closest('.fl-field'),
                wrapper = field.find('.zestsms-save-field-as-controls'),
                settings = field.find('.zestsms-save-field-as-value');

            wrapper.attr('data-saved', 'false');
            // connections_toggle.show();
            settings.val('');
        },

        _deleteSaveFieldAs: function () {
            var field = $(this).closest('.fl-field'),
                wrapper = field.find('.zestsms-save-field-as-controls'),
                value = field.find('.zestsms-save-field-as-value'),
                val = value.val(),
                parsed = JSON.parse(val),
                confirm_message = 'Are you sure you want to delete the ' + parsed.type + ' \'' + parsed.key + '\'';

            confirm_message += ( parsed.type == 'wp_option' ) ? '?' : ' for this page?';

            var confirmation = confirm(confirm_message);

            if (confirmation) {
                parsed.delete = true;
                value.val(JSON.stringify(parsed));
                wrapper.attr('data-saved', 'false');
            }
        },

        _cancelSettingsFormClicked: function () {
            var field = $('.zestsms-save-field-as-editing'),
                val = field.find('.zestsms-save-field-as-value').val();

            field.removeClass('fl-field-connection-editing');
        }

    }

    ZestSMSSaveFieldAsSettings._init();

})(jQuery);
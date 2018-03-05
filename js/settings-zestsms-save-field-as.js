(function ($) {
    ZestSMSSaveFieldAsSettings = {

        _init: function () {
            this._bindEvents();
            this._moduleSettingsClicked();
            FLBuilder.addHook('settings-form-init', function() { ZestSMSSaveFieldAsSettings._moduleSettingsClicked() } );
        },

        _bindEvents: function () {
            $('body').delegate('.fl-builder-settings .zestsms-create-save-field-as-button,.fl-builder-settings .zestsms-edit-save-field-as-button', 'click', this._showSettingsForm);
            $('body').delegate('.fl-builder-settings .zestsms-remove-save-field-as-button', 'click', this._removeSaveFieldAs);
            $('body').delegate('.fl-builder-settings .zestsms-trash-save-field-as-button', 'click', this._deleteSaveFieldAs);
            $('body').delegate('.zestsms-save-field-as-settings .fl-builder-settings-save', 'click', this._saveSettingsFormClicked);
            $('body').delegate('.zestsms-save-field-as-settings .fl-builder-settings-cancel', 'click', this._cancelSettingsFormClicked);
        },

        _moduleSettingsClicked: function () {
            $('.fl-builder-settings').find('.fl-field-control-wrapper .fl-field-connection').each(function(){
                var field_connection = $(this),
                    field_control_wrapper = field_connection.parent(),
                    connections_toggle = field_connection.siblings('.fl-field-connections-toggle'),
                    zestsms_save_field_as_value = field_control_wrapper.find('.zestsms-save-field-as-value'),
                    zestsms_save_field_as_settings = (zestsms_save_field_as_value.val() !== '') ? JSON.parse(zestsms_save_field_as_value.val()) : '';

                field_control_wrapper.addClass('zestsms-save-field-as');

                if( zestsms_save_field_as_settings !== '' && ! zestsms_save_field_as_settings.delete ) {
                    field_control_wrapper.addClass('is-saved');
                    connections_toggle.hide();
                } else {
                    field_control_wrapper.addClass('not-saved');
                }
            });
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
                wrapper = field.find('.zestsms-save-field-as'),
                connections_toggle = field.find('.fl-field-connections-toggle'),
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
                wrapper.removeClass('not-saved').addClass('is-saved');
                connections_toggle.hide();
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
                wrapper = field.find('.zestsms-save-field-as.is-saved'),
                connections_toggle = field.find('.fl-field-connections-toggle'),
                settings = field.find('.zestsms-save-field-as-value');

            wrapper.removeClass('is-saved').addClass('not-saved');
            connections_toggle.show();
            settings.val('');
        },

        _deleteSaveFieldAs: function () {
            var field = $(this).closest('.fl-field'),
                wrapper = field.find('.zestsms-save-field-as.is-saved'),
                connections_toggle = field.find('.fl-field-connections-toggle'),
                value = field.find('.zestsms-save-field-as-value'),
                val = value.val(),
                parsed = JSON.parse(val),
                confirm_message = 'Are you sure you want to delete the ' + parsed.type + ' \'' + parsed.key + '\'';

            confirm_message += ( parsed.type == 'wp_option' ) ? '?' : ' for this page?';

            var confirmation = confirm(confirm_message);

            if (confirmation) {
                parsed.delete = true;
                value.val(JSON.stringify(parsed));
                wrapper.removeClass('is-saved').addClass('not-saved');
                connections_toggle.show();
            }
        },

        _cancelSettingsFormClicked: function () {
            var field = $('.zestsms-save-field-as-editing'),
                val = field.find('.zestsms-save-field-as-value').val();

            field.removeClass('fl-field-connection-editing');
        }

    }

    ZestSMSSaveFieldAsSettings._init();

    // FLBuilder.registerModuleHelper('zestsms_save_field_as', {
    //     init: function () {
    //         var form = $('.zestsms-save-field-as-settings');
    //
    //         if( form.length ) {
    //             var type = form.find('select[name=type]')
    //
    //             this._typeChanged();
    //             type.on('change', this._typeChanged);
    //         }
    //     },
    //
    //     _typeChanged: function () {
    //         var form = $('.fl-builder-settings'),
    //             type = form.find('select[name=type]').val(),
    //             key = form.find('select[name=key]'),
    //             key_val = key.val(),
    //             option = form.find('input[name=option]');
    //
    //         key.rules('remove');
    //         option.rules('remove');
    //
    //         if (type == 'wp_option') {
    //             option.rules('add', {
    //                 required: true
    //             });
    //         } else if (type == 'post_meta') {
    //             key.rules('add', {
    //                 required: true
    //             });
    //         }
    //     }
    // });

})(jQuery);
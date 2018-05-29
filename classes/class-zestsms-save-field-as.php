<?php

final class ZestSaveFieldAs {

    static public function init() {
        add_action('init', __CLASS__ . '::register_form');
        add_filter('fl_builder_before_control', __CLASS__ . '::save_field_as_buttons', 9, 4);
        add_filter('fl_builder_node_settings', __CLASS__ . '::set_defaults', 10, 2);
        add_action('fl_builder_before_render_ajax_layout', __CLASS__ . '::before_render_ajax_layout', 10, 2);
        add_action('fl_builder_before_save_layout', __CLASS__ . '::update_post', 10, 4);
        add_action('updated_postmeta', __CLASS__ . '::updated_postmeta', 10, 4);
    }

    static public function get_meta_options() {
        $post_id = FLBuilderModel::get_post_id();
        $options = array('' => '');

        if ($postmeta = get_post_meta($post_id)) {
            foreach ($postmeta as $key => $val) {
                if (substr($key, 0, 1) !== '_') {
                    $options[$key] = $key;
                }
            }
        }

        if ($_POST && array_key_exists('fl_builder_data', $_POST)) {
            $posted = $_POST['fl_builder_data']['settings'];
            $posted = maybe_unserialize($posted);
            $options[$posted['key']] = $posted['key'];
        }


        return $options;
    }

    static public function register_form() {
        FLBuilder::register_settings_form('zestsms_save_field_as', array(
            'title' => __('Save field as', 'zestsms'),
            'tabs'  => array(
                'general' => array(
                    'title'    => __('General', 'zestsms'),
                    'sections' => array(
                        'general' => array(
                            'title'  => __('Field Settings', 'zestsms'),
                            'fields' => array(
                                'type'   => array(
                                    'type'    => 'select',
                                    'label'   => __('Save field as', 'zestsms'),
                                    'default' => 'post_meta',
                                    'options' => array(
                                        'post_meta' => __('Post Meta', 'zestsms'),
                                        'wp_option' => __('WP Option', 'zestsms')
                                    ),
                                    'toggle'  => array(
                                        'post_meta' => array(
                                            'fields' => array('key')
                                        ),
                                        'wp_option' => array(
                                            'fields' => array('option')
                                        )
                                    )
                                ),
                                'key'    => array(
                                    'type'  => 'text',
                                    'label' => __('Meta Key', 'zestsms')
                                ),
                                'option' => array(
                                    'type'  => 'text',
                                    'label' => __('Option', 'zestsms')
                                )
                            )
                        )
                    )
                )
            )
        ));
    }

    static public function save_field_as_buttons($name, $value, $field, $settings) {
        global $post;

        if ( !array_key_exists('connections', $field)) {
            return;
        }

        $zestsms_save_field_as = false;

        if (isset($settings->zestsms_save_field_as)) {

            $settings->zestsms_save_field_as = (array)$settings->zestsms_save_field_as;

            if (isset($settings->zestsms_save_field_as[$name])) {

                if (is_string($settings->zestsms_save_field_as[$name])) {
                    $settings->zestsms_save_field_as[$name] = json_decode($settings->zestsms_save_field_as[$name]);
                }
                if (is_object($settings->zestsms_save_field_as[$name])) {
                    $zestsms_save_field_as = $settings->zestsms_save_field_as[$name];
                }
            }
        }

        echo '<div class="zestsms-save-field-as-controls"  data-saved="';
        echo ( $zestsms_save_field_as ) ? 'true' : 'false';
        echo '">';

        echo '<i class="fa fa-link zestsms-save-field-as-button zestsms-save-field-as-button-add zestsms-create-save-field-as-button" data-type="zestsms_save_field_as" title="' . __('Save field as', 'zestsms') . '"></i>';
        echo '<i class="fa fa-wrench zestsms-save-field-as-button zestsms-save-field-as-button-edit zestsms-edit-save-field-as-button" data-type="zestsms_save_field_as" title="' . __('Edit field save settings', 'zestsms') . '"></i>';
        echo '<i class="fa fa-unlink zestsms-save-field-as-button zestsms-save-field-as-button-edit zestsms-remove-save-field-as-button" data-type="zestsms_save_field_as" title="' . __('Unlink field save settings', 'zestsms') . '"></i>';

        if (isset($settings->zestsms_save_field_as[$name])) {
            if (property_exists($settings->zestsms_save_field_as[$name], 'key')) {
                echo '<i class="fa fa-trash-o zestsms-save-field-as-button zestsms-save-field-as-button-edit zestsms-trash-save-field-as-button" data-type="zestsms_save_field_as" title="' . __('Unlink field save settings and DELETE in database', 'zestsms') . '"></i>';
            }
        }

        echo '<input class="zestsms-save-field-as-value" name="zestsms_save_field_as[][' . $name . ']" type="hidden" value=\'';
        if ($zestsms_save_field_as) {
            echo json_encode($zestsms_save_field_as);
        }
        echo '\' />';

        echo '</div>';
    }

    static public function set_defaults($settings, $node) {
        $post_data = FLBuilderModel::get_post_data();

        if (array_key_exists('action', $post_data)) {
            if ($post_data['action'] == 'copy_module' || $post_data['action'] == 'copy_row') {
                $node_grand_parent = FLBuilderModel::get_node_parent($node->parent);
                $node_great_grand_parent = FLBuilderModel::get_node_parent($node_grand_parent);

                if ($node->node == $post_data['node_id'] || $node_grand_parent->node == $post_data['node_id'] || $node_great_grand_parent->node == $post_data['node_id']) {
                    unset($settings->zestsms_save_field_as);
                }

                return $settings;
            }
        }

        if (is_object($settings) && isset($settings->zestsms_save_field_as)) {
            foreach ($settings->zestsms_save_field_as as $field => $field_meta) {
                if (isset($field_meta->type)) {
                    $post_meta = '';

                    if (property_exists($field_meta, 'delete')) {
                        $settings->zestsms_save_field_as[$field] = '';

                        return $settings;
                    }

                    if ($field_meta->type == 'post_meta') {
                        $post_meta = get_post_meta(FLBuilderModel::get_post_id(), $field_meta->key, true);
                    }
                    if ($field_meta->type == 'wp_option') {
                        $post_meta = get_option($field_meta->option);
                    }


                    if ($post_meta !== '') {
                        $settings->$field = $post_meta;
                    }
                }
            }
        }

        return $settings;
    }

    static public function before_render_ajax_layout() {
        remove_filter('fl_builder_node_settings', __CLASS__ . '::set_defaults', 10, 2);
    }

    static public function update_post($post_id, $publish, $data, $settings) {
        if ($publish) {
            if (!empty($data)) {
                remove_action('updated_postmeta', __CLASS__ . '::updated_postmeta');

                foreach ($data as $node_id => $object) {
                    if (isset($object->settings) && isset($object->settings->zestsms_save_field_as)) {
                        foreach ($object->settings->zestsms_save_field_as as $field => $field_meta) {
                            if ('fl-builder-template' !== get_post_type($post_id)) {
                                if (isset($field_meta->type)) {
                                    if ($field_meta->type == 'post_meta') {
                                        if (property_exists($field_meta, 'delete')) {
                                            delete_post_meta($post_id, $field_meta->key);
                                        } else {
                                            update_post_meta($post_id, $field_meta->key, $data[$node_id]->settings->$field);
                                        }
                                    }
                                    if ($field_meta->type == 'wp_option') {
                                        if (property_exists($field_meta, 'delete')) {
                                            delete_option($field_meta->option);
                                        } else {
                                            update_option($field_meta->option, $data[$node_id]->settings->$field);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

    }

    static public function updated_postmeta($meta_id, $post_id, $meta_key, $meta_value) {
        if ('_thumbnail_id' === $meta_key) {
            if (get_post_meta($post_id, '_fl_builder_enabled', true)) {
                $_fl_builder_data = get_post_meta($post_id, '_fl_builder_data', true);
                $_fl_builder_draft = get_post_meta($post_id, '_fl_builder_draft', true);

                if ($_fl_builder_data) {
                    $_fl_builder_data = self::update_featured_image($_fl_builder_data, $meta_value);
                    update_post_meta($post_id, '_fl_builder_data', $_fl_builder_data);
                }
                if ($_fl_builder_draft) {
                    $_fl_builder_draft = self::update_featured_image($_fl_builder_draft, $meta_value);
                    update_post_meta($post_id, '_fl_builder_draft', $_fl_builder_draft);
                }
            }
        }
    }

    static public function update_featured_image($builder_data, $meta_value) {
        if ($builder_data) {
            foreach ($builder_data as $node_id => $object) {
                if (isset($object->settings) && isset($object->settings->zestsms_save_field_as)) {
                    foreach ($object->settings->zestsms_save_field_as as $field => $field_meta) {
                        if ($field_meta->key == '_thumbnail_id') {
                            if ($meta_value) {
                                $img_size = 'full';

                                if ($current_data = $object->settings->data) {
                                    $current_img = $object->settings->{$field . '_src'};

                                    foreach ($current_data->sizes as $size => $img_obj) {
                                        if ($current_img === $img_obj->url) {
                                            $img_size = $size;
                                        }
                                    }
                                }


                                $data = FLBuilderPhoto::get_attachment_data($meta_value);
                                $object->settings->data = $data;
                                $object->settings->{$field . '_src'} = $data->sizes->{$img_size}->url;
                            }
                        }
                    }
                }
            }

            return $builder_data;
        }

        return false;
    }

}

ZestSaveFieldAs::init();

?>
<?php
/*
Plugin Name: ZestSMS Save Field As
Plugin URI: https://zestsms.com
Description: Create post meta and wp_options using Beaver Builder
Version: 0.1
Author: ZestSMS
Author URI: https://zestsms.com
Text Domain: zestsms
License:     GPL2
 
ZestSMS Save Field As is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
ZestSMS Save Field As is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with ZestSMS Save Field As. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ZESTSMS_SAVE_FIELD_AS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ZESTSMS_SAVE_FIELD_AS_PLUGIN_URL', plugins_url( '/', __FILE__ ) );

if( class_exists( 'FLBuilder') ) {
    require_once( ZESTSMS_SAVE_FIELD_AS_PLUGIN_DIR .'classes/class-zestsms-save-field-as.php' );
}

add_action('wp_enqueue_scripts', 'zestsms_save_field_as_scripts');
function zestsms_save_field_as_scripts() {
    if(FLBuilderModel::is_builder_active()) {
        wp_enqueue_script('settings-zestsms-save-field-as', ZESTSMS_SAVE_FIELD_AS_PLUGIN_URL . 'js/settings-zestsms-save-field-as.js', array('jquery'), '0.1', true);
        wp_enqueue_style('settings-zestsms-save-field-as', ZESTSMS_SAVE_FIELD_AS_PLUGIN_URL . 'css/settings-zestsms-save-field-as.css', array(), '0.1');
    }
}
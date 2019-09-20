<?php

/*
  Plugin Name: Find and Replace All
  Description: A wordpress plugin to find and replace from all the tables and all the fields
  Version: 1.2
  Author: Taraprasad Swain
  Author URI: https://www.taraprasad.com

  Copyright 2017 by Taraprasad.com (email : swain.tara@gmail.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

add_action('after_setup_theme', 'init_fr_functions');

function init_fr_functions() {
    add_action('admin_menu', 'fr_plugin_settings');
}

function fr_plugin_settings() {
    add_menu_page('Find Replace All', 'Find Replace All', 'administrator', 'frasettings', 'fra_display_settings');
}

function fra_display_settings() {
    include('frform.php');
}

if (!function_exists('array_value_replace')) {

    function array_value_replace($maybe_array, $replace_from, $replace_to) {

        if (!empty($maybe_array)) {
            if (is_array($maybe_array)) {
                foreach ($maybe_array as $key => $value) {
                    $maybe_array[$key] = array_value_replace($value, $replace_from, $replace_to);
                }
            } else {
                if (is_string($maybe_array)) {
                    $maybe_array = str_replace($replace_from, $replace_to, $maybe_array);
                }
            }
        }

        return $maybe_array;
    }

}

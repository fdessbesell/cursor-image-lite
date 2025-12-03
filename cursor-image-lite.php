<?php
/*
Plugin Name: Cursor Image Lite
Plugin URI: https://wp.harukicore.com/cursor-image-lite
Description: A lightweight plugin that allows you to replace the default mouse cursor with a custom image across your website.
Version: 1.0.1
Author: Felipe Dessbesell
Author URI: https://wp.harukicore.com/
Contributors: dessbesell
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: cursor-image-lite
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) exit;

define('CURSIMLI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CURSIMLI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CURSIMLI_PLUGIN_FILE', __FILE__);

register_activation_hook(__FILE__, 'cursimli_activate');
function cursimli_activate(){
    update_option('cursimli_activation_time', time());
}

require_once CURSIMLI_PLUGIN_DIR . 'includes/admin.php';
require_once CURSIMLI_PLUGIN_DIR . 'includes/public.php';

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links){
    $settings_link = '<a href="options-general.php?page=cursimli-settings">' . __('Settings', 'cursor-image-lite') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});

function cursimli_enqueue_cursor_script() {
    $opts = get_option('cursimli_options', array(
        'cursor_id'=>0,
        'hover_id'=>0,
    ));

    $cursor_url = isset($opts['cursor_id']) && $opts['cursor_id'] ? wp_get_attachment_url($opts['cursor_id']) : '';
    $hover_url = isset($opts['hover_id']) && $opts['hover_id'] ? wp_get_attachment_url($opts['hover_id']) : '';

    $need_hide = false;
    foreach (array($cursor_url, $hover_url) as $u) {
        if (! $u) continue;
        $path = wp_parse_url($u, PHP_URL_PATH);
        $ext = strtolower(pathinfo($path ?: $u, PATHINFO_EXTENSION));
        if ($ext === 'png') { $need_hide = true; break; }
    }
    if (!$cursor_url && !$hover_url) return;
    if ($need_hide) {
        wp_enqueue_script(
            'cursimli_cursor_hide',
            CURSIMLI_PLUGIN_URL . 'assets/js/cursimli-cursor.js',
            array(),
            '1.0.1',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'cursimli_enqueue_cursor_script');
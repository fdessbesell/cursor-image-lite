<?php
/*
Plugin Name: Cursor Image Lite
Plugin URI: https://wp.harukicore.com/cursor-image-lite
Description: Personalize o cursor do site com imagens PNG (cursor padrão + hover). Leve, gratuito e minimalista.
Version: 1.0.1
Author: Felipe Dessbesell
Author URI: https://wp.harukicore.com/
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: cursor-image-lite
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) exit;

define('CIL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CIL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CIL_PLUGIN_FILE', __FILE__);

register_activation_hook(__FILE__, 'cil_activate');
function cil_activate(){
    update_option('cil_activation_time', time());
}

require_once CIL_PLUGIN_DIR . 'includes/admin.php';
require_once CIL_PLUGIN_DIR . 'includes/public.php';

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links){
    $settings_link = '<a href="options-general.php?page=cil-settings">' . __('Configurações', 'cursor-image-lite') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
function cil_enqueue_cursor_script() {
    $opts = get_option('cil_options', array(
        'cursor_id'=>0,
        'hover_id'=>0,
    ));
    $cursor_url = $opts['cursor_id'] ? wp_get_attachment_url($opts['cursor_id']) : '';
    $hover_url = $opts['hover_id'] ? wp_get_attachment_url($opts['hover_id']) : '';

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
            'cil-cursor-hide',
            plugin_dir_url(__FILE__) . 'assets/js/cil-cursor.js',
            array(),
            '1.0.1',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'cil_enqueue_cursor_script');
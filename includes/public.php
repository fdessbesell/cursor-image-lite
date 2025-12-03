<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('wp_enqueue_scripts', 'cursimli_public_assets', 20);

function cursimli_public_assets(){
    wp_enqueue_style('cursimli_public_css', CURSIMLI_PLUGIN_URL . 'assets/css/public.css', array(), '1.0.1');
    wp_enqueue_script('cursimli_public_js', CURSIMLI_PLUGIN_URL . 'assets/js/public.js', array('jquery'), '1.0.1', true);

    $opts = get_option('cursimli_options', array(
        'cursor_id'=>0,
        'cursor_size'=>48,
        'hover_id'=>0,
        'hover_size'=>48
    ));
    $cursor_url = $opts['cursor_id'] ? wp_get_attachment_url($opts['cursor_id']) : '';
    $hover_url = $opts['hover_id'] ? wp_get_attachment_url($opts['hover_id']) : '';
    $data = array(
        'cursor_url' => $cursor_url,
        'hover_url' => $hover_url,
        'cursor_size' => intval($opts['cursor_size']),
        'hover_size' => intval($opts['hover_size']),
    );
    wp_localize_script('cursimli_public_js', 'CURSIMLI_Settings', $data);
}
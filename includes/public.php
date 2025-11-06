<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('wp_enqueue_scripts', 'cil_public_assets', 20);
add_action('wp_head', 'cil_public_inline_css', 30);
add_action('wp_footer', 'cil_public_inline_js', 30);

function cil_public_assets(){
    wp_enqueue_style('cil-public-css', CIL_PLUGIN_URL . 'assets/css/public.css', array(), '1.0');
    wp_enqueue_script('cil-public-js', CIL_PLUGIN_URL . 'assets/js/public.js', array('jquery'), '1.0', true);

    $opts = get_option('cil_options', array(
        'cursor_id'=>0,
        'cursor_size'=>48,
        'hover_id'=>0,
        'hover_size'=>48
    ));
    $data = array(
        'cursor_url' => $opts['cursor_id'] ? wp_get_attachment_url($opts['cursor_id']) : '',
        'hover_url' => $opts['hover_id'] ? wp_get_attachment_url($opts['hover_id']) : '',
        'cursor_size' => intval($opts['cursor_size']),
        'hover_size' => intval($opts['hover_size']),
    );
    wp_localize_script('cil-public-js', 'CIL_Settings', $data);
}

function cil_public_inline_css(){
    ?>
    <style>
    .no-cursor,
    .no-cursor a,
    body.no-cursor,
    html.no-cursor {
    cursor: none !important;
}

    @media (pointer: coarse), (hover: none) {
        .cil-cursor { display: none !important; }
    }
    </style>
    <?php
}

function cil_public_inline_js(){
    // intentionally empty; main logic in assets/js/public.js
}
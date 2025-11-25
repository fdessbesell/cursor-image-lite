<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('admin_menu', 'cursimli_admin_menu');
add_action('admin_init', 'cursimli_register_settings');
add_action('admin_enqueue_scripts', 'cursimli_admin_assets');

function cursimli_admin_menu(){
    add_options_page(
        __('Cursor Image Lite', 'cursor-image-lite'),
        __('Cursor Image Lite', 'cursor-image-lite'),
        'manage_options',
        'cil-settings',
        'cursimli_settings_page'
    );
}
function cursimli_register_settings(){
    register_setting('cursimli_options_group', 'cursimli_options', 'cursimli_options_validate');
}

function cursimli_admin_assets($hook){
    if ($hook !== 'settings_page_cil-settings') return;
    wp_enqueue_style('cursimli_admin_css', CURSIMLI_PLUGIN_URL . 'assets/css/admin.css', array(), '1.0.1');
    wp_enqueue_media();
    wp_enqueue_script('cursimli_admin_js', CURSIMLI_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), '1.0.1', true);
    wp_localize_script('cursimli_admin_js', 'CIL_Admin', array(
        'dismiss_nonce' => wp_create_nonce('cursimli_dismiss_notice'),
    ));
}

function cursimli_options_validate($input){
    $prev = get_option('cursimli_options', array(
        'cursor_id'=>0,
        'cursor_size'=>48,
        'hover_id'=>0,
        'hover_size'=>48
    ));
    $out = $prev;
    $out['cursor_id'] = isset($input['cursor_id']) ? intval($input['cursor_id']) : 0;
    $out['cursor_size'] = isset($input['cursor_size']) ? intval($input['cursor_size']) : 48;
    $out['hover_id'] = isset($input['hover_id']) ? intval($input['hover_id']) : 0;
    $out['hover_size'] = isset($input['hover_size']) ? intval($input['hover_size']) : 48;

    $out['cursor_size'] = max(8, min(256, $out['cursor_size']));
    $out['hover_size'] = max(8, min(256, $out['hover_size']));

    $has_error_code = function($code){
        $errors = get_settings_errors('cursimli_options');
        if (empty($errors)) return false;
        foreach ($errors as $e) {
            if (isset($e['code']) && $e['code'] === $code) return true;
        }
        return false;
    };

    if (empty($out['cursor_id']) && empty($out['hover_id'])) {
        return $out;
    }

    if (empty($out['cursor_id']) && ! empty($out['hover_id'])) {
        if (! $has_error_code('cursimli_cursor_required_hover')) {
            add_settings_error('cursimli_options', 'cursimli_cursor_required_hover', __('A default cursor is required when a hover cursor is selected.', 'cursor-image-lite'));
        }
        return $prev;
    }

    if ($out['cursor_id']) {
        $file = get_attached_file($out['cursor_id']);
        if ($file) {
            $filetype = wp_check_filetype($file);
            $ext = isset($filetype['ext']) ? strtolower($filetype['ext']) : '';
            $size = @filesize($file);
            if ($ext !== 'png') {
                if (! $has_error_code('cursimli_cursor_type')) {
                    add_settings_error('cursimli_options', 'cursimli_cursor_type', __('The default cursor must be a PNG.', 'cursor-image-lite'));
                }
                return $prev;
            } elseif ($size !== false && $size > 200 * 1024) {
                    if (! $has_error_code('cursimli_cursor_size')) {
                    add_settings_error('cursimli_options', 'cursimli_cursor_size', __('The default cursor is too large (max 200KB).', 'cursor-image-lite'), 'error');
                }
                return $prev;
            }
        } else {
            if (! $has_error_code('cursimli_cursor_missing')) {
                add_settings_error('cursimli_options', 'cursimli_cursor_missing', __('Default cursor file not found.', 'cursor-image-lite'));
            }
            return $prev;
        }
    }

    if ($out['hover_id']) {
        $file = get_attached_file($out['hover_id']);
        if ($file) {
            $filetype = wp_check_filetype($file);
            $ext = isset($filetype['ext']) ? strtolower($filetype['ext']) : '';
            $size = @filesize($file);
            if ($ext !== 'png') {
                if (! $has_error_code('cursimli_hover_type')) {
                    add_settings_error('cursimli_options', 'cursimli_hover_type', __('The hover cursor must be a PNG.', 'cursor-image-lite'));
                }
                $out['hover_id'] = 0;
            } elseif ($size !== false && $size > 200 * 1024) {
                if (! $has_error_code('cursimli_hover_size')) {
                    add_settings_error('cursimli_options', 'cursimli_hover_size', __('The hover cursor is too large (max 200KB).', 'cursor-image-lite'), 'error');
                }
                $out['hover_id'] = 0;
            }
        } else {
            $out['hover_id'] = 0;
        }
    }
    return $out;
}

function cursimli_settings_page(){
    if ( ! current_user_can('manage_options') ) return;
    $opts = get_option('cursimli_options', array(
        'cursor_id'=>0,
        'cursor_size'=>48,
        'hover_id'=>0,
        'hover_size'=>48
    ));
    $cursor_url = $opts['cursor_id'] ? wp_get_attachment_url($opts['cursor_id']) : '';
    $hover_url = $opts['hover_id'] ? wp_get_attachment_url($opts['hover_id']) : '';
    ?>
    <div class="wrap cil-wrap">
        <h1><?php esc_html_e('Cursor Image Lite', 'cursor-image-lite'); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields('cursimli_options_group'); ?>
            <?php do_settings_sections('cil-settings'); ?>
            <?php settings_errors('cursimli_options'); ?>
            <table class="form-table cil-table">
                <tr>
                    <th><?php esc_html_e('Default cursor (PNG) — required', 'cursor-image-lite'); ?></th>
                    <td>
                        <div class="cil-preview-wrap">
                            <img id="cil_cursor_preview" src="<?php echo esc_url($cursor_url); ?>" class="cil-preview" style="<?php echo $cursor_url ? '' : 'display:none;'; ?>" />
                        </div>
                        <input type="hidden" id="cil_cursor_id" name="cursimli_options[cursor_id]" value="<?php echo esc_attr($opts['cursor_id']); ?>" />
                        <button type="button" class="button" id="cil_upload_cursor"><?php esc_html_e('Select image', 'cursor-image-lite'); ?></button>
                        <button type="button" class="button" id="cil_remove_cursor"><?php esc_html_e('Remove', 'cursor-image-lite'); ?></button>
                        <p class="description"><?php esc_html_e('We recommend lightweight images (max 200KB).', 'cursor-image-lite'); ?></p>
                        <p>
                            <label><?php esc_html_e('Size (px):', 'cursor-image-lite'); ?></label>
                            <input type="number" name="cursimli_options[cursor_size]" value="<?php echo esc_attr($opts['cursor_size']); ?>" min="8" max="256" />
                        </p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Hover cursor (PNG) — optional', 'cursor-image-lite'); ?></th>
                    <td>
                        <div class="cil-preview-wrap">
                            <img id="cil_hover_preview" src="<?php echo esc_url($hover_url); ?>" class="cil-preview" style="<?php echo $hover_url ? '' : 'display:none;'; ?>" />
                        </div>
                        <input type="hidden" id="cil_hover_id" name="cursimli_options[hover_id]" value="<?php echo esc_attr($opts['hover_id']); ?>" />
                        <button type="button" class="button" id="cil_upload_hover"><?php esc_html_e('Select image', 'cursor-image-lite'); ?></button>
                        <button type="button" class="button" id="cil_remove_hover"><?php esc_html_e('Remove', 'cursor-image-lite'); ?></button>
                        <p class="description"><?php esc_html_e('If defined, it will be used when hovering over links and buttons.', 'cursor-image-lite'); ?></p>
                        <p>
                            <label><?php esc_html_e('Hover size (px):', 'cursor-image-lite'); ?></label>
                            <input type="number" name="cursimli_options[hover_size]" value="<?php echo esc_attr($opts['hover_size']); ?>" min="8" max="256" />
                        </p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>

        <div class="cil-support">
            <p><?php esc_html_e('If this plugin has been helpful, please consider supporting it:', 'cursor-image-lite'); ?> <a href="https://buymeacoffee.com/fdessbesell" target="_blank" rel="noopener noreferrer">https://buymeacoffee.com/fdessbesell</a></p>
        </div>
    </div>

    <?php
}

add_action('admin_notices', 'cursimli_maybe_show_support_notice');
function cursimli_maybe_show_support_notice(){
    if (!current_user_can('manage_options')) return;
    $act = get_option('cursimli_activation_time', 0);
    if(!$act) return;
    $days = (time() - intval($act)) / DAY_IN_SECONDS;
    if($days >= 7 && !get_option('cursimli_support_notice_dismissed')){
        /* translators: %s is the URL to buymeacoffee */
        echo '<div class="notice notice-info is-dismissible"><p>';
        /* translators: %s contiene um link de apoio */
    printf( esc_html__( 'If you enjoy Cursor Image Lite and want to support development, please consider a contribution: %s', 'cursor-image-lite' ), '<a href="https://buymeacoffee.com/fdessbesell" target="_blank" rel="noopener noreferrer">buymeacoffee.com/fdessbesell</a>');
        echo '</p></div>';
    }
}
add_action('wp_ajax_cursimli_dismiss_support_notice', function(){
    if (! current_user_can('manage_options')) {
        wp_send_json_error('forbidden', 403);
    }
    check_ajax_referer('cursimli_dismiss_notice', 'nonce');
    update_option('cursimli_support_notice_dismissed', 1);
    wp_send_json_success();
});
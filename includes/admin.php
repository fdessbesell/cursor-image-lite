<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('admin_menu', 'cil_admin_menu');
add_action('admin_init', 'cil_register_settings');
add_action('admin_enqueue_scripts', 'cil_admin_assets');

function cil_admin_menu(){
    add_options_page(
        __('Cursor Image Lite', 'cursor-image-lite'),
        __('Cursor Image Lite', 'cursor-image-lite'),
        'manage_options',
        'cil-settings',
        'cil_settings_page'
    );
}

function cil_register_settings(){
    register_setting('cil_options_group', 'cil_options', 'cil_options_validate');
}

function cil_admin_assets($hook){
    if (strpos($hook, 'settings_page_cil-settings') === false) return;
    wp_enqueue_style('cil-admin-css', CIL_PLUGIN_URL . 'assets/css/admin.css', array(), '1.0');
    wp_enqueue_media();
    wp_enqueue_script('cil-admin-js', CIL_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), '1.0', true);
}

function cil_options_validate($input){
    $out = get_option('cil_options', array());
    $out['cursor_id'] = isset($input['cursor_id']) ? intval($input['cursor_id']) : 0;
    $out['cursor_size'] = isset($input['cursor_size']) ? intval($input['cursor_size']) : 48;
    $out['hover_id'] = isset($input['hover_id']) ? intval($input['hover_id']) : 0;
    $out['hover_size'] = isset($input['hover_size']) ? intval($input['hover_size']) : 48;

    $out['cursor_size'] = max(8, min(256, $out['cursor_size']));
    $out['hover_size'] = max(8, min(256, $out['hover_size']));

    if($out['cursor_id']){
        $file = get_attached_file($out['cursor_id']);
        if($file){
            $mime = wp_check_filetype($file)['type'];
            $size = filesize($file);
            if(strpos($mime, 'png') === false){
                add_settings_error('cil_options', 'cil_cursor_type', __('O cursor padrão deve ser PNG.', 'cursor-image-lite'));
                $out['cursor_id'] = 0;
            } elseif($size > 200 * 1024){
                add_settings_error('cil_options', 'cil_cursor_size', __('O cursor padrão é muito grande (máx 200KB).', 'cursor-image-lite'), 'error');
                $out['cursor_id'] = 0;
            }
        }
    }
    if($out['hover_id']){
        $file = get_attached_file($out['hover_id']);
        if($file){
            $mime = wp_check_filetype($file)['type'];
            $size = filesize($file);
            if(strpos($mime, 'png') === false){
                add_settings_error('cil_options', 'cil_hover_type', __('O cursor de hover deve ser PNG.', 'cursor-image-lite'));
                $out['hover_id'] = 0;
            } elseif($size > 200 * 1024){
                add_settings_error('cil_options', 'cil_hover_size', __('O cursor de hover é muito grande (máx 200KB).', 'cursor-image-lite'), 'error');
                $out['hover_id'] = 0;
            }
        }
    }
    return $out;
}

function cil_settings_page(){
    if ( ! current_user_can('manage_options') ) return;
    $opts = get_option('cil_options', array(
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
            <?php settings_fields('cil_options_group'); ?>
            <?php do_settings_sections('cil-settings'); ?>
            <table class="form-table cil-table">
                <tr>
                    <th><?php esc_html_e('Cursor padrão (PNG) — obrigatório', 'cursor-image-lite'); ?></th>
                    <td>
                        <div class="cil-preview-wrap">
                            <img id="cil_cursor_preview" src="<?php echo esc_url($cursor_url); ?>" class="cil-preview" style="<?php echo $cursor_url ? '' : 'display:none;'; ?>" />
                        </div>
                        <input type="hidden" id="cil_cursor_id" name="cil_options[cursor_id]" value="<?php echo esc_attr($opts['cursor_id']); ?>" />
                        <button class="button" id="cil_upload_cursor"><?php esc_html_e('Selecionar imagem', 'cursor-image-lite'); ?></button>
                        <button class="button" id="cil_remove_cursor"><?php esc_html_e('Remover', 'cursor-image-lite'); ?></button>
                        <p class="description"><?php esc_html_e('Somente PNG. Recomenda-se imagens leves (máx 200KB).', 'cursor-image-lite'); ?></p>
                        <p>
                            <label><?php esc_html_e('Tamanho (px):', 'cursor-image-lite'); ?></label>
                            <input type="number" name="cil_options[cursor_size]" value="<?php echo esc_attr($opts['cursor_size']); ?>" min="8" max="256" />
                        </p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Cursor hover (PNG) — opcional', 'cursor-image-lite'); ?></th>
                    <td>
                        <div class="cil-preview-wrap">
                            <img id="cil_hover_preview" src="<?php echo esc_url($hover_url); ?>" class="cil-preview" style="<?php echo $hover_url ? '' : 'display:none;'; ?>" />
                        </div>
                        <input type="hidden" id="cil_hover_id" name="cil_options[hover_id]" value="<?php echo esc_attr($opts['hover_id']); ?>" />
                        <button class="button" id="cil_upload_hover"><?php esc_html_e('Selecionar imagem', 'cursor-image-lite'); ?></button>
                        <button class="button" id="cil_remove_hover"><?php esc_html_e('Remover', 'cursor-image-lite'); ?></button>
                        <p class="description"><?php esc_html_e('Se definido, será usado quando passar o cursor sobre links e botões.', 'cursor-image-lite'); ?></p>
                        <p>
                            <label><?php esc_html_e('Tamanho do hover (px):', 'cursor-image-lite'); ?></label>
                            <input type="number" name="cil_options[hover_size]" value="<?php echo esc_attr($opts['hover_size']); ?>" min="8" max="256" />
                        </p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>

        <div class="cil-support">
            <p><?php esc_html_e('Se este plugin ajudou, considere apoiar:', 'cursor-image-lite'); ?> <a href="https://buymeacoffee.com/fdessbesell" target="_blank" rel="noopener noreferrer">https://buymeacoffee.com/fdessbesell</a></p>
        </div>
    </div>

    <?php
}

add_action('admin_notices', 'cil_maybe_show_support_notice');
function cil_maybe_show_support_notice(){
    if (!current_user_can('manage_options')) return;
    $act = get_option('cil_activation_time', 0);
    if(!$act) return;
    $days = (time() - intval($act)) / DAY_IN_SECONDS;
    if($days >= 7 && !get_option('cil_support_notice_dismissed')){
        /* translators: %s is the URL to buymeacoffee */
        echo '<div class="notice notice-info is-dismissible"><p>';
        /* translators: %s contiene um link de apoio */
        printf( esc_html__( 'Se você está gostando do Cursor Image Lite e quer apoiar o desenvolvimento, considere uma contribuição: %s', 'cursor-image-lite' ), '<a href="https://buymeacoffee.com/fdessbesell" target="_blank" rel="noopener noreferrer">buymeacoffee.com/fdessbesell</a>');
        echo '</p></div>';
    }
}
add_action('wp_ajax_cil_dismiss_support_notice', function(){
    update_option('cil_support_notice_dismissed', 1);
    wp_send_json_success();
});
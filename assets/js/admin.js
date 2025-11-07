jQuery(document).ready(function($){
    function mediaSelect(btn, previewSelector, hiddenInputSelector){
        var frame;
        $(document).on('click', btn, function(e){
            e.preventDefault();
            if(frame) frame.open();
            frame = wp.media({
                title: 'Selecionar ou enviar imagem',
                button: { text: 'Usar imagem' },
                multiple: false
            });
            frame.on('select', function(){
                var attachment = frame.state().get('selection').first().toJSON();
                $(previewSelector).attr('src', attachment.url).show();
                $(hiddenInputSelector).val(attachment.id);
            });
            frame.open();
        });
    }
    mediaSelect('#cil_upload_cursor', '#cil_cursor_preview', '#cil_cursor_id');
    mediaSelect('#cil_upload_hover', '#cil_hover_preview', '#cil_hover_id');

    $('#cil_remove_cursor').on('click', function(e){ e.preventDefault(); $('#cil_cursor_preview').hide().attr('src',''); $('#cil_cursor_id').val(''); });
    $('#cil_remove_hover').on('click', function(e){ e.preventDefault(); $('#cil_hover_preview').hide().attr('src',''); $('#cil_hover_id').val(''); });

    $(document).on('click', '.notice.is-dismissible', function(){
        var data = {
            action: 'cil_dismiss_support_notice',
            nonce: (typeof CIL_Admin !== 'undefined' && CIL_Admin.dismiss_nonce) ? CIL_Admin.dismiss_nonce : ''
        };
        $.post(ajaxurl, data);
    });
});
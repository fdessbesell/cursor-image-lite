jQuery(document).ready(function($){
    function mediaSelect(btn, previewSelector, hiddenInputSelector){
        var frame;
        $(document).on('click', btn, function(e){
            e.preventDefault();
            if(frame) frame.open();
            frame = wp.media({
                title: 'Select or upload image',
                button: { text: 'Use image' },
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
    mediaSelect('#cursimli_upload_cursor', '#cursimli_cursor_preview', '#cursimli_cursor_id');
    mediaSelect('#cursimli_upload_hover', '#cursimli_hover_preview', '#cursimli_hover_id');

    $('#cursimli_remove_cursor').on('click', function(e){ e.preventDefault(); $('#cursimli_cursor_preview').hide().attr('src',''); $('#cursimli_cursor_id').val(''); });
    $('#cursimli_remove_hover').on('click', function(e){ e.preventDefault(); $('#cursimli_hover_preview').hide().attr('src',''); $('#cursimli_hover_id').val(''); });

    $(document).on('click', '.notice.is-dismissible', function(){
        var data = {
            action: 'cursimli_dismiss_support_notice',
            nonce: (typeof CURSIMLI_Admin !== 'undefined' && CURSIMLI_Admin.dismiss_nonce) ? CURSIMLI_Admin.dismiss_nonce : ''
        };
        $.post(ajaxurl, data);
    });
});
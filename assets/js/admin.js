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

    function dismissSupportNotice(actionType){
        var nonce = $('#cursimli-support-notice').find('[data-nonce]').first().data('nonce');
        
        if (!nonce) {
            console.error('Nonce not found');
            return false;
        }
        
        var data = {
            action: 'cursimli_dismiss_support_notice',
            nonce: nonce,
            action_type: actionType
        };
        
        $.ajax({
            url: ajaxurl || '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: data,
            dataType: 'json',
            beforeSend: function(){
                $('#cursimli-support-notice').css('opacity', '0.5').css('pointer-events', 'none');
            },
            success: function(response){
                if(response.success){
                    $('#cursimli-support-notice').fadeOut(300, function(){
                        $(this).remove();
                    });
                } else {
                    console.error('AJAX Error:', response.data);
                    $('#cursimli-support-notice').css('opacity', '1').css('pointer-events', 'auto');
                }
            },
            error: function(xhr, status, error){
                console.error('AJAX Error:', error);
                $('#cursimli-support-notice').css('opacity', '1').css('pointer-events', 'auto');
            }
        });
        
        return false;
    }
    
    $(document).on('click', '#cursimli-dismiss-permanent', function(e){
        e.preventDefault();
        dismissSupportNotice('permanent');
    });
    
    $(document).on('click', '#cursimli-dismiss-remind', function(e){
        e.preventDefault();
        dismissSupportNotice('remind_later');
    });
});
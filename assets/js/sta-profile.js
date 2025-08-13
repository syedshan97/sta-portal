(function($){
  $(function(){
    var frame;
    $('#sta-change-avatar').on('click', function(e){
      e.preventDefault();
      if (frame) { frame.open(); return; }
      frame = wp.media({
        title: 'Choose Profile Photo',
        button: { text: 'Use this photo' },
        multiple: false,
        library: { type: 'image' }
      });
      frame.on('select', function(){
        var attachment = frame.state().get('selection').first().toJSON();
        $.post(staProfile.ajaxurl, {
          action: 'sta_portal_save_avatar',
          attachment_id: attachment.id,
          nonce: staProfile.nonce
        }).done(function(resp){
          if(resp && resp.success && resp.data && resp.data.url){
            $('#sta-avatar-preview').attr('src', resp.data.url);
          } else {
            alert((resp && resp.data) ? resp.data : 'Failed to save avatar.');
          }
        }).fail(function(){
          alert('Upload failed. Please try again.');
        });
      });
      frame.open();
    });
  });
})(jQuery);

(function($){
  var downloadFrame, galleryFrame;

  function ph_localized(key, fallback) {
    try {
      if (typeof window !== 'undefined' && window.PluginHub && window.PluginHub.i18n && window.PluginHub.i18n[key]) {
        return window.PluginHub.i18n[key];
      }
    } catch (e) {}
    return fallback;
  }

  $(document).ready(function(){
    if ( typeof wp === 'undefined' || typeof wp.media === 'undefined' ) {
      return;
    }

    function ensureUploadTarget(frame) {
      try {
        if ( frame && frame.uploader && frame.uploader.uploader && frame.uploader.uploader.settings ) {
          frame.uploader.uploader.settings.multipart_params = frame.uploader.uploader.settings.multipart_params || {};
          frame.uploader.uploader.settings.multipart_params.ph_upload_target = 'plugins-download';
        }
        if ( frame && frame.uploader && frame.uploader.options ) {
          frame.uploader.options.multipart_params = frame.uploader.options.multipart_params || {};
          frame.uploader.options.multipart_params.ph_upload_target = 'plugins-download';
        }
      } catch (e) {}
    }

    $('#ph-upload-download-btn').on('click', function(e){
      e.preventDefault();

      if ( downloadFrame ) {
        ensureUploadTarget(downloadFrame);
        downloadFrame.open();
        return;
      }

      var title = ph_localized('select_file', 'Select or upload file');

      downloadFrame = wp.media({
        title: title,
        button: { text: ph_localized('use_file', 'Use this file') },
        multiple: false
      });

      downloadFrame.on('open', function() {
        ensureUploadTarget(downloadFrame);
      });

      downloadFrame.on('select', function() {
        var attachment = downloadFrame.state().get('selection').first().toJSON();
        if ( attachment && attachment.id ) {
          // Ask server to move file (AJAX)
          var data = {
            action: 'ph_move_attachment_to_plugins_download',
            attachment_id: attachment.id,
            _ajax_nonce: (window.PluginHub && window.PluginHub.nonce) ? window.PluginHub.nonce : ''
          };

          $('#ph-download-filename').text(ph_localized('moving', 'Moving...'));

          $.post(window.PluginHub.ajax_url, data, function(resp){
            if ( resp && resp.success && resp.data && resp.data.url ) {
              $('#ph-download-field').val(resp.data.url).trigger('change');
              $('#ph-download-filename').text(resp.data.basename || resp.data.url);
            } else {
              var url = (attachment.url) ? attachment.url : '';
              $('#ph-download-field').val(url).trigger('change');
              var filename = attachment.filename || attachment.title || url;
              $('#ph-download-filename').text(filename);
              console.error('Move failed', resp);
            }
          }, 'json').fail(function(xhr){
            var url = (attachment.url) ? attachment.url : '';
            $('#ph-download-field').val(url).trigger('change');
            $('#ph-download-filename').text(attachment.filename || attachment.title || url);
            console.error('AJAX error', xhr);
          });
        }
      });

      downloadFrame.open();
    });

    // remove, gallery code unchanged (keep previous handlers)
    $('#ph-remove-download-btn').on('click', function(e){
      e.preventDefault();
      $('#ph-download-field').val('').trigger('change');
      var nofile = $('#ph-download-filename').attr('data-no-file') || ph_localized('no_file', 'No file selected');
      $('#ph-download-filename').text(nofile);
    });

    $('#ph-upload-gallery-btn').on('click', function(e){
      e.preventDefault();

      if ( galleryFrame ) {
        ensureUploadTarget(galleryFrame);
        galleryFrame.open();
        return;
      }

      galleryFrame = wp.media({
        title: ph_localized('select_screens', 'Select Screenshots'),
        button: { text: ph_localized('add_to_gallery', 'Add to gallery') },
        library: { type: 'image' },
        multiple: true
      });

      galleryFrame.on('open', function() {
        ensureUploadTarget(galleryFrame);
      });

      galleryFrame.on('select', function() {
        var selection = galleryFrame.state().get('selection').toJSON();
        var urls = [];
        $('#ph-screens-preview').empty();
        selection.forEach(function(att){
          if ( att && att.url ) {
            urls.push(att.url);
            var $thumb = $('<div class="ph-screen-thumb" />').attr('data-url', att.url);
            var $img = $('<img />').attr('src', att.url);
            var $btn = $('<button type="button" class="button ph-remove-screen-btn" />').html('&times;');
            $thumb.append($img).append($btn);
            $('#ph-screens-preview').append($thumb);
          }
        });

        $('#ph-screens-field').val(urls.join(',')).trigger('change');
      });

      galleryFrame.open();
    });

    $(document).on('click', '.ph-remove-screen-btn', function(e){
      e.preventDefault();
      var $wrap = $(this).closest('.ph-screen-thumb');
      var url = $wrap.attr('data-url');
      $wrap.remove();
      var cur = $('#ph-screens-field').val();
      var parts = cur ? cur.split(',') : [];
      parts = parts.map(function(p){ return p.trim(); }).filter(function(p){ return p && p !== url; });
      $('#ph-screens-field').val(parts.join(',')).trigger('change');
    });

    $('#ph-clear-gallery-btn').on('click', function(e){
      e.preventDefault();
      $('#ph-screens-field').val('').trigger('change');
      $('#ph-screens-preview').empty();
    });

  });
})(jQuery);
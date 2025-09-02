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

document.addEventListener('DOMContentLoaded', function () {
  const card      = document.getElementById('sta-avatar-card');
  if (!card) return;

  const preview       = document.getElementById('sta-avatar-preview');
  const hiddenTrigger = document.getElementById('sta-change-avatar');
  const placeholder   = card.getAttribute('data-placeholder') || '';

  const modal   = document.getElementById('sta-avatar-modal');
  const btnChg  = document.getElementById('sam-change');
  const btnRm   = document.getElementById('sam-remove');
  const toClose = modal ? modal.querySelectorAll('[data-close]') : [];

  // Ensure placeholder if no photo
  if (card.getAttribute('data-has') === '0' && placeholder && !preview.getAttribute('src')) {
    preview.setAttribute('src', placeholder);
  }

  // Open picker
  function openPicker() {
    if (hiddenTrigger) hiddenTrigger.click();
  }

  // Modal control
  function openModal() {
    if (!modal) return;
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    // focus first button
    setTimeout(() => { btnChg && btnChg.focus(); }, 10);
  }
  function closeModal() {
    if (!modal) return;
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
  }

  // Click avatar:
  // - if empty -> upload
  // - if has photo -> modal
  card.addEventListener('click', function (e) {
    e.preventDefault();
    if (card.getAttribute('data-has') === '0') {
      openPicker();
    } else {
      openModal();
    }
  });

  // ESC and backdrop close
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeModal();
  });
  toClose.forEach(el => el.addEventListener('click', closeModal, { capture: true }));

  // Change photo -> open picker
  btnChg && btnChg.addEventListener('click', function (e) {
    e.preventDefault();
    closeModal();
    openPicker();
  });

  // When media flow updates <img src>, mark as "has"
  const mo = new MutationObserver(() => {
    const src = preview.getAttribute('src') || '';
    if (src && (!placeholder || src !== placeholder)) {
      card.setAttribute('data-has', '1');
    }
  });
  mo.observe(preview, { attributes: true, attributeFilter: ['src'] });

  // Remove photo via AJAX (immediate)
  btnRm && btnRm.addEventListener('click', function (e) {
    e.preventDefault();
    const cfg = window.STA_PORTAL_AJAX || {};
    if (!cfg.url || !cfg.nonce) { alert('Cannot remove photo: missing AJAX config.'); return; }
    if (!confirm('Remove your profile photo?')) return;

    fetch(cfg.url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: new URLSearchParams({ action: 'sta_remove_avatar', nonce: cfg.nonce })
    })
    .then(r => r.json())
    .then(res => {
      if (res && res.success) {
        // UI update
        if (placeholder) preview.setAttribute('src', placeholder);
        card.setAttribute('data-has', '0');
        closeModal();
      } else {
        const msg = (res && res.data && res.data.message) ? res.data.message : 'Failed to remove photo.';
        alert(msg);
      }
    })
    .catch(() => alert('Network error while removing photo.'));
  });
});

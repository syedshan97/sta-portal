(function(){
  function closeAll(){
    document.querySelectorAll('[data-sta-user-menu].sta-open').forEach(function(wrap){
      wrap.classList.remove('sta-open');
      var btn = wrap.querySelector('.sum-trigger');
      if (btn) btn.setAttribute('aria-expanded','false');
    });
  }
  document.addEventListener('click', function(e){
    var trigger = e.target.closest('.sum-trigger');
    var wrap = e.target.closest('[data-sta-user-menu]');
    if (trigger && wrap){
      var isOpen = wrap.classList.contains('sta-open');
      closeAll();
      wrap.classList.toggle('sta-open', !isOpen);
      trigger.setAttribute('aria-expanded', (!isOpen).toString());
      return;
    }
    if (!wrap){ closeAll(); }
  });
  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape'){ closeAll(); }
  });
})();

// Signup Form Validation Code
(function () {
  // Only run on the Signup page (form present)
  var form = document.getElementById('sta-signup-form');
  if (!form) return;

  var nameInput = document.getElementById('sta-signup-name');
  var emailInput = document.getElementById('sta-signup-email');
  var passInput  = document.getElementById('sta-signup-password');

  if (!nameInput || !emailInput || !passInput) return;

  // Create or fetch a <small> message element just after an input
  function ensureMsgEl(input, id) {
    var el = document.getElementById(id);
    if (el) return el;
    el = document.createElement('small');
    el.id = id;
    el.className = 'sta-field-msg';
    input.insertAdjacentElement('afterend', el);
    return el;
  }

  var nameMsg = ensureMsgEl(nameInput,  'msg-name');
  var emailMsg= ensureMsgEl(emailInput, 'msg-email');
  var passMsg = ensureMsgEl(passInput,  'msg-password');

  function setErr(input, msgEl, msg) {
    input.classList.add('is-error');
    input.classList.remove('is-valid');
    if (msgEl) msgEl.textContent = msg || '';
    var wrap = input.closest('.sta-field');
    if (wrap) { wrap.classList.add('has-error'); wrap.classList.remove('has-valid'); }
  }
  function setOk(input, msgEl, msg) {
    input.classList.remove('is-error');
    input.classList.add('is-valid');
    if (msgEl) msgEl.textContent = msg || '';
    var wrap = input.closest('.sta-field');
    if (wrap) { wrap.classList.remove('has-error'); wrap.classList.add('has-valid'); }
  }
  function clearState(input, msgEl) {
    input.classList.remove('is-error','is-valid');
    if (msgEl) msgEl.textContent = '';
    var wrap = input.closest('.sta-field');
    if (wrap) { wrap.classList.remove('has-error','has-valid'); }
  }

  // Simple validators
  function validateName() {
    var v = (nameInput.value || '').trim();
    if (!v) { setErr(nameInput, nameMsg, 'Name is required.'); return false; }
    if (!/^[A-Za-z ]+$/.test(v)) { setErr(nameInput, nameMsg, 'Use English letters and spaces only.'); return false; }
    setOk(nameInput, nameMsg, '');
    return true;
  }

  function validateEmail() {
    var v = (emailInput.value || '').trim();
    if (!v) { setErr(emailInput, emailMsg, 'Email is required.'); return false; }
    // Simple client-side pattern; server re-validates
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) {
      setErr(emailInput, emailMsg, 'Enter a valid email (e.g., name@example.com).'); return false;
    }
    setOk(emailInput, emailMsg, '');
    return true;
  }

  function validatePassword(live) {
    var v = passInput.value || '';
    var okLen   = v.length >= 8;
    var okAlpha = /[A-Za-z]/.test(v);
    var okDigit = /\d/.test(v);
    var okSym   = /[^A-Za-z0-9]/.test(v);
    var ok = okLen && okAlpha && okDigit && okSym;

    if (ok) {
  setOk(passInput, passMsg, 'Looks good.');
  passMsg.classList.add('is-ok');      // <-- force green style on the message
} else {
  passInput.classList.remove('is-valid');
  passMsg.classList.remove('is-ok');   // <-- remove green when invalid
  if (live){
    passMsg.textContent = 'Password must be at least 8 characters and include a letter, a number, and a symbol.';
    var wrap = passInput.closest('.sta-field');
    if (wrap) wrap.classList.remove('has-error','has-valid');
  } else {
    setErr(passInput, passMsg, 'Password must be at least 8 characters and include a letter, a number, and a symbol.');
  }
}
    return ok;
  }

  // Live validation
  nameInput.addEventListener('input', validateName);
  emailInput.addEventListener('input', validateEmail);
  passInput .addEventListener('input', function(){ validatePassword(true); });

  // On submit
  form.addEventListener('submit', function (e) {
    var ok1 = validateName();
    var ok2 = validateEmail();
    var ok3 = validatePassword(false);
    if (!(ok1 && ok2 && ok3)) {
      e.preventDefault();
      e.stopPropagation();
      // focus first error
      if (!ok1) { nameInput.focus(); return; }
      if (!ok2) { emailInput.focus(); return; }
      if (!ok3) { passInput.focus(); return; }
    }
  });

})();

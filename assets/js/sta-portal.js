// assets/js/sta-portal.js
// Simple client-side validation for the STA signup form
// This is a basic validation script for the STA portal signup form.
// It checks first name, last name, email, and password fields for validity.
// Messages are displayed inline next to each field.
(function () {
  // Only run on the Signup page (form present)
  var form = document.getElementById('sta-signup-form');
  if (!form) return;

  var firstInput = document.getElementById('sta-signup-first');
  var lastInput  = document.getElementById('sta-signup-last');
  var emailInput = document.getElementById('sta-signup-email');
  var passInput  = document.getElementById('sta-signup-password');

  if (!firstInput || !lastInput || !emailInput || !passInput) return;

  // Message elements (already in template)
  var firstMsg = document.getElementById('msg-first');
  var lastMsg  = document.getElementById('msg-last');
  var emailMsg = document.getElementById('msg-email');
  var passMsg  = document.getElementById('msg-password');

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

  // Validators
  function validateFirst() {
    var v = (firstInput.value || '').trim();
    if (!v) { setErr(firstInput, firstMsg, 'First name is required.'); return false; }
    if (!/^[A-Za-z]+(?:[ '\-][A-Za-z]+)*$/.test(v)) { setErr(firstInput, firstMsg, 'Use English letters, spaces & hyphens/apostrophes only.'); return false; }
    setOk(firstInput, firstMsg, '');
    return true;
  }

  function validateLast() {
    var v = (lastInput.value || '').trim();
    if (!v) { setErr(lastInput, lastMsg, 'Last name is required.'); return false; }
    if (!/^[A-Za-z]+(?:[ '\-][A-Za-z]+)*$/.test(v)) { setErr(lastInput, lastMsg, 'Use English letters, spaces & hyphens/apostrophes only.'); return false; }
    setOk(lastInput, lastMsg, '');
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

  // Rules
  var okLen   = v.length >= 8;
  var okUpper = /[A-Z]/.test(v);
  var okLower = /[a-z]/.test(v);
  var okDigit = /\d/.test(v);
  var okSym   = /[^A-Za-z0-9]/.test(v) && !/[<>]/.test(v); // exclude < >

  var allOk = okLen && okUpper && okLower && okDigit && okSym;

  // Update the checklist safely
  var wrap = document.getElementById('msg-password');
  if (wrap) {
    var rules = {
      len:   okLen,
      upper: okUpper,
      lower: okLower,
      num:   okDigit,
      sym:   okSym
    };
    Object.keys(rules).forEach(function(key){
      var item = wrap.querySelector('[data-rule="' + key + '"]');
      if (item) {
        item.classList.toggle('pass', !!rules[key]);
        item.setAttribute('aria-checked', rules[key] ? 'true' : 'false');
      }
    });
    wrap.classList.toggle('is-ok', allOk);
  }

  // Keep previous field-level styling
  passInput.classList.toggle('is-valid', allOk);
  var field = passInput.closest('.sta-field');
  if (field) {
    field.classList.toggle('has-valid', allOk);
    if (!allOk && !live) field.classList.add('has-error'); else field.classList.remove('has-error');
  }

  return allOk;
}

  // Live validation
  firstInput.addEventListener('input', validateFirst);
  lastInput .addEventListener('input', validateLast);
  emailInput.addEventListener('input', validateEmail);
  passInput .addEventListener('input', function(){ validatePassword(true); });

  // On submit
  form.addEventListener('submit', function (e) {
    var ok1 = validateFirst();
    var ok2 = validateLast();
    var ok3 = validateEmail();
    var ok4 = validatePassword(false);
    if (!(ok1 && ok2 && ok3 && ok4)) {
      e.preventDefault();
      e.stopPropagation();
      // focus first error
      if (!ok1) { firstInput.focus(); return; }
      if (!ok2) { lastInput.focus(); return; }
      if (!ok3) { emailInput.focus(); return; }
      if (!ok4) { passInput.focus(); return; }
    }
  });
})();

// ==== RESET PASSWORD PAGE ====
(function(){
  var form = document.getElementById('sta-reset-form');
  if (!form) return;

  var p1 = document.getElementById('sta-reset-pass1');
  var p2 = document.getElementById('sta-reset-pass2');
  var confirmMsg = document.getElementById('msg-reset-confirm');

  function validatePasswordGeneric(inputEl, wrapId, live){
    var v = (inputEl.value || '');
    var okLen   = v.length >= 8;
    var okUpper = /[A-Z]/.test(v);
    var okLower = /[a-z]/.test(v);
    var okDigit = /\d/.test(v);
    var okSym   = /[^A-Za-z0-9]/.test(v) && !/[<>]/.test(v);
    var allOk   = okLen && okUpper && okLower && okDigit && okSym;

    var wrap = document.getElementById(wrapId);
    if (wrap){
      var rules = { len:okLen, upper:okUpper, lower:okLower, num:okDigit, sym:okSym };
      Object.keys(rules).forEach(function(key){
        var li = wrap.querySelector('[data-rule="'+key+'"]');
        if (li){ li.classList.toggle('pass', !!rules[key]); }
      });
      wrap.classList.toggle('is-ok', allOk);
    }

    inputEl.classList.toggle('is-valid', allOk);
    var field = inputEl.closest('.sta-field');
    if (field){
      field.classList.toggle('has-valid', allOk);
      if (!allOk && !live) field.classList.add('has-error'); else field.classList.remove('has-error');
    }
    return allOk;
  }

  function validateP1(live){ return validatePasswordGeneric(p1, 'msg-password-reset', live); }

function validateP2() {
  var v1 = (p1.value || '');
  var v2 = (p2.value || '');
  var same = v2 !== '' && v2 === v1;

  // message element under confirm field
  if (confirmMsg) {
    if (same) {
       confirmMsg.textContent = 'Password matched.';
       confirmMsg.classList.add('ok');
    } else {
       confirmMsg.textContent = v2 ? 'Passwords do not match.' : '';
       confirmMsg.classList.remove('ok');
    }
  }

  // field/input styling
  var field = p2.closest('.sta-field');
  if (same) {
    p2.classList.add('is-valid');
    p2.classList.remove('is-error');
    if (field) { field.classList.add('has-valid'); field.classList.remove('has-error'); }
  } else {
    p2.classList.remove('is-valid');
    if (field) {
      if (v2) { field.classList.add('has-error'); }
      else { field.classList.remove('has-error'); }
      field.classList.remove('has-valid');
    }
  }

  return same;
}


  // live events
  p1.addEventListener('input', function(){ validateP1(true); validateP2(); });
  p2.addEventListener('input', validateP2);

  // init once (in case of autofill)
  document.addEventListener('DOMContentLoaded', function(){
    validateP1(true);
    validateP2();
  });

  // submit guard
  form.addEventListener('submit', function(e){
    var ok1 = validateP1(false);
    var ok2 = validateP2();
    if (!(ok1 && ok2)){
      e.preventDefault(); e.stopPropagation();
      if (!ok1) { p1.focus(); } else { p2.focus(); }
    }
  });
})();



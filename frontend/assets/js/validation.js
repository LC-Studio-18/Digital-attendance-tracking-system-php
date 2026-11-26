export function validateEmail(emailValue) {
  const trimmed = (emailValue || '').trim();
  if (!trimmed) {
    return 'Please enter your email address.';
  }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(trimmed)) {
    return 'Please enter a valid email address.';
  }
  return '';
}

export function validatePassword(passwordValue) {
  const value = passwordValue || '';
  if (!value.trim()) {
    return 'Please enter your password.';
  }
  return '';
}

export function validateLoginForm(emailValue, passwordValue) {
  const errors = {
    email: validateEmail(emailValue),
    password: validatePassword(passwordValue),
  };

  return {
    isValid: !errors.email && !errors.password,
    errors,
  };
}

export function setFieldError(inputEl, errorEl, message) {
  if (!inputEl || !errorEl) return;

  if (message) {
    inputEl.classList.add('is-invalid');
    inputEl.setAttribute('aria-invalid', 'true');
    errorEl.textContent = message;
  } else {
    inputEl.classList.remove('is-invalid');
    inputEl.removeAttribute('aria-invalid');
    errorEl.textContent = '';
  }
}
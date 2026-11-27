import {
  validateEmail,
  validatePassword,
  validateLoginForm,
  setFieldError,
} from './validation.js';

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('loginForm');
  const emailInput = document.getElementById('email');
  const passwordInput = document.getElementById('password');
  const emailError = document.getElementById('emailError');
  const passwordError = document.getElementById('passwordError');
  const submitBtn = document.getElementById('loginSubmitBtn');
  const togglePasswordBtn = document.getElementById('togglePasswordBtn');
  const offlineBanner = document.getElementById('offlineBanner');

  if (!form || !emailInput || !passwordInput) return;

  const syncNetworkState = () => {
    if (!offlineBanner) return;
    offlineBanner.hidden = navigator.onLine;
  };

  syncNetworkState();
  window.addEventListener('online', syncNetworkState);
  window.addEventListener('offline', syncNetworkState);

  if (togglePasswordBtn) {
    togglePasswordBtn.addEventListener('click', () => {
      const isHidden = passwordInput.type === 'password';
      passwordInput.type = isHidden ? 'text' : 'password';
      togglePasswordBtn.setAttribute(
        'aria-label',
        isHidden ? 'Hide password' : 'Show password'
      );
      togglePasswordBtn.setAttribute('aria-pressed', String(isHidden));
    });
  }

  emailInput.addEventListener('blur', () => {
    setFieldError(emailInput, emailError, validateEmail(emailInput.value));
  });

  emailInput.addEventListener('input', () => {
    if (emailInput.classList.contains('is-invalid')) {
      setFieldError(emailInput, emailError, validateEmail(emailInput.value));
    }
  });

  passwordInput.addEventListener('blur', () => {
    setFieldError(
      passwordInput,
      passwordError,
      validatePassword(passwordInput.value)
    );
  });

  passwordInput.addEventListener('input', () => {
    if (passwordInput.classList.contains('is-invalid')) {
      setFieldError(
        passwordInput,
        passwordError,
        validatePassword(passwordInput.value)
      );
    }
  });

  form.addEventListener('submit', (event) => {
    if (!navigator.onLine) {
      event.preventDefault();
      syncNetworkState();
      return;
    }

    const { isValid, errors } = validateLoginForm(
      emailInput.value,
      passwordInput.value
    );

    setFieldError(emailInput, emailError, errors.email);
    setFieldError(passwordInput, passwordError, errors.password);

    if (!isValid) {
      event.preventDefault();
      if (errors.email) {
        emailInput.focus();
      } else {
        passwordInput.focus();
      }
      return;
    }

    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Signing in...';
    }
  });
});
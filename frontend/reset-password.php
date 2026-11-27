<?php
declare(strict_types=1);

$email = trim((string)($_GET['email'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create New Password — LC Attends</title>
  <link rel="stylesheet" href="./assets/css/layout.css">
  <style>
    *, *::before, *::after {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      background-color: #0e181f;
      color: #ffffff;
      font-family: "TASA Orbiter", sans-serif;
    }

    .recovery-topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 20px 32px;
      font-size: 12px;
    }

    .recovery-topbar__brand {
      font-weight: 700;
      color: #ffffff;
    }

    .recovery-topbar__portal {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      color: #9fb0b8;
      font-weight: 500;
    }

    .recovery-topbar__dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background-color: #b2ed1d;
    }

    .recovery-main {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 16px;
    }

    .recovery-logo {
      height: 44px;
      width: auto;
      margin-bottom: 12px;
      object-fit: contain;
    }

    .recovery-kicker {
      margin: 0 0 18px;
      color: #7d9aa3;
      font-size: 13px;
      font-weight: 700;
      letter-spacing: 0.14em;
      text-transform: uppercase;
    }

    .recovery-card {
      position: relative;
      width: 100%;
      max-width: 400px;
      background-color: #ffffff;
      color: #0e181f;
      border-radius: 10px;
      padding: 32px 28px;
      box-shadow: 0 16px 36px rgba(0, 0, 0, 0.35);
    }

    .recovery-card::before,
    .recovery-card::after {
      content: "";
      position: absolute;
      width: 72px;
      height: 3px;
      background-color: #b2ed1d;
      border-radius: 2px;
    }

    .recovery-card::before {
      top: 10px;
      left: 16px;
    }

    .recovery-card::after {
      bottom: 10px;
      right: 16px;
    }

    .recovery-card__title {
      margin: 0 0 8px;
      text-align: center;
      font-size: 20px;
      font-weight: 700;
      color: #0e181f;
    }

    .recovery-card__subtitle {
      margin: 0 0 20px;
      text-align: center;
      font-size: 11.5px;
      line-height: 1.45;
      color: #5b6c74;
    }

    .form-group {
      margin-bottom: 14px;
    }

    .recovery-label {
      display: block;
      margin-bottom: 6px;
      font-size: 11.5px;
      font-weight: 600;
      color: #0e181f;
    }

    .recovery-input {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #cfd8dc;
      border-radius: 6px;
      font-family: "TASA Orbiter", sans-serif;
      font-size: 13px;
      color: #0e181f;
      background-color: #ffffff;
    }

    .recovery-input:focus {
      outline: 2px solid #2f6b75;
      border-color: #2f6b75;
    }

    .recovery-input.is-invalid {
      border-color: #d93025;
    }

    .recovery-hint {
      margin: 5px 0 0;
      color: #5b6c74;
      font-size: 10.5px;
      line-height: 1.35;
    }

    .recovery-error {
      display: none;
      align-items: center;
      gap: 5px;
      margin: 10px 0 0;
      color: #d93025;
      font-size: 11px;
      font-weight: 500;
    }

    .recovery-error.is-visible {
      display: flex;
    }

    .recovery-btn {
      display: block;
      width: 190px;
      margin: 20px auto 14px;
      padding: 10px 16px;
      border: 0;
      border-radius: 6px;
      background-color: #b2ed1d;
      color: #0e181f;
      font-family: "TASA Orbiter", sans-serif;
      font-size: 12px;
      font-weight: 700;
      cursor: pointer;
      text-align: center;
    }

    .recovery-btn:hover {
      background-color: #c2f244;
    }

    .recovery-back {
      display: block;
      text-align: center;
      color: #2f6b75;
      font-size: 11.5px;
      font-weight: 600;
      text-decoration: none;
    }

    .recovery-back:hover {
      text-decoration: underline;
    }

    .recovery-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 18px 32px;
      font-size: 10px;
      font-weight: 600;
      letter-spacing: 0.06em;
      color: #8fa1aa;
      text-transform: uppercase;
    }

    @media (max-width: 480px) {
      .recovery-topbar,
      .recovery-footer {
        padding: 16px;
      }

      .recovery-card {
        padding: 28px 20px;
      }
    }
  </style>
</head>
<body>
  <header class="recovery-topbar">
    <span class="recovery-topbar__brand">LC Attends</span>
    <span class="recovery-topbar__portal">
      <span class="recovery-topbar__dot" aria-hidden="true"></span>
      Staff Attendance Portal
    </span>
  </header>

  <main class="recovery-main">
    <img src="./assets/images/LCLogo.svg" alt="Life Choices" class="recovery-logo">
    <p class="recovery-kicker">ACCOUNT RECOVERY</p>

    <section class="recovery-card">
      <h1 class="recovery-card__title">Create New Password</h1>
      <p class="recovery-card__subtitle">
        Enter your registered email address and choose a strong new password.
      </p>

      <form id="resetPasswordForm" action="../backend/reset-password.php" method="POST" novalidate>
        <div class="form-group">
          <label for="reset_email" class="recovery-label">Email address</label>
          <input
            type="email"
            id="reset_email"
            name="email"
            class="recovery-input"
            placeholder="name@example.com"
            value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
            required
            autocomplete="email"
          >
        </div>

        <div class="form-group">
          <label for="new_password" class="recovery-label">New password</label>
          <input
            type="password"
            id="new_password"
            name="new_password"
            class="recovery-input"
            placeholder="Enter new password"
            required
            minlength="8"
            autocomplete="new-password"
          >
          <p class="recovery-hint">At least 8 characters, with uppercase, lowercase, and a number.</p>
        </div>

        <div class="form-group">
          <label for="confirm_password" class="recovery-label">Confirm new password</label>
          <input
            type="password"
            id="confirm_password"
            name="confirm_password"
            class="recovery-input"
            placeholder="Confirm new password"
            required
            minlength="8"
            autocomplete="new-password"
          >
        </div>

        <p id="resetError" class="recovery-error" role="alert"></p>

        <button type="submit" class="recovery-btn">Save New Password</button>
      </form>

      <a href="./login.php" class="recovery-back">&larr; Back to Login</a>
    </section>
  </main>

  <footer class="recovery-footer">
    <span>POWERED BY LIFE CHOICES</span>
    <span>&bull; 2026 LC ATTENDS</span>
  </footer>

  <script>
    (function () {
      const form = document.getElementById('resetPasswordForm');
      const emailInput = document.getElementById('reset_email');
      const passInput = document.getElementById('new_password');
      const confirmInput = document.getElementById('confirm_password');
      const errorBox = document.getElementById('resetError');

      function showError(message, field) {
        [emailInput, passInput, confirmInput].forEach(el => el.classList.remove('is-invalid'));
        if (field) field.classList.add('is-invalid');
        errorBox.textContent = '\u26A0 ' + message;
        errorBox.classList.add('is-visible');
        if (field) field.focus();
      }

      form.addEventListener('submit', function (event) {
        event.preventDefault();

        const email = emailInput.value.trim();
        const password = passInput.value;
        const confirm = confirmInput.value;
        const emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        const strongEnough = password.length >= 8 && /[A-Z]/.test(password) && /[a-z]/.test(password) && /\d/.test(password);

        if (!emailValid) {
          showError('Please enter a valid email address.', emailInput);
          return;
        }

        if (!strongEnough) {
          showError('Password must be at least 8 characters with uppercase, lowercase, and a number.', passInput);
          return;
        }

        if (password !== confirm) {
          showError('New password and confirmation password do not match.', confirmInput);
          return;
        }

        // Redirect back to login page upon frontend completion
        window.location.href = './login.php?reset=success';
      });
    })();
  </script>
</body>
</html>
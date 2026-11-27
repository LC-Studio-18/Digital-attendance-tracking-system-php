<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Recovery — LC Attends</title>
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

    /* Top bar */
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

    /* Center section */
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

    /* White recovery card */
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

    /* Top-left and bottom-right lime accent lines from Figma */
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
      margin: 0 0 22px;
      text-align: center;
      font-size: 11.5px;
      line-height: 1.45;
      color: #5b6c74;
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

    .recovery-error {
      display: none;
      align-items: center;
      gap: 5px;
      margin-top: 6px;
      color: #d93025;
      font-size: 11px;
      font-weight: 500;
    }

    .recovery-error.is-visible {
      display: flex;
    }

    .recovery-btn {
      display: block;
      width: 180px;
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

    /* Success state */
    .recovery-success {
      display: none;
      text-align: center;
      padding: 8px 4px;
    }

    .recovery-success.is-visible {
      display: block;
    }

    .recovery-success__icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 12px;
      color: #0e181f;
    }

    .recovery-next-link {
      display: inline-block;
      margin-top: 14px;
      padding: 8px 14px;
      border-radius: 6px;
      background-color: #0e181f;
      color: #b2ed1d;
      font-size: 11.5px;
      font-weight: 600;
      text-decoration: none;
    }

    /* Bottom bar */
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

    <section class="recovery-card" aria-live="polite">
      <!-- State 1 & 2: Form + Error State -->
      <div id="recoveryFormView">
        <h1 class="recovery-card__title">Forgot your password?</h1>
        <p class="recovery-card__subtitle">
          Enter your registered email address and we'll send you instructions to reset your password.
        </p>

        <form id="forgotPasswordForm" novalidate>
          <label for="recoveryEmail" class="recovery-label">Email address</label>
          <input
            type="email"
            id="recoveryEmail"
            name="email"
            class="recovery-input"
            placeholder="name@example.com"
            required
            autocomplete="email"
          >
          <p id="recoveryError" class="recovery-error" role="alert">
            &#9888; Please enter a valid email address.
          </p>

          <button type="submit" class="recovery-btn">Send Reset Link</button>
        </form>

        <a href="./login.php" class="recovery-back">&larr; Back to Login</a>
      </div>

      <!-- State 3: Check your email! -->
      <div id="recoverySuccessView" class="recovery-success">
        <div class="recovery-success__icon" aria-hidden="true">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="4" width="20" height="16" rx="2"></rect>
            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
          </svg>
        </div>
        <h2 class="recovery-card__title">Check your email!</h2>
        <p class="recovery-card__subtitle">
          We've sent a password reset link to your registered email address. Follow the instructions in the email to create a new password.
        </p>
        <a href="./login.php" class="recovery-back">&larr; Back to Login</a>
        <div>
          <a id="proceedResetLink" href="./reset-password.php" class="recovery-next-link">Continue to Create New Password &rarr;</a>
        </div>
      </div>
    </section>
  </main>

  <footer class="recovery-footer">
    <span>POWERED BY LIFE CHOICES</span>
    <span>&bull; 2026 LC ATTENDS</span>
  </footer>

  <script>
    (function () {
      const form = document.getElementById('forgotPasswordForm');
      const emailInput = document.getElementById('recoveryEmail');
      const errorMsg = document.getElementById('recoveryError');
      const formView = document.getElementById('recoveryFormView');
      const successView = document.getElementById('recoverySuccessView');
      const proceedLink = document.getElementById('proceedResetLink');

      form.addEventListener('submit', function (event) {
        event.preventDefault();
        const email = emailInput.value.trim();
        const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

        if (!isValid) {
          emailInput.classList.add('is-invalid');
          errorMsg.classList.add('is-visible');
          emailInput.focus();
          return;
        }

        emailInput.classList.remove('is-invalid');
        errorMsg.classList.remove('is-visible');
        proceedLink.href = './reset-password.php?email=' + encodeURIComponent(email);
        formView.style.display = 'none';
        successView.classList.add('is-visible');
      });

      emailInput.addEventListener('input', function () {
        if (errorMsg.classList.contains('is-visible')) {
          emailInput.classList.remove('is-invalid');
          errorMsg.classList.remove('is-visible');
        }
      });
    })();
  </script>
</body>
</html>
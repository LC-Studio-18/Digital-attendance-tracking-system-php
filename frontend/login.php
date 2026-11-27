<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$loginError = isset($_SESSION['login_error']) ? (string) $_SESSION['login_error'] : '';
$oldEmail = isset($_SESSION['old_email']) ? (string) $_SESSION['old_email'] : '';

unset($_SESSION['login_error'], $_SESSION['old_email']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login — LC Attends</title>
  <link rel="stylesheet" href="./assets/css/variables.css" />
  <link rel="stylesheet" href="./assets/css/base.css" />
  <link rel="stylesheet" href="./assets/css/layout.css" />
  <link rel="stylesheet" href="./assets/css/component.css" />
  <link rel="stylesheet" href="./assets/css/login.css" />
</head>
<body class="login-page">
  <header class="login-topbar">
    <span class="login-topbar-brand">LC Attends</span>
    <span class="login-topbar-portal">
      <span class="login-dot" aria-hidden="true"></span>
      Staff Attendance Portal
    </span>
  </header>

  <main class="login-main">
    <section class="login-brand" aria-label="Welcome to LC Attends">
      <a href="./login.php" class="login-logo-link" aria-label="Life Choices home">
        <img
          src="./assets/images/life-choices-logo.png"
          alt="Life Choices"
          class="login-logo-img"
        />
      </a>

      <h1 class="login-hero-title">
        WELCOME TO <span class="login-hero-emphasis">LC ATTENDS.</span>
      </h1>

      <p class="login-hero-sub">Your attendance, managed in real time.</p>
      <p class="login-hero-body">
        Manage your workday efficiently. Sign in to access your attendance records and stay informed.
      </p>
      <p class="login-hero-tagline">Modern workforce management, built around simplicity.</p>
    </section>

    <section class="login-card" aria-labelledby="loginCardHeading">
      <p class="login-welcome">WELCOME BACK</p>
      <h2 id="loginCardHeading" class="login-card-title">Sign in to your account</h2>
      <p class="login-card-subtitle">Enter your details below to continue.</p>

      <div id="offlineBanner" class="login-offline-banner" role="status" aria-live="polite" hidden>
        You're offline. Check your connection and try again.
      </div>

      <!-- Confirm exact POST target path with Lisekho (e.g. ../backend/auth/login.php) -->
      <form id="loginForm" action="./login-submit.php" method="post" novalidate>
        <div class="login-field">
          <label for="email" class="login-label--email">Email address</label>
          <div class="login-input-wrap">
            <input
              id="email"
              name="email"
              type="email"
              class="login-input"
              placeholder="name@example.com"
              value="<?= htmlspecialchars($oldEmail, ENT_QUOTES, 'UTF-8') ?>"
              autocomplete="email"
              aria-describedby="emailError"
              required
            />
          </div>
          <span id="emailError" class="login-field-error" aria-live="polite"></span>
        </div>

        <div class="login-field">
          <label for="password" class="login-label--password">Password</label>
          <div class="login-input-wrap">
            <input
              id="password"
              name="password"
              type="password"
              class="login-input <?= $loginError !== '' ? 'is-invalid' : '' ?>"
              placeholder="••••••••••"
              autocomplete="current-password"
              aria-describedby="passwordError"
              required
            />
            <button
              type="button"
              id="togglePasswordBtn"
              class="login-toggle-password"
              aria-label="Show password"
              aria-pressed="false"
            >
              <!-- 18x18 eye-off control (stroke-width 2.5, right 27px, top 18px) -->
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.77 21.77 0 0 1 5.06-5.94" />
                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" />
                <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24" />
                <line x1="1" y1="1" x2="23" y2="23" />
              </svg>
            </button>
          </div>
          <span id="passwordError" class="login-field-error" role="alert" aria-live="polite"><?= htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8') ?></span>
        </div>

        <div class="login-utility-row">
          <label class="login-remember">
            <input type="checkbox" name="remember" value="1" />
            <span>Remember me</span>
          </label> 
          <div class="auth-actions">
          <a href="./forgot-password.php" class="auth-forgot-link">Forgot password?</a>
        </div>
        </div>

        <button type="submit" id="loginSubmitBtn" class="login-submit">
          <span>Sign in</span>
          <span aria-hidden="true">&rarr;</span>
        </button>

        <p class="login-card-note">Your information. Your attendance. Securely managed.</p>
      </form>
    </section>
  </main>

  <footer class="login-footer">
    <span class="login-footer-text">POWERED BY LIFE CHOICES</span>
    <span class="login-footer-text">
      <span class="login-dot" aria-hidden="true"></span>
      2026 LC ATTENDS
    </span>
  </footer>

  <script type="module" src="./assets/js/login.js"></script>
</body>
</html>
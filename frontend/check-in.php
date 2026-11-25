<?php

/**
 * QR check-in landing screen.
 *
 * Route: /check-in.php (served from the frontend/ web root).
 * This is a presentational page only: it renders the scanner UI and exposes
 * `data-checkin-*` hooks. qr-scanner.js owns the camera, and check-in.js wires
 * the UI to it. No credentials or database access belong in this file, and all
 * dynamic values are escaped before being printed into HTML.
 *
 * Naming: every CSS class and HTML ID below uses the unique `qr-checkin-`
 * prefix so it cannot collide with styles or IDs from other contributors.
 */

declare(strict_types=1);

// Shared application constants (APP_NAME, BASE_URL), not database configuration.
require_once __DIR__ . '/../backend/config/config.php';

// $e escapes any value before it is placed in HTML attributes or text.
$e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

// Asset links respect BASE_URL so the page also works in a subfolder deployment.
$assetBase = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f172a">
    <title><?= $e(APP_NAME . ' | QR check-in') ?></title>
    <!-- Styles are scoped to .qr-checkin-page; see check-in.css for section notes. -->
    <link rel="stylesheet" href="<?= $e($assetBase . '/assets/css/check-in.css') ?>">
</head>
<body class="qr-checkin-page">
    <!-- .qr-checkin-shell centres the card and caps its width on large screens. -->
    <main class="qr-checkin-shell">
        <!-- Intro explains both check-in methods before the user touches anything. -->
        <header class="qr-checkin-header">
            <span class="qr-checkin-header__eyebrow"><?= $e(APP_NAME) ?></span>
            <h1 class="qr-checkin-header__title">Check in with your QR code</h1>
            <p class="qr-checkin-header__subtitle">Scan the code on your staff badge, or enter your code manually.</p>
        </header>

        <!-- Scanner card groups the live status, camera viewport and controls. -->
        <section class="qr-checkin-card" aria-labelledby="qr-checkin-scanner-heading">
            <h2 class="qr-checkin-card__title" id="qr-checkin-scanner-heading">Camera scanner</h2>

            <!-- role="status"/aria-live announce scanner updates to screen readers. -->
            <p class="qr-checkin-status" data-checkin-status data-state="idle" role="status" aria-live="polite">
                Ready to scan. Start the camera when you are ready.
            </p>

            <!-- The video receives the camera stream from qr-scanner.js. The four
                 corner spans are a decorative aiming guide, hidden from assistive
                 tech; the placeholder is removed once the camera is running. -->
            <div class="qr-checkin-scanner-frame">
                <video class="qr-checkin-scanner-frame__video" data-checkin-video playsinline muted autoplay aria-label="Camera preview for QR code scanning"></video>
                <div class="qr-checkin-scanner-frame__overlay" aria-hidden="true">
                    <span class="qr-checkin-scanner-frame__corner qr-checkin-scanner-frame__corner--top-left"></span>
                    <span class="qr-checkin-scanner-frame__corner qr-checkin-scanner-frame__corner--top-right"></span>
                    <span class="qr-checkin-scanner-frame__corner qr-checkin-scanner-frame__corner--bottom-left"></span>
                    <span class="qr-checkin-scanner-frame__corner qr-checkin-scanner-frame__corner--bottom-right"></span>
                </div>
                <p class="qr-checkin-scanner-frame__placeholder" data-checkin-placeholder>Camera preview will appear here</p>
            </div>

            <!-- check-in.js shows/hides these as the scanner changes state. -->
            <div class="qr-checkin-actions">
                <button class="qr-checkin-button qr-checkin-button--primary" type="button" data-checkin-camera>Start camera</button>
                <button class="qr-checkin-button qr-checkin-button--secondary" type="button" data-checkin-stop hidden>Stop camera</button>
                <button class="qr-checkin-button qr-checkin-button--link" type="button" data-checkin-manual>Enter code manually</button>
            </div>

            <!-- Manual entry fallback: revealed on camera errors, on request, or
                 after a scan so the user can confirm the captured code. -->
            <section class="qr-checkin-manual-entry" data-checkin-fallback hidden aria-labelledby="qr-checkin-manual-heading">
                <h3 class="qr-checkin-manual-entry__title" id="qr-checkin-manual-heading">Enter your code</h3>
                <p class="qr-checkin-manual-entry__hint" data-checkin-fallback-message></p>

                <!-- TODO(backend): set data-checkin-endpoint to the check-in API route once it exists.
                     check-in.js already POSTs { code } there; an empty value means "not connected yet". -->
                <!-- novalidate: JavaScript validates the code and shows an inline message instead. -->
                <form class="qr-checkin-form" data-checkin-form data-checkin-endpoint="" novalidate>
                    <label class="qr-checkin-form__label" for="qr-checkin-code">Check-in code</label>
                    <input
                        class="qr-checkin-form__input"
                        id="qr-checkin-code"
                        name="code"
                        data-checkin-code
                        type="text"
                        inputmode="text"
                        autocomplete="off"
                        spellcheck="false"
                        placeholder="e.g. STAFF-1234"
                        required
                    >
                    <button class="qr-checkin-button qr-checkin-button--primary" type="submit" data-checkin-submit>Confirm check-in</button>
                </form>

                <!-- Feedback is written with textContent by JavaScript, so a
                     scanned code is never injected as HTML. -->
                <p class="qr-checkin-result" data-checkin-result data-state="idle" role="status" aria-live="polite" hidden></p>
            </section>
        </section>

        <!-- Reminder that a camera is optional, not a blocker for check-in. -->
        <footer class="qr-checkin-footer">
            <p>Cannot use the camera? Manual entry works on any device.</p>
        </footer>
    </main>

    <!-- Load order matters: the scanner component is defined before the page
         controller tries to construct it. -->
    <script src="<?= $e($assetBase . '/assets/js/qr-scanner.js') ?>"></script>
    <script src="<?= $e($assetBase . '/assets/js/check-in.js') ?>"></script>
</body>
</html>

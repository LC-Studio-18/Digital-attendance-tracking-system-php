<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/config/config.php';

$e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$assetBase = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f172a">
    <title><?= $e(APP_NAME . ' | QR check-in') ?></title>
    <link rel="stylesheet" href="<?= $e($assetBase . '/assets/css/check-in.css') ?>">
</head>
<body class="checkin-page">
    <main class="checkin-shell">
        <header class="checkin-header">
            <span class="checkin-header__eyebrow"><?= $e(APP_NAME) ?></span>
            <h1 class="checkin-header__title">Check in with your QR code</h1>
            <p class="checkin-header__subtitle">Scan the code on your staff badge, or enter your code manually.</p>
        </header>

        <section class="checkin-card" aria-labelledby="scanner-heading">
            <h2 class="checkin-card__title" id="scanner-heading">Camera scanner</h2>

            <p class="checkin-status" data-checkin-status data-state="idle" role="status" aria-live="polite">
                Ready to scan. Start the camera when you are ready.
            </p>

            <div class="scanner-frame">
                <video class="scanner-frame__video" data-checkin-video playsinline muted autoplay aria-label="Camera preview for QR code scanning"></video>
                <div class="scanner-frame__overlay" aria-hidden="true">
                    <span class="scanner-frame__corner scanner-frame__corner--top-left"></span>
                    <span class="scanner-frame__corner scanner-frame__corner--top-right"></span>
                    <span class="scanner-frame__corner scanner-frame__corner--bottom-left"></span>
                    <span class="scanner-frame__corner scanner-frame__corner--bottom-right"></span>
                </div>
                <p class="scanner-frame__placeholder" data-checkin-placeholder>Camera preview will appear here</p>
            </div>

            <div class="checkin-actions">
                <button class="button button--primary" type="button" data-checkin-camera>Start camera</button>
                <button class="button button--secondary" type="button" data-checkin-stop hidden>Stop camera</button>
                <button class="button button--link" type="button" data-checkin-manual>Enter code manually</button>
            </div>

            <section class="manual-entry" data-checkin-fallback hidden aria-labelledby="manual-heading">
                <h3 class="manual-entry__title" id="manual-heading">Enter your code</h3>
                <p class="manual-entry__hint" data-checkin-fallback-message></p>

                <!-- TODO(backend): set data-checkin-endpoint to the check-in API route once it exists. -->
                <form class="checkin-form" data-checkin-form data-checkin-endpoint="" novalidate>
                    <label class="checkin-form__label" for="checkin-code">Check-in code</label>
                    <input
                        class="checkin-form__input"
                        id="checkin-code"
                        name="code"
                        data-checkin-code
                        type="text"
                        inputmode="text"
                        autocomplete="off"
                        spellcheck="false"
                        placeholder="e.g. STAFF-1234"
                        required
                    >
                    <button class="button button--primary" type="submit" data-checkin-submit>Confirm check-in</button>
                </form>

                <p class="checkin-result" data-checkin-result data-state="idle" role="status" aria-live="polite" hidden></p>
            </section>
        </section>

        <footer class="checkin-footer">
            <p>Cannot use the camera? Manual entry works on any device.</p>
        </footer>
    </main>

    <script src="<?= $e($assetBase . '/assets/js/qr-scanner.js') ?>"></script>
    <script src="<?= $e($assetBase . '/assets/js/check-in.js') ?>"></script>
</body>
</html>

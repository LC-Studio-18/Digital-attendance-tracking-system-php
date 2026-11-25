<?php
/**
 * Reusable IN/OUT badge markup (S2-12A).
 * Usage:  <?php render_status_badge(); ?>
 *         <?php render_status_badge(['endpoint' => '/some/url', 'class' => 'my-extra-class']); ?>
 * Requires on the page: assets/css/status-badge.css, assets/js/status-api.js, assets/js/status-badge.js (defer).
 */
function render_status_badge(array $opts = []): void
{
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/../config.php';
    }

    $endpoint = $opts['endpoint'] ?? $config['status_endpoint'];
    $extra    = isset($opts['class']) ? ' ' . $opts['class'] : '';
    $e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    ?>
    <span class="status-badge-wrap<?= $e($extra) ?>">
        <span class="status-badge" data-status-badge data-state="loading" data-endpoint="<?= $e($endpoint) ?>">
            <span class="status-badge__icon" aria-hidden="true"></span>
            <span class="status-badge__sr">Attendance status: </span>
            <span class="status-badge__label">Checking...</span>
        </span>
        <span class="status-badge__live" role="status" aria-live="polite" aria-atomic="true"></span>
    </span>
    <?php
}

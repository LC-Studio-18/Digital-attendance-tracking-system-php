<?php
/**
 * Reusable IN/OUT badge markup (S2-12A).
 * Usage:  <?php render_status_badge(); ?>
 *         <?php render_status_badge(['show_detail' => true]); ?>   // adds "You are currently Clocked In / Since 08:30"
 *         <?php render_status_badge(['endpoint' => '/some/url', 'class' => 'my-extra-class']); ?>
 * Requires on the page: assets/css/status-badge.css, assets/js/status-api.js, assets/js/status-badge.js (defer).
 */
function render_status_badge(array $opts = []): void
{
    $configFile = __DIR__ . '/../config.php';
    $config     = is_file($configFile) ? require $configFile : [];
    $endpoint   = $opts['endpoint'] ?? ($config['status_endpoint'] ?? '');
    $showDetail = !empty($opts['show_detail']);
    $classes    = 'status-badge-wrap'
                . ($showDetail ? ' status-badge-wrap--detail' : '')
                . (isset($opts['class']) ? ' ' . $opts['class'] : '');
    $e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    ?>
    <span class="<?= $e($classes) ?>">
        <span class="status-badge" data-status-badge data-state="loading" data-endpoint="<?= $e($endpoint) ?>">
            <span class="status-badge__icon" aria-hidden="true"></span>
            <span class="status-badge__sr">Attendance status: </span>
            <span class="status-badge__label">Checking...</span>
        </span>
        <?php if ($showDetail): ?>
        <span class="status-badge__detail" hidden>
            <span class="status-badge__detail-text"></span>
            <span class="status-badge__since" hidden></span>
        </span>
        <?php endif; ?>
        <span class="status-badge__live" role="status" aria-live="polite" aria-atomic="true"></span>
    </span>
    <?php
}

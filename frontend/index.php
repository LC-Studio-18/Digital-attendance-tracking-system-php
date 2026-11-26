<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/config/config.php';
require_once __DIR__ . '/../backend/includes/functions.php';

session_start();

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$clientAddress = $_SERVER['REMOTE_ADDR'] ?? '';
$localPreview = in_array($clientAddress, ['127.0.0.1', '::1'], true) && isset($_GET['preview']);

/*
 * This file is the front controller for the frontend web root.
 * Route the dashboard API request through to the backend endpoint so the
 * browser never needs to reach backend/ directly.
 */
$isDashboardApi = strpos($requestPath, '/api/dashboard') === 0
    || (($_GET['api'] ?? '') === 'dashboard');

if ($isDashboardApi) {
    header('Content-Type: application/json');

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }

    if (empty($_SESSION['staff_id']) && !$localPreview) {
        http_response_code(401);
        echo json_encode(['error' => 'Not authenticated']);
        exit;
    }

    require __DIR__ . '/../backend/api/dashboard.php';
    exit;
}

// Unknown API paths get a clean JSON 404 instead of an HTML login redirect.
if (strpos($requestPath, '/api/') === 0) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'API endpoint not implemented']);
    exit;
}

// Routing guard: only authenticated staff may reach the dashboard.
// Login sets $_SESSION['staff_id'] before redirecting back to this page.
if (empty($_SESSION['staff_id']) && !$localPreview) {
    redirectTo('login.php');
}

header('Cache-Control: no-store');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | <?php echo htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/component.css">
</head>
<body>
    <div class="app">
        <aside class="sidebar">
            <div class="sidebar__logo">LC Attends</div>
            <nav class="sidebar__nav">
                <a href="index.php" class="sidebar__link sidebar__link--active">Dashboard</a>
                <a href="#" class="sidebar__link">Attendance log</a>
            </nav>
            <a href="#" class="sidebar__logout">Logout</a>
        </aside>

        <div class="main">
            <header class="topbar">
                <span id="today-date"></span>
                <span id="last-updated"></span>
                <span id="user-chip"></span>
            </header>

            <main class="dashboard" id="dashboard" aria-busy="true">
                <section id="greeting"></section>

                <!--
                  S2-13 shared loading/error contract. The shared script has not
                  landed in this repository yet; the inline implementation in
                  the script below provides the same data-component hooks.
                -->
                <div id="dashboard-loading" data-component="LoadingState" role="status" aria-live="polite">
                    <p id="dashboard-loading-message">Loading attendance data&hellip;</p>
                </div>
                <div id="dashboard-error" data-component="ErrorState" role="alert" hidden>
                    <p id="dashboard-error-message">Something went wrong.</p>
                    <button type="button" id="dashboard-retry" data-feedback-action="retry">Try again</button>
                </div>

                <div id="dashboard-content" hidden>
                    <div class="row row--status">
                        <section id="status-badge"></section>
                        <section id="clocked-out"></section>
                    </div>

                    <section id="hours-summary"></section>

                    <div class="row row--history">
                        <section id="recent-records"></section>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        (function () {
            'use strict';

            var PREVIEW = <?php echo isset($_GET['preview']) ? 'true' : 'false'; ?>;
            var ENDPOINT = 'index.php?api=dashboard' + (PREVIEW ? '&preview=1' : '');
            var DEBUG = <?php echo json_encode(APP_DEBUG); ?>;
            var STAFF_ID = <?php echo json_encode($_SESSION['staff_id'] ?? null); ?>;
            var TIMEOUT_MS = 10000;
            var REFRESH_EVENTS = [
                'attendance:clock-updated',
                'attendance:clock-success',
                'attendance:clocked-in',
                'attendance:clocked-out'
            ];

            // Sample data from the dashboard-ui branch (frontend/assets/js/dashboard.js).
            // Used when the dashboard API is not available yet, or in ?preview=1.
            var DUMMY_DATA = {
                status: 'IN',
                clockInDisplay: '09:15 AM',
                clockInDateDisplay: 'Today, 24 Mar 2025',
                hours: { today: 4.5, week: 22.25 },
                clockOutDisplay: null,
                lastUpdatedDisplay: '24 Mar 2025, 09:15 AM',
                generated_at: '2025-03-24 09:15:00',
                recent_records: [
                    { record_id: 4, clock_in: '2026-11-24 09:15:00', clock_out: null, hours: null, is_corrected: false },
                    { record_id: 3, clock_in: '2026-11-23 08:30:00', clock_out: '2026-11-23 16:30:00', hours: 8, is_corrected: true },
                    { record_id: 2, clock_in: '2026-11-22 08:25:00', clock_out: '2026-11-22 16:30:00', hours: 8.08, is_corrected: false }
                ]
            };

            var dashboard = document.getElementById('dashboard');
            var contentEl = document.getElementById('dashboard-content');
            var todayEl = document.getElementById('today-date');
            var lastUpdatedEl = document.getElementById('last-updated');
            var userEl = document.getElementById('user-chip');
            var greetingEl = document.getElementById('greeting');

            var lastData = null;
            var activeController = null;
            var refreshTimer = null;
            var retryHandler = null;

            /* ---------- S2-13 shared loading/error component (inline shim) ---------- */

            var loadingEl = document.getElementById('dashboard-loading');
            var loadingMessageEl = document.getElementById('dashboard-loading-message');
            var errorEl = document.getElementById('dashboard-error');
            var errorMessageEl = document.getElementById('dashboard-error-message');
            var retryBtn = document.getElementById('dashboard-retry');

            if (!window.SharedFeedback || typeof window.SharedFeedback.showLoading !== 'function') {
                window.SharedFeedback = {
                    onRetry: function (handler) {
                        retryHandler = handler;
                    },
                    showLoading: function (message) {
                        loadingEl.hidden = false;
                        errorEl.hidden = true;
                        if (message) {
                            loadingMessageEl.textContent = message;
                        }
                    },
                    showError: function (message) {
                        loadingEl.hidden = true;
                        errorEl.hidden = false;
                        errorMessageEl.textContent = message || 'Something went wrong.';
                    },
                    hide: function () {
                        loadingEl.hidden = true;
                        errorEl.hidden = true;
                    }
                };
            }

            var feedback = window.SharedFeedback;

            if (retryBtn) {
                retryBtn.addEventListener('click', function () {
                    if (typeof retryHandler === 'function') {
                        retryHandler();
                    }
                });
            }
            feedback.onRetry(function () { loadDashboard(); });

            /* ---------- Data normalising and rendering ---------- */

            function escapeHtml(value) {
                return String(value === null || value === undefined ? '' : value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function parseApiDate(value) {
                if (!value) {
                    return null;
                }
                var date = new Date(String(value).replace(' ', 'T'));
                return isNaN(date.getTime()) ? null : date;
            }

            function formatDate(value) {
                var date = parseApiDate(value);
                return date
                    ? date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
                    : '';
            }

            function formatTime(value) {
                var date = parseApiDate(value);
                return date
                    ? date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
                    : '--:--';
            }

            function formatDateTime(value) {
                var date = parseApiDate(value);
                return date
                    ? formatDate(value) + ', ' + formatTime(value)
                    : '';
            }

            function firstClock(records, field) {
                for (var i = 0; i < records.length; i += 1) {
                    if (records[i] && records[i][field]) {
                        return records[i][field];
                    }
                }
                return null;
            }

            // Maps GET /api/dashboard JSON (and the sample data) to page values.
            function normalise(raw) {
                var data = raw && typeof raw === 'object' ? raw : {};
                var hours = data.hours || {};
                var records = Array.isArray(data.recent_records)
                    ? data.recent_records
                    : (Array.isArray(data.records) ? data.records
                        : (Array.isArray(data.recentActivity) ? data.recentActivity : []));
                var status = String(data.status || '').toUpperCase() === 'IN' ? 'IN' : 'OUT';

                return {
                    status: status,
                    hours: {
                        today: Number(hours.today) || 0,
                        week: Number(hours.week) || 0
                    },
                    records: records.slice(0, 10),
                    clockInAt: firstClock(records, 'clock_in'),
                    clockOutAt: firstClock(records, 'clock_out'),
                    clockInDisplay: data.clockInDisplay || data.clockedInAt || null,
                    clockInDateDisplay: data.clockInDateDisplay || data.dateText || null,
                    clockOutDisplay: data.clockOutDisplay || data.clockedOutAt || null,
                    lastUpdatedDisplay: data.lastUpdatedDisplay || data.lastUpdated || null,
                    generatedAt: data.generated_at || data.generatedAt || null
                };
            }

            function renderStatus(data) {
                var el = document.getElementById('status-badge');
                var isIn = data.status === 'IN';
                var clockInText = data.clockInDisplay || (data.clockInAt ? formatTime(data.clockInAt) : '--:--');
                var dateText = data.clockInDateDisplay || (data.clockInAt ? formatDate(data.clockInAt) : '');

                el.className = 'status-card ' + (isIn ? 'status-card--in' : 'status-card--out');
                el.innerHTML =
                    '<div class="status-card__icon">' + (isIn ? '&#10003;' : '&#8212;') + '</div>' +
                    '<div class="status-card__main">' +
                        '<span class="status-card__label">Current Status</span>' +
                        '<span class="status-card__value">' + (isIn ? 'IN' : 'OUT') + '</span>' +
                        '<span class="status-card__pill">' + (isIn ? 'Clocked In' : 'Clocked Out') + '</span>' +
                    '</div>' +
                    '<div class="status-card__time">' +
                        '<span class="status-card__label">Clocked In At</span>' +
                        '<span class="status-card__value status-card__value--time">' + escapeHtml(clockInText) + '</span>' +
                        '<span class="status-card__date">' + escapeHtml(dateText) + '</span>' +
                    '</div>';
            }

            function renderClockedOut(data) {
                var el = document.getElementById('clocked-out');
                var hasClockOut = Boolean(data.clockOutDisplay || (data.status === 'OUT' && data.clockOutAt));
                var clockOutText = data.clockOutDisplay ||
                    (data.clockOutAt ? formatTime(data.clockOutAt) : '--:--');
                var updatedText = data.lastUpdatedDisplay ||
                    (data.generatedAt ? formatDateTime(data.generatedAt) : 'just now');

                el.className = 'clocked-out-card';
                el.innerHTML =
                    '<span class="clocked-out-card__label">Clocked Out</span>' +
                    '<span class="clocked-out-card__time">' + escapeHtml(clockOutText) + '</span>' +
                    '<span class="clocked-out-card__note">' + (hasClockOut ? 'Shift ended' : 'Not yet clocked out') + '</span>' +
                    '<span class="clocked-out-card__updated">' +
                        'Last updated ' + escapeHtml(updatedText) +
                    '</span>';
            }

            function renderHours(data) {
                var el = document.getElementById('hours-summary');

                el.innerHTML =
                    '<div class="hours-card">' +
                        '<span class="hours-card__label">Today</span>' +
                        '<span class="hours-card__value">' + data.hours.today.toFixed(2) + ' h</span>' +
                    '</div>' +
                    '<div class="hours-card">' +
                        '<span class="hours-card__label">This week</span>' +
                        '<span class="hours-card__value">' + data.hours.week.toFixed(2) + ' h</span>' +
                    '</div>';
            }

            function renderRecords(records) {
                var el = document.getElementById('recent-records');

                el.className = 'records-card';

                if (!records.length) {
                    el.innerHTML =
                        '<h2 class="records-card__title">Attendance History</h2>' +
                        '<p class="records-card__empty">No records yet.</p>';
                    return;
                }

                var rows = records.map(function (record) {
                    var clockOut = record.clock_out ? formatTime(record.clock_out) : 'Still in';
                    var hours = (record.hours === null || record.hours === undefined)
                        ? '--'
                        : Number(record.hours).toFixed(2) + ' h';
                    var tag = record.is_corrected ? '<span class="record__tag">Corrected</span>' : '';

                    return '<li class="record ' + (record.is_corrected ? 'record--corrected' : '') + '">' +
                        '<div class="record__date">' + escapeHtml(formatDate(record.clock_in)) + ' ' + tag + '</div>' +
                        '<div class="record__times">' +
                            '<span>In: ' + escapeHtml(formatTime(record.clock_in)) + '</span>' +
                            '<span>Out: ' + escapeHtml(clockOut) + '</span>' +
                            '<span>' + escapeHtml(hours) + '</span>' +
                        '</div>' +
                    '</li>';
                }).join('');

                el.innerHTML =
                    '<h2 class="records-card__title">Attendance History</h2>' +
                    '<ul class="records-list">' + rows + '</ul>';
            }

            function render(data) {
                todayEl.textContent = new Date().toLocaleDateString('en-US', {
                    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
                });
                userEl.textContent = STAFF_ID ? 'Staff #' + STAFF_ID : 'Staff';
                greetingEl.textContent = 'Dashboard';
                lastUpdatedEl.textContent = data.lastUpdatedDisplay ||
                    (data.generatedAt ? 'Last updated ' + formatDateTime(data.generatedAt) : '');

                renderStatus(data);
                renderClockedOut(data);
                renderHours(data);
                renderRecords(data.records);
            }

            function setState(state, message) {
                dashboard.setAttribute('aria-busy', state === 'loading' ? 'true' : 'false');

                if (state === 'loading') {
                    feedback.showLoading(message);
                } else if (state === 'error') {
                    feedback.showError(message);
                } else {
                    feedback.hide();
                }
            }

            function showDashboard(data, isSample) {
                lastData = data;
                render(data);
                contentEl.hidden = false;
                setState('ready');
                if (isSample) {
                    lastUpdatedEl.textContent += ' (sample data)';
                }
            }

            /* ---------- GET /api/dashboard via fetch/AJAX ---------- */

            function loadDashboard() {
                if (PREVIEW) {
                    showDashboard(normalise(DUMMY_DATA), true);
                    return Promise.resolve(lastData);
                }

                if (activeController) {
                    activeController.abort(); // a newer refresh replaces this request
                }

                var controller = new AbortController();
                var timedOut = false;
                activeController = controller;

                var timer = window.setTimeout(function () {
                    timedOut = true;
                    controller.abort();
                }, TIMEOUT_MS);

                contentEl.hidden = true;
                setState('loading', 'Loading attendance data…');

                return fetch(ENDPOINT, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    cache: 'no-store',
                    signal: controller.signal
                })
                    .then(function (response) {
                        if (!response.ok) {
                            var status = response.status;
                            return response.json().catch(function () { return null; }).then(function (body) {
                                var error = new Error(body && body.error ? body.error : 'HTTP ' + status);
                                error.useFallback = status === 404 || status === 501;
                                throw error;
                            });
                        }
                        return response.json();
                    })
                    .then(function (json) {
                        if (!json || typeof json !== 'object') {
                            var error = new Error('Invalid dashboard response');
                            error.useFallback = true;
                            throw error;
                        }
                        if (json.error) {
                            throw new Error(json.error);
                        }

                        showDashboard(normalise(json), false);
                        return lastData;
                    })
                    .catch(function (error) {
                        if (controller.signal.aborted && !timedOut) {
                            return null; // superseded by a newer refresh
                        }

                        // The endpoint is not available yet: show the sample data
                        // from the dashboard-ui branch instead of an empty page.
                        if (error && error.useFallback) {
                            showDashboard(normalise(DUMMY_DATA), true);
                            return lastData;
                        }

                        var message = timedOut
                            ? 'The request timed out. Please try again.'
                            : (error && error.message ? error.message : 'Could not load dashboard data.');

                        setState('error', DEBUG
                            ? message
                            : 'Could not load dashboard data. Please try again.');
                        return null;
                    })
                    .then(function (result) {
                        window.clearTimeout(timer);
                        if (activeController === controller) {
                            activeController = null;
                        }
                        return result;
                    });
            }

            /* ---------- Post-clock refresh ---------- */

            function scheduleRefresh() {
                window.clearTimeout(refreshTimer);
                refreshTimer = window.setTimeout(loadDashboard, 150);
            }

            REFRESH_EVENTS.forEach(function (eventName) {
                window.addEventListener(eventName, scheduleRefresh);
            });

            window.addEventListener('storage', function (event) {
                if (event.key && REFRESH_EVENTS.indexOf(event.key) !== -1) {
                    scheduleRefresh();
                }
            });

            // Clock controls that do not dispatch an event yet.
            document.addEventListener('click', function (event) {
                if (event.target && event.target.closest &&
                    event.target.closest('[data-clock-action]')) {
                    scheduleRefresh();
                }
            });

            window.Dashboard = {
                refresh: loadDashboard,
                eventNames: REFRESH_EVENTS,
                get data() { return lastData; }
            };

            loadDashboard();
        }());
    </script>
</body>
</html>

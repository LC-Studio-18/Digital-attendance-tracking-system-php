<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/config/config.php';
require_once __DIR__ . '/../backend/includes/functions.php';

// The PHP dev server falls back to this file for unknown /api/* paths.
// Return a clean JSON 404 so the dashboard can use its draft data instead
// of being redirected to the login page.
if (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') === 0) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'API endpoint not implemented']);
    exit;
}

session_start();

// Routing guard: only authenticated staff may reach the dashboard.
// Login sets $_SESSION['staff_id'] before redirecting here.
// Local-only preview: http://localhost:8000/index.php?preview=1
$clientAddress = $_SERVER['REMOTE_ADDR'] ?? '';
$localPreview = in_array($clientAddress, ['127.0.0.1', '::1'], true) && isset($_GET['preview']);

if (empty($_SESSION['staff_id']) && !$localPreview) {
    redirectTo('login.php');
}
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
    <?php
    /*
     * Component contract to agree with Phelisa (frontend3-dashboard-ui):
     *
     * #dashboard-summary         data-component="SummaryCards"
     *     expects: { onsiteCount: number, totalStaff: number, expectedToday: number }
     * #dashboard-onsite-list     data-component="OnsiteStaffList"
     *     expects: [{ id: number, name: string, role: string, clockedInAt: string }]
     * #dashboard-recent-activity data-component="RecentActivityList"
     *     expects: [{ id: number, staffName: string, type: string, timestamp: string }]
     *
     * Loading/error markup below are temporary S2-13 stand-ins.
     * After a successful clock-in/out, dispatch:
     *     window.dispatchEvent(new CustomEvent('attendance:clock-updated'))
     * or call window.Dashboard.refresh().
     */
    ?>
    <main class="dashboard" id="dashboard" aria-busy="true">
        <header class="dashboard__header">
            <h1>Attendance dashboard</h1>
            <p class="dashboard__updated" id="dashboard-updated">Loading&hellip;</p>
        </header>

        <!-- TODO(S2-13): replace with the shared loading component when it lands. -->
        <div id="dashboard-loading" class="dashboard__state dashboard__state--loading" role="status" aria-live="polite">
            Loading dashboard data&hellip;
        </div>

        <!-- TODO(S2-13): replace with the shared error component when it lands. -->
        <div id="dashboard-error" class="dashboard__state dashboard__state--error" role="alert" hidden>
            <p id="dashboard-error-message">Something went wrong.</p>
            <button type="button" id="dashboard-retry">Try again</button>
        </div>

        <div id="dashboard-content" hidden>
            <section id="dashboard-summary" class="dashboard__summary" aria-label="Attendance summary"
                     data-component="SummaryCards"
                     data-expects="onsiteCount:number, totalStaff:number, expectedToday:number"></section>

            <div class="dashboard__panels">
                <section id="dashboard-onsite-list" class="dashboard__list dashboard__panel" aria-label="Staff currently onsite"
                         data-component="OnsiteStaffList"
                         data-expects="[{id:number, name:string, role:string, clockedInAt:string}]"></section>

                <section id="dashboard-recent-activity" class="dashboard__list dashboard__panel" aria-label="Recent attendance activity"
                         data-component="RecentActivityList"
                         data-expects="[{id:number, staffName:string, type:string, timestamp:string}]"></section>
            </div>
        </div>
    </main>

    <script>
        (function () {
            'use strict';

            var ENDPOINT = '/api/dashboard';
            var REFRESH_EVENT = 'attendance:clock-updated';
            var DEBUG = <?php echo json_encode(APP_DEBUG); ?>;

            var dashboard = document.getElementById('dashboard');
            var updatedEl = document.getElementById('dashboard-updated');
            var loadingEl = document.getElementById('dashboard-loading');
            var errorEl = document.getElementById('dashboard-error');
            var errorMessageEl = document.getElementById('dashboard-error-message');
            var retryBtn = document.getElementById('dashboard-retry');
            var contentEl = document.getElementById('dashboard-content');
            var summaryEl = document.getElementById('dashboard-summary');
            var onsiteEl = document.getElementById('dashboard-onsite-list');
            var activityEl = document.getElementById('dashboard-recent-activity');

            var lastData = null;

            /*
             * DRAFT JSON - section 7 placeholder.
             * Used only while GET /api/dashboard is not implemented yet (404/501).
             * Replace this function with the real agreed sample when it is available.
             */
            function draftDashboardData() {
                return {
                    generatedAt: new Date().toISOString(),
                    summary: {
                        onsiteCount: 2,
                        totalStaff: 12,
                        expectedToday: 10
                    },
                    onsiteStaff: [
                        { id: 101, name: 'Jane Doe', role: 'Staff', clockedInAt: '2026-11-25T08:01:00+02:00' },
                        { id: 102, name: 'John Smith', role: 'Staff', clockedInAt: '2026-11-25T08:15:00+02:00' }
                    ],
                    recentActivity: [
                        { id: 9001, staffId: 101, staffName: 'Jane Doe', type: 'clock-in', timestamp: '2026-11-25T08:01:00+02:00' },
                        { id: 9002, staffId: 102, staffName: 'John Smith', type: 'clock-in', timestamp: '2026-11-25T08:15:00+02:00' }
                    ]
                };
            }

            function firstDefined() {
                for (var i = 0; i < arguments.length; i += 1) {
                    if (arguments[i] !== undefined && arguments[i] !== null) {
                        return arguments[i];
                    }
                }
                return undefined;
            }

            function normalise(raw) {
                var data = raw && typeof raw === 'object' ? raw : {};
                var summary = data.summary || data.stats || {};
                var onsite = firstDefined(data.onsiteStaff, data.staffOnSite, data.staff, []);
                var activity = firstDefined(data.recentActivity, data.activity, data.activities, []);

                return {
                    generatedAt: firstDefined(data.generatedAt, data.updatedAt, data.lastUpdated, null),
                    summary: {
                        onsiteCount: Number(firstDefined(summary.onsiteCount, summary.onSiteCount, summary.currentOnsite, 0)),
                        totalStaff: Number(firstDefined(summary.totalStaff, summary.staffTotal, 0)),
                        expectedToday: Number(firstDefined(summary.expectedToday, summary.expected, 0))
                    },
                    onsiteStaff: Array.isArray(onsite) ? onsite : [],
                    recentActivity: Array.isArray(activity) ? activity : []
                };
            }

            function clear(element) {
                while (element.firstChild) {
                    element.removeChild(element.firstChild);
                }
            }

            function formatTime(value) {
                var date = new Date(value);
                return isNaN(date.getTime()) ? String(value) : date.toLocaleTimeString();
            }

            function appendHeading(target, text) {
                var heading = document.createElement('h2');
                heading.textContent = text;
                target.appendChild(heading);
            }

            function appendEmpty(target, text) {
                var paragraph = document.createElement('p');
                paragraph.textContent = text;
                target.appendChild(paragraph);
            }

            function initials(name) {
                return String(name)
                    .split(/\s+/)
                    .filter(function (part) { return part.length > 0; })
                    .slice(0, 2)
                    .map(function (part) { return part.charAt(0); })
                    .join('')
                    .toUpperCase();
            }

            function badgeClass(status) {
                var value = String(status).toLowerCase();
                if (value.indexOf('absent') !== -1) {
                    return 'dashboard__badge dashboard__badge--absent';
                }
                if (value.indexOf('away') !== -1) {
                    return 'dashboard__badge dashboard__badge--away';
                }
                if (value.indexOf('late') !== -1) {
                    return 'dashboard__badge dashboard__badge--late';
                }
                return 'dashboard__badge';
            }

            function renderSummary(target, summary) {
                clear(target);
                appendHeading(target, 'Attendance summary');

                var items = [
                    ['Onsite now', summary.onsiteCount, 'People in the building'],
                    ['Total staff', summary.totalStaff, 'Staff records'],
                    ['Expected today', summary.expectedToday, 'Expected today']
                ];

                items.forEach(function (item) {
                    var card = document.createElement('article');
                    card.className = 'dashboard__card';

                    var label = document.createElement('h3');
                    label.textContent = item[0];

                    var value = document.createElement('p');
                    value.className = 'dashboard__card-value';
                    value.textContent = String(item[1]);

                    var note = document.createElement('p');
                    note.className = 'dashboard__card-note';
                    note.textContent = item[2];

                    card.appendChild(label);
                    card.appendChild(value);
                    card.appendChild(note);
                    target.appendChild(card);
                });
            }

            function renderOnsite(target, staff) {
                clear(target);
                appendHeading(target, 'Currently onsite');

                if (!staff.length) {
                    appendEmpty(target, 'No staff are currently onsite.');
                    return;
                }

                var list = document.createElement('ul');
                staff.forEach(function (person) {
                    var item = document.createElement('li');
                    item.className = 'dashboard__row';

                    var name = firstDefined(person.name, person.staffName, person.fullName, 'Unknown staff');
                    var role = firstDefined(person.role, person.department, '');
                    var clockedIn = firstDefined(person.clockedInAt, person.clockInTime, person.clockedIn, '');
                    var status = firstDefined(person.status, 'Present');

                    var avatar = document.createElement('span');
                    avatar.className = 'dashboard__avatar';
                    avatar.textContent = initials(name);

                    var info = document.createElement('span');
                    info.className = 'dashboard__row-info';

                    var nameEl = document.createElement('strong');
                    nameEl.textContent = name;

                    var meta = document.createElement('span');
                    meta.className = 'dashboard__row-meta';
                    meta.textContent = role + (clockedIn ? ' · In ' + formatTime(clockedIn) : '');

                    var badge = document.createElement('span');
                    badge.className = badgeClass(status);
                    badge.textContent = status;

                    info.appendChild(nameEl);
                    info.appendChild(meta);
                    item.appendChild(avatar);
                    item.appendChild(info);
                    item.appendChild(badge);
                    list.appendChild(item);
                });
                target.appendChild(list);
            }

            function renderActivity(target, activity) {
                clear(target);
                appendHeading(target, 'Recent activity');

                if (!activity.length) {
                    appendEmpty(target, 'No recent attendance activity.');
                    return;
                }

                var list = document.createElement('ul');
                activity.forEach(function (entry) {
                    var item = document.createElement('li');
                    item.className = 'dashboard__activity-item';

                    var name = firstDefined(entry.staffName, entry.name, 'Unknown staff');
                    var type = firstDefined(entry.type, entry.action, 'attendance');
                    var time = firstDefined(entry.timestamp, entry.at, '');

                    var marker = document.createElement('span');
                    marker.className = 'dashboard__activity-marker';

                    var body = document.createElement('span');
                    body.className = 'dashboard__activity-body';

                    var nameEl = document.createElement('strong');
                    nameEl.textContent = name;

                    var detail = document.createElement('span');
                    detail.className = 'dashboard__activity-detail';
                    detail.textContent = type;

                    var timeEl = document.createElement('span');
                    timeEl.className = 'dashboard__activity-time';
                    timeEl.textContent = time ? formatTime(time) : '';

                    body.appendChild(nameEl);
                    body.appendChild(detail);
                    item.appendChild(marker);
                    item.appendChild(body);
                    item.appendChild(timeEl);
                    list.appendChild(item);
                });
                target.appendChild(list);
            }

            function render(data) {
                renderSummary(summaryEl, data.summary);
                renderOnsite(onsiteEl, data.onsiteStaff);
                renderActivity(activityEl, data.recentActivity);
                updatedEl.textContent = data.generatedAt
                    ? 'Last updated ' + formatTime(data.generatedAt)
                    : 'Last updated just now';
            }

            function setState(state) {
                loadingEl.hidden = state !== 'loading';
                errorEl.hidden = state !== 'error';
                contentEl.hidden = state === 'loading' || state === 'error';
                dashboard.setAttribute('aria-busy', state === 'loading' ? 'true' : 'false');
            }

            function showError(message) {
                errorMessageEl.textContent = message;
                setState('error');
            }

            function loadDashboard() {
                setState('loading');

                return fetch(ENDPOINT, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    cache: 'no-store'
                })
                    .then(function (response) {
                        var contentType = response.headers.get('Content-Type') || '';

                        if (response.ok && contentType.indexOf('application/json') !== -1) {
                            return response.json();
                        }
                        // Endpoint not built yet - develop against the draft JSON above.
                        // Also covers a dev-server fallback that returns HTML instead of JSON.
                        if (response.status === 404 || response.status === 501 ||
                            contentType.indexOf('application/json') === -1) {
                            return draftDashboardData();
                        }
                        throw new Error('HTTP ' + response.status);
                    })
                    .then(function (raw) {
                        lastData = normalise(raw);
                        render(lastData);
                        setState('ready');
                        return lastData;
                    })
                    .catch(function (error) {
                        showError(DEBUG
                            ? 'Could not load dashboard: ' + error.message
                            : 'Could not load dashboard data. Please try again.');
                        return null;
                    });
            }

            retryBtn.addEventListener('click', loadDashboard);

            // Post-clock refresh contract: Phelisa's clock code dispatches this event
            // (or calls window.Dashboard.refresh()) after a successful clock-in/out.
            window.addEventListener(REFRESH_EVENT, loadDashboard);
            window.addEventListener('storage', function (event) {
                if (event.key === REFRESH_EVENT || event.key === 'attendance:refresh-dashboard') {
                    loadDashboard();
                }
            });

            // Fallback for clock controls that use data-clock-action before the event is wired.
            document.addEventListener('click', function (event) {
                if (event.target && event.target.closest && event.target.closest('[data-clock-action]')) {
                    window.setTimeout(loadDashboard, 500);
                }
            });

            window.Dashboard = {
                refresh: loadDashboard,
                eventName: REFRESH_EVENT,
                get data() {
                    return lastData;
                }
            };

            loadDashboard();
        }());
    </script>
</body>
</html>

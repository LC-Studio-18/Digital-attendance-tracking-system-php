<?php
declare(strict_types=1);
require_once __DIR__ . '/../backend/config/config.php';
require_once __DIR__ . '/../backend/includes/functions.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clock-Out View</title>
    <link rel="stylesheet" href="assets/css/clockout.css">
</head>
<body>
    <main class="app-shell">
        <header class="topbar">
            <div class="topbar-title">Clock-Out View</div>
            <div class="brand">
                <div class="brand-mark" aria-hidden="true">
                    <span></span><span></span><span></span>
                </div>
                <strong>LC Attends</strong>
            </div>
        </header>

        <section class="content" id="clockOutView">
            <div class="clock-card">
                <div class="clock-card-main">
                    <div class="welcome-block">
                        <div class="clock-icon" aria-hidden="true">
                            <span class="clock-hand clock-hand-hour"></span>
                            <span class="clock-hand clock-hand-minute"></span>
                        </div>
                        <div class="welcome-copy">
                            <p class="greeting" id="dayGreeting">Good Day</p>
                            <h1>Welcome, <span>{Employee}</span></h1>
                            <p class="date-time" id="dateTime"></p>
                        </div>
                    </div>

                </div>

                <button class="clock-out-button" id="openConfirm" type="button">
                    <span class="play-icon" aria-hidden="true"></span>
                    Clock Out
                </button>

                <p class="attendance-active">
                    <span class="shield-icon" aria-hidden="true"></span>
                    Your Attendance is Active
                </p>
            </div>
        </section>
    </main>

    <div class="modal-backdrop" id="confirmModal" role="dialog" aria-modal="true" aria-labelledby="confirmTitle" hidden>
        <div class="modal-card confirm-card">
            <div class="calendar-icon" aria-hidden="true">
                <span class="calendar-binding binding-left"></span>
                <span class="calendar-binding binding-right"></span>
            </div>
            <h2 id="confirmTitle">Are you sure you<br>want to Clock Out?</h2>
            <p>This will record your attendance<br>for the day</p>
            <div class="modal-actions">
                <button class="secondary-button" id="cancelClockOut" type="button">Cancel</button>
                <button class="primary-button" id="confirmClockOut" type="button">Yes</button>
            </div>
        </div>
    </div>

    <main class="app-shell success-shell" id="successView" hidden>
        <header class="topbar">
            <div class="topbar-title">Clocked Out Success View</div>
            <div class="brand">
                <div class="brand-mark" aria-hidden="true">
                    <span></span><span></span><span></span>
                </div>
                <strong>LC Attends</strong>
            </div>
        </header>
        <section class="success-content">
            <div class="success-card">
                <div class="success-copy">
                    <p class="success-greeting" id="successGreeting">Good Afternoon!</p>
                    <p class="success-employee">{Employee}</p>
                    <h2>Enjoy the rest of your day!</h2>
                </div>
                <div class="success-divider" aria-hidden="true"></div>
                <aside class="success-status">
                    <div class="success-icon" aria-hidden="true">
                        <span class="success-check"></span>
                    </div>
                    <strong>Clocked Out</strong>
                    <p>Thank you for your time</p>
                    <small id="clockoutTime">14:30 PM</small>
                </aside>
            </div>
            <button class="dashboard-button" id="backToDashboard" type="button">Back to Dashboard</button>
        </section>
    </main>

    <script src="assets/js/clockout.js"></script>
</body>
</html>
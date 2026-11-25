<?php
// S2-16b - Live onsite dashboard page (admin).
// Subtask 1: page structure. Dummy rows only; data loading comes in later subtasks.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Live Onsite Dashboard</title>

    <!-- Existing shared styles -->
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/component.css">

    <!-- This page's styles -->
    <link rel="stylesheet" href="assets/css/dashboard-admin.css">
</head>
<body>
    <main class="f4-dashboard-admin">

        <header class="f4-dashboard-admin__header">
            <h1 class="f4-dashboard-admin__title">Who is onsite now</h1>
            <p class="f4-dashboard-admin__updated">
                Last updated: <span id="f4-dashboard-admin-updated">--:--:--</span>
            </p>
        </header>

        <section class="f4-dashboard-admin__summary" aria-label="Summary">
            <p class="f4-dashboard-admin__count">
                Total onsite: <strong id="f4-dashboard-admin-count">3</strong>
            </p>
        </section>

        <section class="f4-dashboard-admin__list" aria-label="Staff currently onsite">
            <div class="f4-dashboard-admin__table-wrapper">
                <table class="f4-dashboard-admin__table">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Employee ID</th>
                            <th scope="col">Clock-in time</th>
                            <th scope="col">Time onsite</th>
                        </tr>
                    </thead>
                    <tbody id="f4-dashboard-admin-rows">
                        <!-- DUMMY ROWS: replaced by the fetch() data in a later subtask -->
                        <tr>
                            <td>Dummy Person One</td>
                            <td>EMP001</td>
                            <td>08:02</td>
                            <td>3h 15m</td>
                        </tr>
                        <tr>
                            <td>Dummy Person Two</td>
                            <td>EMP002</td>
                            <td>08:45</td>
                            <td>2h 32m</td>
                        </tr>
                        <tr>
                            <td>Dummy Person Three</td>
                            <td>EMP003</td>
                            <td>09:10</td>
                            <td>2h 07m</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

    </main>

    <script src="assets/js/dashboard-admin.js"></script>
</body>
</html>
<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/config/config.php';
require_once __DIR__ . '/../backend/includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Dashboard</title>
  <link rel="stylesheet" href="assets/css/variables.css">
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/layout.css">
  <link rel="stylesheet" href="assets/css/component.css">
</head>
<body>
  <div class="pf-app">

    <!-- Sidebar (becomes a bar across the top on phones) -->
    <aside class="pf-sidebar">
      <div class="pf-sidebar__logo">LC Attends</div>

      <nav class="pf-sidebar__nav">
        <a href="index.php" class="pf-sidebar__link pf-sidebar__link--active">
          <svg class="pf-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
          Dashboard
        </a>
        <a href="#" class="pf-sidebar__link">
          <svg class="pf-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
          Attendance log
        </a>
      </nav>

      <!-- Employee Access card (hidden on phones) -->
      <div class="pf-sidebar__access">
        <span class="pf-sidebar__access-pill">Employee Access</span>
        <span class="pf-sidebar__access-role">Employee</span>
        <span class="pf-sidebar__access-place">Life Choices Lansdowne</span>
        <span class="pf-sidebar__live"><span class="pf-sidebar__live-dot"></span>Live</span>
      </div>

      <!-- User card: filled in by renderGreeting() in dashboard.js (hidden on phones) -->
      <div class="pf-sidebar-user" id="sidebar-user"></div>

      <a href="#" class="pf-sidebar__logout">
        <svg class="pf-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/></svg>
        Logout
      </a>
    </aside>

    <div class="pf-main">

      <!-- Top bar: date, notifications, user -->
      <header class="pf-topbar">
        <span id="today-date"></span>
        <span id="user-chip"></span>
      </header>

      <main class="pf-dashboard">
        <!-- Filled in by renderGreeting() in dashboard.js -->
        <section id="greeting"></section>

        <!-- Row 1: current status and clocked out -->
        <div class="pf-row pf-row--status">
          <section id="status-badge"></section>
          <section id="clocked-out"></section>
        </div>

        <!-- Hours today and this week -->
        <section id="hours-summary"></section>

        <!-- Row 2: chart and this week -->
        <div class="pf-row pf-row--overview">
          <section id="attendance-chart"></section>
          <section id="week-summary"></section>
        </div>

        <!-- Row 3: history and help -->
        <div class="pf-row pf-row--history">
          <section id="recent-records"></section>
          <section id="help-card"></section>
        </div>
      </main>

      <footer class="pf-site-footer" id="site-footer"></footer>
    </div>
  </div>

  <script src="assets/js/dashboard.js"></script>
</body>
</html>
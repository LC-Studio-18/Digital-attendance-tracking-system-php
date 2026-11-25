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
  <div class="app">

    <!-- Sidebar (hidden on mobile, where the bottom bar is used) -->
    <aside class="sidebar">
      <div class="sidebar__logo">LC Attends</div>
      <nav class="sidebar__nav">
        <a href="dashboard.php" class="sidebar__link sidebar__link--active">Dashboard</a>
        <a href="#" class="sidebar__link">Attendance log</a>
      </nav>
      <a href="#" class="sidebar__logout">Logout</a>
    </aside>

    <div class="main">

      <!-- Top bar: date, notifications, user -->
      <header class="topbar">
        <span id="today-date"></span>
        <span id="user-chip"></span>
      </header>

      <main class="dashboard">
        <!-- Filled in by renderGreeting() in dashboard.js -->
        <section id="greeting"></section>

        <!-- Row 1: current status and clocked out -->
        <div class="row row--status">
          <section id="status-badge"></section>
          <section id="clocked-out"></section>
        </div>

        <!-- Hours today and this week -->
        <section id="hours-summary"></section>

        <!-- Row 2: chart and this week -->
        <div class="row row--overview">
          <section id="attendance-chart"></section>
          <section id="week-summary"></section>
        </div>

        <!-- Row 3: history and help -->
        <div class="row row--history">
          <section id="recent-records"></section>
          <section id="help-card"></section>
        </div>
      </main>

      <footer class="site-footer" id="site-footer"></footer>
    </div>
  </div>

  <script src="assets/js/dashboard.js"></script>
</body>
</html>
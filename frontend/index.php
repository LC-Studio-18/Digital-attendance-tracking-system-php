<?php require __DIR__ . '/partials/status-badge.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Clock screen | LC Attends</title>
  <link rel="icon" type="image/png" href="assets/img/lc-favicon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
  <!-- Replace status-card.css with the S2-04 shared styles/layout once they are in the repo -->
  <link rel="stylesheet" href="assets/css/status-card.css">
  <link rel="stylesheet" href="assets/css/status-badge.css">
  <script src="assets/js/status-api.js" defer></script>
  <script src="assets/js/status-badge.js" defer></script>
</head>
<body class="attends">
  <header class="attends__header"><img src="assets/img/lc-favicon.png" alt=""><span>LC Attends</span></header>
  <main class="attends__main">
    <h1>Clock in / out</h1>

    <div class="status-card">
      <?php render_status_badge(['show_detail' => true]); ?>
    </div>

    <!-- Existing clock-in / clock-out UI goes here.
         After the backend confirms a clock action succeeded, call:
           StatusBadge.notifyClockSuccess('in');   // or 'out'
         Do not call it when the action fails. -->
  </main>
</body>
</html>

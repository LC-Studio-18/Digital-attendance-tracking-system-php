<?php require __DIR__ . '/partials/status-badge.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Clock screen</title>
  <!-- Replace with the S2-04 shared styles/layout once you have them in the repo -->
  <link rel="stylesheet" href="assets/css/status-badge.css">
  <script src="assets/js/status-api.js" defer></script>
  <script src="assets/js/status-badge.js" defer></script>
</head>
<body>
  <main>
    <h1>Clock in / out</h1>
    <p>Current status: <?php render_status_badge(); ?></p>

    <!-- Existing clock-in / clock-out UI goes here.
         After the backend confirms a clock action succeeded, call:
           StatusBadge.notifyClockSuccess('in');   // or 'out'
         Do not call it when the action fails. -->
  </main>
</body>
</html>

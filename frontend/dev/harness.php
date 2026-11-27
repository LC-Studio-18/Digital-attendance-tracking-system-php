<?php
/** TEMPORARY test page for the badge. Do NOT commit or deploy. */
require __DIR__ . '/../partials/status-badge.php';

$allowed = ['', 'slow', 'error', 'invalid', 'badjson'];
$force   = in_array($_GET['force'] ?? '', $allowed, true) ? ($_GET['force'] ?? '') : '';
$failClock = isset($_GET['clockfail']);
$statusUrl = 'stub.php?action=status' . ($force ? '&force=' . $force : '');
$clockSuffix = $failClock ? '&force=fail' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Badge test harness</title>
  <link rel="icon" type="image/png" href="../assets/img/lc-favicon.png">
  <link rel="stylesheet" href="../assets/css/status-card.css">
  <link rel="stylesheet" href="../assets/css/status-badge.css">
  <script src="../assets/js/status-api.js" defer></script>
  <script src="../assets/js/status-badge.js" defer></script>
  <style>button{font:inherit;padding:.5rem 1rem;margin-right:.5rem}</style>
</head>
<body class="attends"><main class="attends__main">
  <h1>Badge test harness</h1>
  <div class="status-card"><?php render_status_badge(['endpoint' => $statusUrl, 'show_detail' => true]); ?></div>
  <p>
    <button id="in">Clock in</button>
    <button id="out">Clock out</button>
  </p>
  <p id="msg" role="status"></p>

  <h2>Scenarios (reload with)</h2>
  <ul>
    <li><a href="harness.php">Normal</a></li>
    <li><a href="harness.php?force=slow">Slow response (see "Checking...")</a></li>
    <li><a href="harness.php?force=error">Server error</a></li>
    <li><a href="harness.php?force=invalid">Invalid data</a></li>
    <li><a href="harness.php?force=badjson">Not JSON</a></li>
    <li><a href="harness.php?clockfail=1">Clock actions fail</a> (badge must not change)</li>
  </ul>

  <script>
    var suffix = <?= json_encode($clockSuffix) ?>;
    function clock(action) {
      var msg = document.getElementById('msg');
      fetch('stub.php?action=' + (action === 'in' ? 'clockin' : 'clockout') + suffix, { credentials: 'same-origin' })
        .then(function (r) { if (!r.ok) throw new Error('failed'); return r.json(); })
        .then(function () { msg.textContent = 'Clock ' + action + ' succeeded.'; StatusBadge.notifyClockSuccess(action); })
        .catch(function () { msg.textContent = 'Clock ' + action + ' failed.'; });
    }
    document.getElementById('in').onclick = function () { clock('in'); };
    document.getElementById('out').onclick = function () { clock('out'); };
  </script>
</main></body>
</html>

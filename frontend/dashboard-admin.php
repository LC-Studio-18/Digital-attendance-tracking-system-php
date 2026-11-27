<?php
// S2-16b - Live onsite dashboard page (admin).
// Admin only: anyone else is redirected to login. Data comes from the JSON file below
// (dummy for now; point $dataUrl at Elijah's endpoint when it is ready, same JSON shape).
session_start();
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
$adminName = htmlspecialchars($_SESSION['name'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
$initials  = strtoupper(mb_substr($adminName, 0, 1) . (mb_substr(strrchr(' ' . $adminName, ' '), 1, 1) ?: ''));
$dataUrl   = 'assets/data/onsite-dummy.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Live Onsite Dashboard</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/component.css">

    <link rel="stylesheet" href="assets/css/dashboard-admin.css">
</head>
<body>
<div class="f4-dashboard-admin">
 <aside class="f4-dashboard-admin__side">
  <div class="f4-dashboard-admin__brand f4-dashboard-admin__brand--sm">
    <img class="f4-dashboard-admin__brand-icon" src="assets/img/lc-attends-icon.png" alt="">
    <span class="f4-dashboard-admin__brand-text"><span class="f4-dashboard-admin__brand-lc">LC</span> <span class="f4-dashboard-admin__brand-attends">Attends</span></span>
</div>
  <nav class="f4-dashboard-admin__nav" aria-label="Main">
   <a class="f4-dashboard-admin__link--active" href="dashboard-admin.php" aria-current="page"><svg class="f4-dashboard-admin__ic" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>Dashboard</a>
   <a href="attendance-log.php"><svg class="f4-dashboard-admin__ic" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>Attendance log</a>
   <a href="staff-directory.php"><svg class="f4-dashboard-admin__ic" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6M16 4.5a3.5 3.5 0 010 7M18 14.3c2.2.7 3.5 2.6 3.5 5.7"/></svg>Staff Directory</a>
  </nav>
  <a class="f4-dashboard-admin__btn" href="add-employee.php" style="text-align:center;margin-top:30px">+ Add Employee</a>
  <div class="f4-dashboard-admin__access"><b>Admin Access</b><br><strong>Admin</strong><br>Life Choices Lansdowne<br><span class="f4-dashboard-admin__live">Live</span></div>
  <div class="f4-dashboard-admin__me"><span class="f4-dashboard-admin__av"><?= $initials ?></span><span><?= $adminName ?><br><small style="color:#9fb3b8">Admin</small></span></div>
  <a class="f4-dashboard-admin__logout" href="logout.php"><svg class="f4-dashboard-admin__ic" viewBox="0 0 24 24"><path d="M9 4H5a1 1 0 00-1 1v14a1 1 0 001 1h4M16 8l4 4-4 4M20 12H9"/></svg>Logout</a>
 </aside>

 <main class="f4-dashboard-admin__main">
    <div class="f4-dashboard-admin__top">
   <span class="f4-dashboard-admin__crumb">Workspace / Overview</span>
   <button class="f4-dashboard-admin__bell" aria-label="Notifications"><svg class="f4-dashboard-admin__ic" viewBox="0 0 24 24" style="width:22px;height:22px"><path d="M6 17V11a6 6 0 0112 0v6l2 2H4l2-2zM10 21h4"/></svg></button>
   <span class="f4-dashboard-admin__av" title="<?= $adminName ?>"><?= $initials ?></span>
  </div>
  <div class="f4-dashboard-admin__date" id="f4-dashboard-admin-date" style="margin-top:12px"></div>
  <h1 id="f4-dashboard-admin-greet"><span id="f4-dashboard-admin-greet-text">Good morning</span>, <?= $adminName ?>.</h1> 
  <div class="f4-dashboard-admin__sub">
   <p id="f4-dashboard-admin-lead">All systems are operational. You have 4 pending approvals and 2 new user sign-ups today</p>
   <div><a class="f4-dashboard-admin__btn" href="#f4-dashboard-admin-staff">View Staff Attendance</a> <a class="f4-dashboard-admin__btn f4-dashboard-admin__button--secondary" href="attendance-log.php">Attendance Log</a></div>
  </div>

  <div id="f4-dashboard-admin-banner" class="f4-dashboard-admin__banner" role="status" hidden></div>

  <section class="f4-dashboard-admin__stats" aria-label="Summary">
   <div class="f4-dashboard-admin__stat"><small>Currently Onsite</small><strong id="f4-dashboard-admin-s-on">–</strong><span>People in the Building</span></div>
   <div class="f4-dashboard-admin__stat"><small>Offsite</small><strong id="f4-dashboard-admin-s-off">–</strong><span>Clocked Out Today</span></div>
   <div class="f4-dashboard-admin__stat"><small>Not Checked In</small><strong id="f4-dashboard-admin-s-nci">–</strong><span>No Entry Recorded Today</span></div>
   <div class="f4-dashboard-admin__stat"><small>Late Comings</small><strong id="f4-dashboard-admin-s-late">–</strong><span>People LATE today</span></div>
  </section>

  <div class="f4-dashboard-admin__grid">
   <section class="f4-dashboard-admin__card" id="f4-dashboard-admin-staff" aria-labelledby="f4-dashboard-admin-h-staff">
    <h2 id="f4-dashboard-admin-h-staff">Staff Attendance </h2>
    <div class="f4-dashboard-admin__tabs" role="tablist" id="f4-dashboard-admin-tabs">
     <button role="tab" data-t="all" aria-selected="true">All</button>
     <button role="tab" data-t="present" aria-selected="false">Present</button>
     <button role="tab" data-t="absent" aria-selected="false">Absent</button>
     <button role="tab" data-t="not_checked_in" aria-selected="false">Not Checked In</button>
     <button role="tab" data-t="away" aria-selected="false">Away</button>
    </div>
    <div class="f4-dashboard-admin__tw" aria-live="polite" aria-busy="true">
     <table><thead><tr><th>Member</th><th>Department</th><th>Status</th><th>In</th><th>Out</th></tr></thead><tbody id="f4-dashboard-admin-rows"></tbody></table>
     <div id="f4-dashboard-admin-state" class="f4-dashboard-admin__state"></div>
    </div>
   </section>
   <section class="f4-dashboard-admin__card" aria-labelledby="f4-dashboard-admin-h-act">
    <h2 id="f4-dashboard-admin-h-act">Recent Activity <span class="f4-dashboard-admin__tag" id="f4-dashboard-admin-live-tag" title="Not updated yet">Live</span></h2>
    <ul class="f4-dashboard-admin__feed" id="f4-dashboard-admin-feed"></ul>
    <div id="f4-dashboard-admin-feed-state" class="f4-dashboard-admin__state" hidden>No activity yet today.</div>
   </section>
  </div>

  <div class="f4-dashboard-admin__grid2">
   <section class="f4-dashboard-admin__card"><h2>Attendance this week</h2><div class="f4-dashboard-admin__chart" id="f4-dashboard-admin-bars"></div><div class="f4-dashboard-admin__wk-leg" id="f4-dashboard-admin-wk-leg"></div></section>
   <section class="f4-dashboard-admin__card"><h2>Departments</h2><div class="f4-dashboard-admin__pie" id="f4-dashboard-admin-pie"></div><ul class="f4-dashboard-admin__legend" id="f4-dashboard-admin-legend"></ul></section>
  </div>

  <div class="f4-dashboard-admin__grid2" style="margin-top:16px">
   <section class="f4-dashboard-admin__card f4-dashboard-admin__early" aria-labelledby="f4-dashboard-admin-h-early">
    <div class="f4-dashboard-admin__early-h"><h2 id="f4-dashboard-admin-h-early">Weekly early arrivals report</h2><a class="f4-dashboard-admin__btn" href="export-csv.php?report=early-arrivals">Export ↗</a><p id="f4-dashboard-admin-early-sub"></p></div>
    <div class="f4-dashboard-admin__tw"><table><thead><tr><th>#</th><th>Member</th><th>Department</th><th>Early days</th><th>Earliest Arrival</th></tr></thead><tbody id="f4-dashboard-admin-early-rows"></tbody></table></div>
    <div id="f4-dashboard-admin-early-state" class="f4-dashboard-admin__empty-s" hidden>No early arrivals recorded this week.</div>
   </section>
   <section class="f4-dashboard-admin__card f4-dashboard-admin__sheets" aria-labelledby="f4-dashboard-admin-h-sheets">
    <div style="display:flex;justify-content:space-between;align-items:center"><h3 id="f4-dashboard-admin-h-sheets">Google Sheets</h3><span class="f4-dashboard-admin__tag f4-dashboard-admin__tag--ok" id="f4-dashboard-admin-sh-status">◉ Connected</span></div>
    <small id="f4-dashboard-admin-sh-sync">Not synced yet</small>
    <p><strong style="color:var(--ink)">Your attendance, backed up.</strong></p>
    <p>Attendance records sync automatically to your organisation's spreadsheet</p>
    <a class="f4-dashboard-admin__btn" id="f4-dashboard-admin-sh-open" href="#" target="_blank" rel="noopener">Open Spreadsheet ↗</a>
   </section>
  </div>

  <footer>
   <div class="f4-dashboard-admin__fl">
    <div class="f4-dashboard-admin__brand">
    <img class="f4-dashboard-admin__brand-icon" src="assets/img/lc-attends-icon.png" alt="">
    <span class="f4-dashboard-admin__brand-text"><span class="f4-dashboard-admin__brand-lc">LC</span> <span class="f4-dashboard-admin__brand-attends">Attends</span></span>
</div>
    <p>Life Choices invests in young people from all over South Africa, so every one of them has a fair chance in life.</p>
   </div>
   <div class="f4-dashboard-admin__fr"><h4>VISIT OR CONTACT US</h4><p>314 Imam Haron Rd, Lansdowne,<br>Cape Town, 7780</p><p>+27 (0) 21 696 4157</p><p>theteam@lcstudio.co.za</p><p>www.lcstudio.co.za</p><small>Admin access only - LC Attends System</small></div>
  </footer>
 </main>
 <nav class="f4-dashboard-admin__bn" aria-label="Main mobile"><a class="f4-dashboard-admin__link--active" href="dashboard-admin.php">Dashboard</a><a href="attendance-log.php">Attendance</a><a href="staff-directory.php">Staff</a></nav>
</div>
    <script>window.F4_ONSITE_URL = <?= json_encode($dataUrl) ?>;</script>
    <script src="assets/js/dashboard-admin.js"></script>
</body>
</html>
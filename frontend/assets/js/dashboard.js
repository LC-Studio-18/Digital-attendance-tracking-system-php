/*
 * dashboard.js
 * Builds the Employee Dashboard on the page.
 *
 * Each render function takes one piece of the API data and writes it into
 * its own section of dashboard.php. The field names (status, hours.today,
 * hours.week) come from the JSON contract in the Team 3 brief.
 */

// ---------- Icons ----------
// Small line icons drawn as SVG. icon("clock", 24) returns the <svg> text.
const ICON_PATHS = {
  calendar: '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
  clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
  bell: '<path d="M6 16v-5a6 6 0 0 1 12 0v5l2 2H4z"/><path d="M10 21h4"/>',
  bars: '<path d="M5 20v-8M12 20V4M19 20v-11"/>',
  list: '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>',
  headset: '<path d="M4 15v-3a8 8 0 0 1 16 0v3"/><rect x="3" y="14" width="4" height="6" rx="1.5"/><rect x="17" y="14" width="4" height="6" rx="1.5"/>',
  mail: '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
  arrow: '<path d="M5 12h14M13 6l6 6-6 6"/>',
  check: '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
  dash: '<path d="M6 12h12"/>',
  chevron: '<path d="M6 9l6 6 6-6"/>'
};

function icon(name, size) {
  const px = size || 20;
  return `<svg class="icon" width="${px}" height="${px}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${ICON_PATHS[name]}</svg>`;
}

// ---------- Status card ----------

// Status card: the big green IN card (or grey OUT card).
// Inputs: status ("IN" or "OUT"), clockedInAt (text such as "09:15 AM"),
//         dateText (text such as "Today, 24 Mar 2025")
// Writes into: <section id="status-badge">
function renderStatusBadge(status, clockedInAt, dateText) {
  const el = document.getElementById("status-badge");
  const isIn = status === "IN";

  // The class decides the colour (see component.css)
  el.className = "status-card " + (isIn ? "status-card--in" : "status-card--out");
  el.innerHTML = `
    <div class="status-card__icon">${icon(isIn ? "check" : "dash", 30)}</div>
    <div class="status-card__main">
      <span class="status-card__label">Current Status</span>
      <span class="status-card__value">${isIn ? "IN" : "OUT"}</span>
      <span class="status-card__pill"><span class="status-card__dot"></span>${isIn ? "Clocked In" : "Clocked Out"}</span>
    </div>
    <div class="status-card__time">
      <span class="status-card__label">Clocked In At</span>
      <span class="status-card__value status-card__value--time">${clockedInAt || "--:--"}</span>
      <span class="status-card__date">${dateText}</span>
    </div>
  `;
}

// ---------- Clocked Out card ----------

// Clocked Out card: shows when the person clocked out, or --:-- if still in.
// Inputs: clockedOutAt (text such as "04:30 PM", or null if the shift is open),
//         lastUpdated (text such as "24 Mar 2025, 09:15 AM")
// Writes into: <section id="clocked-out">
function renderClockedOut(clockedOutAt, lastUpdated) {
  const el = document.getElementById("clocked-out");

  el.className = "clocked-out-card";
  el.innerHTML = `
    <div class="clocked-out-card__top">
      <span class="clocked-out-card__icon">${icon("clock", 28)}</span>
      <div class="clocked-out-card__main">
        <span class="clocked-out-card__label">Clocked Out</span>
        <span class="clocked-out-card__time">${clockedOutAt || "--:--"}</span>
      </div>
      <span class="clocked-out-card__note">${clockedOutAt ? "Shift ended" : "Not yet clocked out"}</span>
    </div>
    <div class="clocked-out-card__updated">
      <div class="clocked-out-card__updated-text">
        <span class="clocked-out-card__updated-label">Last updated</span>
        <span class="clocked-out-card__updated-value">${lastUpdated}</span>
      </div>
      <span class="clocked-out-card__live"></span>
    </div>
  `;
}

// ---------- Hours summary ----------

// Hours summary: two cards, one for today and one for this week.
// Input: hours, an object like { today: 4.5, week: 22.25 }
//        (the values can also be text such as "4.50")
// Writes into: <section id="hours-summary">
function renderHoursSummary(hours) {
  const el = document.getElementById("hours-summary");

  // Number() converts text to a number, and toFixed(2) always shows
  // two decimals, for example 4.50
  el.innerHTML = `
    <div class="hours-card">
      <span class="hours-card__label">Today</span>
      <span class="hours-card__value">${Number(hours.today).toFixed(2)} h</span>
    </div>
    <div class="hours-card">
      <span class="hours-card__label">This week</span>
      <span class="hours-card__value">${Number(hours.week).toFixed(2)} h</span>
    </div>
  `;
}

// ---------- Recent records ----------

// Helper: turns "2026-11-25 08:00:00" into "Wed, 25 Nov 2026"
function formatRecordDate(dateTime) {
  const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
  const weekdays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
  const datePart = dateTime.split(" ")[0];
  const [year, month, day] = datePart.split("-").map(Number);
  // Date.UTC avoids timezone surprises when working out the weekday
  const weekday = weekdays[new Date(Date.UTC(year, month - 1, day)).getUTCDay()];
  return `${weekday}, ${day} ${months[month - 1]} ${year}`;
}

// Helper: turns "2026-11-25 08:00:00" into "08:00 AM" (or "--:--" if empty)
function formatRecordTime(dateTime) {
  if (!dateTime) return "--:--";
  const [hourText, minutes] = dateTime.split(" ")[1].split(":");
  const hour = Number(hourText);
  const suffix = hour >= 12 ? "PM" : "AM";
  const hour12 = hour % 12 || 12;
  return `${String(hour12).padStart(2, "0")}:${minutes} ${suffix}`;
}

// Helper: the coloured Late / Present pill. Returns "" if there is no status.
function statusPill(status) {
  if (!status) return "";
  const text = String(status);
  const lower = text.toLowerCase();
  let kind = "neutral";
  if (lower === "late") kind = "late";
  if (lower === "present") kind = "present";
  return `<span class="status-pill status-pill--${kind}"><span class="status-pill__dot"></span>${text}</span>`;
}

// Recent records: a table of the last 10 attendance records.
// Input: records, an array like
//   [{ record_id: 4, clock_in: "2026-11-25 08:00:00", clock_out: null,
//      hours: null, is_corrected: false, status: "Late" }]
// is_corrected can be true/false or 0/1. status ("Late" or "Present") is
// optional until the backend adds it: without it the pill is left out.
// Writes into: <section id="recent-records">
function renderRecentRecords(records) {
  const el = document.getElementById("recent-records");
  el.className = "records-card";

  const header = `
    <div class="records-card__header">
      <div class="records-card__heading">
        ${icon("list", 20)}
        <h2 class="records-card__title">Attendance History</h2>
      </div>
      <a href="#" class="records-card__viewall">View All ${icon("arrow", 14)}</a>
    </div>
  `;

  // No records yet
  if (!records || records.length === 0) {
    el.innerHTML = header + '<p class="records-card__empty">No records yet.</p>';
    return;
  }

  const rows = records.map(function (record) {
    // The database may send is_corrected as 0 or 1, so convert it first
    const isCorrected = Number(record.is_corrected) === 1;
    const tag = isCorrected ? '<span class="record__tag">Corrected</span>' : "";

    return `
      <li class="record ${isCorrected ? "record--corrected" : ""}">
        <div class="record__cell record__cell--date">${formatRecordDate(record.clock_in)} ${tag}</div>
        <div class="record__cell" data-label="Clock In">${formatRecordTime(record.clock_in)}</div>
        <div class="record__cell" data-label="Clocked Out">${formatRecordTime(record.clock_out)}</div>
        <div class="record__cell" data-label="Status">${statusPill(record.status)}</div>
      </li>
    `;
  });

  el.innerHTML = `
    ${header}
    <div class="records-head" aria-hidden="true">
      <span>Date</span><span>Clock In</span><span>Clocked Out</span><span>Status</span>
    </div>
    <ul class="records-list">${rows.join("")}</ul>
  `;
}

// ---------- Greeting and top bar ----------

// Greeting and top bar: "Good morning, Phelisa." plus the date and a user chip.
// Inputs: fullName (text such as "Phelisa Fani"), today (a JavaScript Date)
// Writes into: #greeting, #today-date, #user-chip
function renderGreeting(fullName, today) {
  const hour = today.getHours();
  const part = hour < 12 ? "morning" : hour < 18 ? "afternoon" : "evening";
  const firstName = fullName.split(" ")[0];

  // First letters of each name, e.g. "Phelisa Fani" becomes "PF"
  const initials = fullName
    .split(" ")
    .map(function (word) { return word[0]; })
    .join("")
    .toUpperCase();

  const dateText = today.toLocaleDateString("en-GB", {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric"
  });

  document.getElementById("greeting").innerHTML = `
    <h1 class="greeting__title">Good ${part}, ${firstName}.</h1>
    <p class="greeting__sub">Here’s your attendance overview for today.</p>
  `;
  document.getElementById("today-date").innerHTML = `
    ${icon("calendar", 20)}<span>${dateText}</span>
  `;
  document.getElementById("user-chip").innerHTML = `
    <span class="user-chip__bell">${icon("bell", 20)}</span>
    <span class="user-chip__avatar">${initials}</span>
    <span class="user-chip__name">${fullName}</span>
    <span class="user-chip__chevron">${icon("chevron", 16)}</span>
  `;
  
  // The user card in the sidebar (only exists on larger screens)
  const sidebarUser = document.getElementById("sidebar-user");
  if (sidebarUser) {
    sidebarUser.innerHTML = `
      <span class="sidebar-user__avatar">${initials}</span>
      <div class="sidebar-user__text">
        <span class="sidebar-user__name">${fullName}</span>
        <span class="sidebar-user__role">Employee</span>
      </div>
    `;
  }
}

// ---------- This Week card ----------

// This Week summary: days present out of working days, plus an attendance rate ring.
// Inputs: daysPresent (number, e.g. 5), workingDays (number, e.g. 5)
// Writes into: <section id="week-summary">
function renderWeekSummary(daysPresent, workingDays) {
  const el = document.getElementById("week-summary");
  const rate = workingDays > 0 ? Math.round((daysPresent / workingDays) * 100) : 0;

  el.className = "week-card";
  el.innerHTML = `
    <div class="week-card__header">
      ${icon("calendar", 20)}
      <h2 class="week-card__title">This Week</h2>
    </div>
    <div class="week-card__big">${daysPresent} / ${workingDays}</div>
    <div class="week-card__label">Days Present</div>
    <hr class="week-card__divider">
    <div class="week-card__rate-row">
      <div class="week-card__ring" style="--rate: ${rate}">
        <span class="week-card__rate">${rate}%</span>
      </div>
      <span class="week-card__rate-label">Attendance Rate</span>
    </div>
  `;
}

// ---------- Attendance chart ----------

// Attendance chart: a smooth area chart, one point per day.
// Input: days, an array like [{ label: "Mar 18", value: 45 }, ...]
//        where value is the attendance from 0 to 100.
// Writes into: <section id="attendance-chart">
function renderAttendanceChart(days) {
  const el = document.getElementById("attendance-chart");
  el.className = "chart-card";

  const header = `
    <div class="chart-card__header">
      <div class="chart-card__heading">
        ${icon("bars", 20)}
        <h2 class="chart-card__title">Attendance Overview</h2>
      </div>
      <select class="chart-card__filter" aria-label="Time range">
        <option>This Month</option>
      </select>
    </div>
  `;

  // No data: show the header and a short message
  if (!days || days.length === 0) {
    el.innerHTML = header + '<p class="records-card__empty">No data yet.</p>';
    return;
  }

  // Size of the drawing and the space kept for the labels
  const width = 600;
  const height = 230;
  const left = 40;
  const right = 40;
  const top = 12;
  const bottom = 30;
  const plotW = width - left - right;
  const plotH = height - top - bottom;
  const baseY = top + plotH;

  // Turn each day into an x and y position on the drawing
  const points = days.map(function (day, i) {
    const value = Math.min(100, Math.max(0, Number(day.value)));
    return {
      x: left + (days.length > 1 ? (i * plotW) / (days.length - 1) : plotW / 2),
      y: baseY - (value / 100) * plotH,
      label: day.label
    };
  });

  // The smooth line: a curve from each point to the next
  let line = `M ${points[0].x} ${points[0].y}`;
  for (let i = 1; i < points.length; i++) {
    const prev = points[i - 1];
    const cur = points[i];
    const midX = (prev.x + cur.x) / 2;
    line += ` C ${midX} ${prev.y}, ${midX} ${cur.y}, ${cur.x} ${cur.y}`;
  }

  // The filled area: the same line, closed along the bottom
  const area = `${line} L ${points[points.length - 1].x} ${baseY} L ${points[0].x} ${baseY} Z`;

  // Horizontal grid lines with the 0 to 100 numbers
  const gridLines = [0, 25, 50, 75, 100].map(function (tick) {
    const y = baseY - (tick / 100) * plotH;
    return `
      <line class="chart__grid" x1="${left}" x2="${width - right}" y1="${y}" y2="${y}"></line>
      <text class="chart__axis" x="${left - 8}" y="${y + 4}" text-anchor="end">${tick}</text>
    `;
  });

  // A dot on each point and the day label underneath
  const dots = points.map(function (p) {
    return `<circle class="chart__dot" cx="${p.x}" cy="${p.y}" r="4"></circle>`;
  });
  const labels = points.map(function (p) {
    return `<text class="chart__axis" x="${p.x}" y="${height - 8}" text-anchor="middle">${p.label}</text>`;
  });

  el.innerHTML = `
    ${header}
    <svg class="chart__svg" viewBox="0 0 ${width} ${height}" role="img" aria-label="Attendance overview chart">
      ${gridLines.join("")}
      <path class="chart__area" d="${area}"></path>
      <path class="chart__line" d="${line}"></path>
      ${dots.join("")}
      ${labels.join("")}
    </svg>
  `;
}

// ---------- Need Help card ----------

// Need Help card: a short message and a Contact HR button. No inputs.
// Writes into: <section id="help-card">
function renderHelpCard() {
  const el = document.getElementById("help-card");

  el.className = "help-card";
  el.innerHTML = `
    <span class="help-card__icon">${icon("headset", 40)}</span>
    <h2 class="help-card__title">Need Help?</h2>
    <p class="help-card__text">Contact your HR department for assistance.</p>
    <a href="#" class="help-card__button">${icon("mail", 16)}Contact HR</a>
  `;
}

// ---------- Footer ----------

// Footer: the LC Attends logo text, a short message and the contact details.
// No inputs. Writes into: <footer id="site-footer">
function renderFooter() {
  const el = document.getElementById("site-footer");

  el.innerHTML = `
    <div class="footer__inner">
      <div class="footer__brand">
        <span class="footer__logo">LC Attends</span>
        <p class="footer__message">Life Choices invests in young people from all over South Africa, so every one of them has a fair chance in life.</p>
      </div>
      <div class="footer__contact">
        <span class="footer__heading">Visit or contact us</span>
        <address class="footer__address">
          314 Imam Haron Rd, Lansdowne,<br>
          Cape Town, 7780<br>
          +27 (0) 21 696 4157<br>
          theteam@lcstudio.co.za<br>
          www.lcstudio.co.za
        </address>
        <span class="footer__small">Employee access only · LC Attends System</span>
      </div>
    </div>
  `;
}

// ---------- Dummy data ----------
// Imaan will replace these calls with the real values from GET /api/dashboard.
renderGreeting("Phelisa Fani", new Date());
renderStatusBadge("IN", "09:15 AM", "Today, 24 Mar 2025");
renderClockedOut(null, "24 Mar 2025, 09:15 AM");
renderHoursSummary({ today: 4.5, week: 22.25 });

renderAttendanceChart([
  { label: "Mar 18", value: 45 },
  { label: "Mar 19", value: 58 },
  { label: "Mar 20", value: 75 },
  { label: "Mar 21", value: 75 },
  { label: "Mar 22", value: 65 },
  { label: "Mar 23", value: 87 },
  { label: "Mar 24", value: 82 }
]);
renderWeekSummary(5, 5);

renderRecentRecords([
  { record_id: 4, clock_in: "2026-11-24 09:15:00", clock_out: null, hours: null, is_corrected: false, status: "Late" },
  { record_id: 3, clock_in: "2026-11-23 08:30:00", clock_out: "2026-11-23 16:30:00", hours: 8, is_corrected: true, status: "Present" },
  { record_id: 2, clock_in: "2026-11-22 08:25:00", clock_out: "2026-11-22 16:30:00", hours: 8.08, is_corrected: false, status: "Present" },
  { record_id: 1, clock_in: "2026-11-21 09:10:00", clock_out: "2026-11-21 16:30:00", hours: 7.33, is_corrected: false, status: "Late" }
]);
renderHelpCard();
renderFooter();
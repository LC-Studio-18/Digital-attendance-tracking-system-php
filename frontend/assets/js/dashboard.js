/*
 * dashboard.js
 * Builds the Employee Dashboard on the page.
 *
 * Each render function takes one piece of the API data and writes it into
 * its own section of dashboard.php. The field names (status, hours.today,
 * hours.week) come from the JSON contract in the Team 3 brief.
 */

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
    <div class="status-card__icon">${isIn ? "&#10003;" : "&#8212;"}</div>
    <div class="status-card__main">
      <span class="status-card__label">Current Status</span>
      <span class="status-card__value">${isIn ? "IN" : "OUT"}</span>
      <span class="status-card__pill">${isIn ? "Clocked In" : "Clocked Out"}</span>
    </div>
    <div class="status-card__time">
      <span class="status-card__label">Clocked In At</span>
      <span class="status-card__value status-card__value--time">${clockedInAt || "--:--"}</span>
      <span class="status-card__date">${dateText}</span>
    </div>
  `;
}

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

// Clocked Out card: shows when the person clocked out, or --:-- if still in.
// Inputs: clockedOutAt (text such as "04:30 PM", or null if the shift is open),
//         lastUpdated (text such as "24 Mar 2025, 09:15 AM")
// Writes into: <section id="clocked-out">
function renderClockedOut(clockedOutAt, lastUpdated) {
  const el = document.getElementById("clocked-out");

  el.className = "clocked-out-card";
  el.innerHTML = `
    <span class="clocked-out-card__label">Clocked Out</span>
    <span class="clocked-out-card__time">${clockedOutAt || "--:--"}</span>
    <span class="clocked-out-card__note">${clockedOutAt ? "Shift ended" : "Not yet clocked out"}</span>
    <span class="clocked-out-card__updated">Last updated ${lastUpdated}</span>
  `;
}

// Helper: turns "2026-11-25 08:00:00" into "25 Nov 2026"
function formatRecordDate(dateTime) {
  const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
  const datePart = dateTime.split(" ")[0];
  const [year, month, day] = datePart.split("-").map(Number);
  return `${day} ${months[month - 1]} ${year}`;
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

// Recent records: a list of the last 10 attendance records.
// Input: records, an array like
//   [{ record_id: 4, clock_in: "2026-11-25 08:00:00", clock_out: null,
//      hours: null, is_corrected: false }]
// hours can be a number or text, and is_corrected can be true/false or 0/1.
// Writes into: <section id="recent-records">
function renderRecentRecords(records) {
  const el = document.getElementById("recent-records");
  el.className = "records-card";

  // No records yet
  if (!records || records.length === 0) {
    el.innerHTML = `
      <h2 class="records-card__title">Attendance History</h2>
      <p class="records-card__empty">No records yet.</p>
    `;
    return;
  }

  const rows = records.map(function (record) {
    // An open shift has no clock-out and no hours yet
    const clockOut = record.clock_out ? formatRecordTime(record.clock_out) : "Still in";

    // The database may send hours as text ("8.42") and is_corrected as 0 or 1,
    // so convert them before using them
    const hasHours = record.hours !== null && record.hours !== undefined;
    const hours = hasHours ? Number(record.hours).toFixed(2) + " h" : "--";
    const isCorrected = Number(record.is_corrected) === 1;
    const tag = isCorrected
      ? '<span class="record__tag">Corrected</span>'
      : "";

    return `
      <li class="record ${isCorrected ? "record--corrected" : ""}">
        <div class="record__date">${formatRecordDate(record.clock_in)} ${tag}</div>
        <div class="record__times">
          <span>In: ${formatRecordTime(record.clock_in)}</span>
          <span>Out: ${clockOut}</span>
          <span>${hours}</span>
        </div>
      </li>
    `;
  });

  el.innerHTML = `
    <h2 class="records-card__title">Attendance History</h2>
    <ul class="records-list">${rows.join("")}</ul>
  `;
}

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
    <p class="greeting__sub">Here's your attendance overview.</p>
  `;
  document.getElementById("today-date").textContent = dateText;
  document.getElementById("user-chip").innerHTML = `
    <span class="user-chip__avatar">${initials}</span>
    <span class="user-chip__name">${fullName}</span>
  `;
}

// This Week summary: days present out of working days, plus an attendance rate ring.
// Inputs: daysPresent (number, e.g. 5), workingDays (number, e.g. 5)
// Writes into: <section id="week-summary">
function renderWeekSummary(daysPresent, workingDays) {
  const el = document.getElementById("week-summary");
  const rate = workingDays > 0 ? Math.round((daysPresent / workingDays) * 100) : 0;

  el.className = "week-card";
  el.innerHTML = `
    <h2 class="week-card__title">This Week</h2>
    <div class="week-card__body">
      <div class="week-card__days">
        <span class="week-card__big">${daysPresent}/${workingDays}</span>
        <span class="week-card__label">days present</span>
      </div>
      <div class="week-card__ring" style="--rate: ${rate}">
        <span class="week-card__rate">${rate}%</span>
      </div>
    </div>
    <p class="week-card__note">Attendance rate</p>
  `;
}

// Attendance chart: a smooth area chart, one point per day.
// Input: days, an array like [{ label: "Mar 18", value: 45 }, ...]
//        where value is the attendance from 0 to 100.
// Writes into: <section id="attendance-chart">
function renderAttendanceChart(days) {
  const el = document.getElementById("attendance-chart");
  el.className = "chart-card";

  const header = `
    <div class="chart-card__header">
      <h2 class="chart-card__title">Attendance Overview</h2>
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
  const right = 24;
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

// Dummy data for now. Imaan will replace these two lines with a call
// that passes in the real values from GET /api/dashboard.
renderStatusBadge("IN", "09:15 AM", "Today, 24 Mar 2025");
renderHoursSummary({ today: 4.5, week: 22.25 });
renderWeekSummary(5, 5);
renderClockedOut(null, "24 Mar 2025, 09:15 AM");

renderRecentRecords([
  { record_id: 4, clock_in: "2026-11-24 09:15:00", clock_out: null, hours: null, is_corrected: false },
  { record_id: 3, clock_in: "2026-11-23 08:30:00", clock_out: "2026-11-23 16:30:00", hours: 8, is_corrected: true },
  { record_id: 2, clock_in: "2026-11-22 08:25:00", clock_out: "2026-11-22 16:30:00", hours: 8.08, is_corrected: false }
]);

renderGreeting("Phelisa Fani", new Date());
renderWeekSummary(5, 5);
renderAttendanceChart([
  { label: "Mar 18", value: 45 },
  { label: "Mar 19", value: 58 },
  { label: "Mar 20", value: 75 },
  { label: "Mar 21", value: 75 },
  { label: "Mar 22", value: 65 },
  { label: "Mar 23", value: 87 },
  { label: "Mar 24", value: 82 }
]);
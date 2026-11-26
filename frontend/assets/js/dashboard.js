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
// Writes into: <section id="hours-summary">
function renderHoursSummary(hours) {
  const el = document.getElementById("hours-summary");

  // toFixed(2) always shows two decimals, for example 4.50
  el.innerHTML = `
    <div class="hours-card">
      <span class="hours-card__label">Today</span>
      <span class="hours-card__value">${hours.today.toFixed(2)} h</span>
    </div>
    <div class="hours-card">
      <span class="hours-card__label">This week</span>
      <span class="hours-card__value">${hours.week.toFixed(2)} h</span>
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
    const hours = record.hours !== null ? record.hours.toFixed(2) + " h" : "--";
    const tag = record.is_corrected
      ? '<span class="record__tag">Corrected</span>'
      : "";

    return `
      <li class="record ${record.is_corrected ? "record--corrected" : ""}">
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

// Dummy data for now. Imaan will replace these two lines with a call
// that passes in the real values from GET /api/dashboard.
renderStatusBadge("IN", "09:15 AM", "Today, 24 Mar 2025");
renderHoursSummary({ today: 4.5, week: 22.25 });
renderClockedOut(null, "24 Mar 2025, 09:15 AM");

renderRecentRecords([
  { record_id: 4, clock_in: "2026-11-24 09:15:00", clock_out: null, hours: null, is_corrected: false },
  { record_id: 3, clock_in: "2026-11-23 08:30:00", clock_out: "2026-11-23 16:30:00", hours: 8, is_corrected: true },
  { record_id: 2, clock_in: "2026-11-22 08:25:00", clock_out: "2026-11-22 16:30:00", hours: 8.08, is_corrected: false }
]);
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

// Dummy data for now. Imaan will replace these two lines with a call
// that passes in the real values from GET /api/dashboard.
renderStatusBadge("IN", "09:15 AM", "Today, 24 Mar 2025");
renderHoursSummary({ today: 4.5, week: 22.25 });
renderClockedOut(null, "24 Mar 2025, 09:15 AM");
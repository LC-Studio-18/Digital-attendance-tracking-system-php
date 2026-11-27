// ---- Dummy data switch: set USE_DUMMY to false ONLY after the go-ahead ----
const USE_DUMMY = true;
const BASE_URL = "";                    // real API base URL goes here
const ENDPOINT = "/attendance/history"; // confirm with Khanya G (S2-17a)

// URL parameter names (send these to Zaarah for the Export CSV button)
const FILTERS = ["search", "department", "status", "in", "out", "from", "to"];
const COLOURS = ["#8f1d1d", "#2d3a7a", "#3b8a2a", "#f08a1a", "#5b5bd6", "#b02ab0", "#b0182a", "#e5333a"];

const $ = (id) => document.getElementById(id);
const esc = (s) => String(s ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
const urlParams = new URLSearchParams(location.search);
const state = Object.fromEntries(FILTERS.map((k) => [k, urlParams.get(k) || ""]));
let data = null;

function syncUrl() {
  const p = new URLSearchParams();
  FILTERS.forEach((k) => state[k] && p.set(k, state[k]));
  const qs = p.toString();
  history.replaceState(null, "", qs ? "?" + qs : location.pathname);
}

async function load() {
  const url = USE_DUMMY ? "assets/data/attendance-history.json" : BASE_URL + ENDPOINT + location.search;
  const res = await fetch(url);
  if (!res.ok) throw new Error("HTTP " + res.status);
  return res.json();
}

// Only used with dummy data. With the real endpoint the backend does the filtering.
function applyFilters(records) {
  const q = state.search.trim().toLowerCase();
  return records.filter((r) =>
    (!q || r.name.toLowerCase().includes(q) || r.staff_id.toLowerCase().includes(q)) &&
    (!state.department || r.department === state.department) &&
    (!state.status || r.status === state.status) &&
    (!state.in || (r.clock_in && r.clock_in >= state.in)) &&
    (!state.out || (r.clock_out && r.clock_out <= state.out)) &&
    (!state.from || r.date >= state.from) &&
    (!state.to || r.date <= state.to));
}

const initials = (n) => { const p = n.trim().split(/\s+/); return (p[0][0] + p[p.length - 1][0]).toUpperCase(); };
// Avatar colours copied from the Figma for the dummy staff; any other staff get a colour from their ID.
const AVATARS = { "LC-322": "#8f1d1d", "LC-245": "#2d3a7a", "LC-298": "#3b8a2a", "LC-320": "#f08a1a", "LC-210": "#5b5bd6", "LC-324": "#b02ab0", "LC-213": "#b0182a", "LC-145": "#e5333a" };
const colour = (id) => AVATARS[id] || COLOURS[[...id].reduce((a, c) => a + c.charCodeAt(0), 0) % COLOURS.length];

function statusCell(r) {
  if (r.status === "onsite") return '<span class="pill">Onsite</span>';
  if (r.status === "offsite") return '<span class="pill off">Offsite</span>';
  return '<span class="muted">Not Checked In</span>';
}

function render() {
  const rows = USE_DUMMY ? applyFilters(data.records) : data.records;
  $("records").innerHTML = rows.map((r) => {
    const inTime = r.clock_in ? (r.late ? '<span class="late">' + esc(r.clock_in) + "</span>" : esc(r.clock_in)) : "&mdash;";
    const recordId = esc(r.record_id ?? "");
    const editButton = '<button class="edit-time-row-button" type="button" data-edit-time-open data-record-id="' + recordId + '"' +
      ' data-edit-time-staff-name="' + esc(r.name) + '"' +
      ' data-edit-time-staff-id="' + esc(r.staff_id) + '"' +
      ' data-edit-time-clock-in="' + esc(r.clock_in || "") + '"' +
      ' data-edit-time-clock-out="' + esc(r.clock_out || "") + '"' +
      ' aria-haspopup="dialog" aria-controls="edit-time-modal"' +
      ' aria-label="' + esc("Edit attendance times for " + r.name + " (" + r.staff_id + ")") + '">' +
      '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">' +
      '<path d="m14.7 5.3 4 4M4 20l4.5-1 10.1-10.1a2.1 2.1 0 0 0-3-3L5.5 16 4 20Z"></path>' +
      "</svg></button>";
    return '<li class="rec" data-record-id="' + recordId + '"><span class="av" style="background:' + colour(r.staff_id) + '">' + esc(initials(r.name)) + "</span>" +
      '<div class="who"><strong>' + esc(r.name) + "</strong><small>" + esc(r.staff_id) + "</small></div>" +
      '<span class="dept">' + esc(r.department) + '</span><div class="st">' + statusCell(r) + "</div>" +
      '<div class="c"><span class="lbl">In</span>' + inTime + "</div>" +
      '<div class="c"><span class="lbl">Out</span>' + esc(r.clock_out || "\u2014") + "</div>" +
      '<div class="row-actions edit-time-row-actions" data-staff-id="' + esc(r.staff_id) + '">' + editButton + "</div></li>";
  }).join("");
  showMessage(rows.length ? "" : "No records match these filters. Clear a filter to see more.");
  $("status").value = state.status;
  $("rangeBtn").classList.toggle("on", !!(state.from || state.to));
  ["in", "out"].forEach((k) => $(k).classList.toggle("has-value", !!state[k]));
  document.querySelectorAll("#pills button").forEach((b) => b.classList.toggle("on", b.dataset.status === state.status && state.status !== ""));
}

function showMessage(text) { const m = $("message"); m.textContent = text; m.hidden = !text; }

function renderStatic() {
  $("cOnsite").textContent = data.summary.onsite;
  $("cAll").textContent = data.summary.all_staff;
  $("cOffsite").textContent = data.summary.offsite;
  const depts = [...new Set(data.records.map((r) => r.department))].sort();
  $("department").innerHTML = '<option value="">All Departments</option>' + depts.map((d) => '<option value="' + esc(d) + '">' + esc(d) + "</option>").join("");
  $("activity").innerHTML = data.recent_activity.map((a) => {
    const changed = a.action === "Time Corrected" && a.reason;
    const staff = data.records.find((record) => record.staff_id === a.staff_id) || {};
    const popupName = a.name;
    const popupId = a.staff_id || staff.staff_id || "";
    const popupInitials = esc(initials(popupName));
    const name = changed
      ? '<span class="activity-person activity-person--changed" tabindex="0" aria-label="' + esc(a.name + ". Attendance changed. Hover or focus to view the reason.") + '">' +
        '<span class="activity-person__name">' + esc(a.name) + '</span>' +
        '<span class="activity-person__icon" aria-hidden="true">i</span>' +
        '<span class="activity-change-card" role="tooltip">' +
          '<span class="activity-change-card__header"><span class="activity-change-card__avatar" style="background:' + colour(popupId) + '">' + popupInitials + '</span>' +
            '<span class="activity-change-card__identity"><strong>' + esc(popupName) + '</strong><span>' + esc(popupId) + '</span></span></span>' +
          '<span class="activity-change-card__body"><span class="activity-change-card__times">' +
            '<span class="activity-change-card__field"><span>Clock In</span><strong>' + esc(a.clock_in || staff.clock_in || "\u2014") + '</strong></span>' +
            '<span class="activity-change-card__field activity-change-card__field--out"><span>Clock Out</span><strong>' + esc(a.clock_out || staff.clock_out || "\u2014") + '</strong><span class="activity-change-card__badge" aria-label="Modified">M</span></span>' +
          '</span><span class="activity-change-card__reason-label">Reason for Change</span>' +
          '<span class="activity-change-card__reason">' + esc(a.reason) + '</span></span></span></span>'
      : esc(a.name);
    const action = a.action ? '<em' + (changed ? ' class="activity-edited"' : '') + '>&bull;' + esc(changed ? "Edited" : a.action) + "</em>" : "";
    return "<li><span>" + esc(a.time) + "</span><span>" + name + action + "</span></li>";
  }).join("");
}

function positionActivityReason(person) {
  const popup = person.querySelector(".activity-change-card");
  const name = person.querySelector(".activity-person__name");
  if (!popup || !name || !popup.getClientRects().length) return;
  const rect = popup.getBoundingClientRect();
  if (!rect.width || !rect.height) {
    requestAnimationFrame(() => positionActivityReason(person));
    return;
  }
  const target = name.getBoundingClientRect();
  const styles = getComputedStyle(popup);
  const reach = parseFloat(styles.getPropertyValue("--tail-reach")) || 10;
  const gap = reach + 3;
  const aboveTop = target.top - rect.height - gap;
  const below = aboveTop < 12;
  const left = Math.max(12, Math.min(target.left + target.width / 2 - rect.width / 2, window.innerWidth - rect.width - 12));
  const top = below
    ? Math.max(12, Math.min(target.bottom + gap, window.innerHeight - rect.height - 12))
    : aboveTop;
  const tailCenter = Math.max(24, Math.min(target.left + target.width / 2 - left, rect.width - 24));
  popup.classList.toggle("activity-change-card--below", below);
  popup.style.left = left + "px";
  popup.style.top = top + "px";
  popup.style.setProperty("--tail-left", tailCenter - 3 + "px");
}

function positionVisibleActivityReasons() {
  document.querySelectorAll(".activity-change-card").forEach((popup) => {
    if (popup.getClientRects().length) positionActivityReason(popup.parentElement);
  });
}

$("activity").addEventListener("mouseover", (event) => {
  const person = event.target.closest(".activity-person");
  if (person) positionActivityReason(person);
});
$("activity").addEventListener("focusin", (event) => {
  const person = event.target.closest(".activity-person");
  if (person) positionActivityReason(person);
});
$("activity").addEventListener("scroll", positionVisibleActivityReasons, { passive: true });
window.addEventListener("resize", positionVisibleActivityReasons);

window.EditTime = window.EditTime || {};
window.EditTime.updateAttendanceRow = function (recordId, clockIn, clockOut, reason) {
  if (!data) {
    throw new Error("Attendance records are not loaded.");
  }

  const record = data.records.find((item) => String(item.record_id) === String(recordId));
  if (!record) {
    throw new Error("The attendance record could not be found. Refresh the page and try again.");
  }

  record.clock_in = clockIn || null;
  record.clock_out = clockOut || null;
  data.recent_activity.unshift({
    time: new Intl.DateTimeFormat(undefined, { hour: "2-digit", minute: "2-digit", hour12: false }).format(new Date()),
    name: record.name,
    staff_id: record.staff_id,
    clock_in: record.clock_in,
    clock_out: record.clock_out,
    action: "Time Corrected",
    reason: String(reason || "").trim()
  });

  renderStatic();
  render();
};

function setFilter(key, value) { state[key] = value; syncUrl(); render(); }

let timer;
$("search").addEventListener("input", (e) => { clearTimeout(timer); timer = setTimeout(() => setFilter("search", e.target.value), 250); });
["department", "status", "in", "out", "from", "to"].forEach((k) => $(k).addEventListener("change", (e) => setFilter(k, e.target.value)));
$("pills").addEventListener("click", (e) => { const b = e.target.closest("button"); if (b) setFilter("status", b.dataset.status); });
function toggleRange(open) {
  $("rangePanel").hidden = !open;
  $("rangeBtn").setAttribute("aria-expanded", open);
}
$("rangeBtn").addEventListener("click", () => toggleRange($("rangePanel").hidden));
document.addEventListener("click", (e) => { if (!e.target.closest(".range")) toggleRange(false); });
document.addEventListener("keydown", (e) => { if (e.key === "Escape") toggleRange(false); });
$("clearDates").addEventListener("click", () => {
  $("from").value = ""; $("to").value = ""; state.from = ""; state.to = "";
  syncUrl(); render();
});

(async function init() {
  $("search").value = state.search; $("in").value = state.in; $("out").value = state.out; $("from").value = state.from; $("to").value = state.to;
  showMessage("Loading attendance records...");
  try {
    data = await load();
    renderStatic();
    $("department").value = state.department;
    render();
  } catch (err) {
    showMessage("Could not load attendance records. Check your connection and refresh the page.");
  }
})();

// S2-16b Live Onsite Dashboard: fetch, 30s refresh, loading/empty/error states.
const DATA_URL = window.F4_ONSITE_URL || 'assets/data/onsite-dummy.json';
const REFRESH_MS = 30000, TIMEOUT_MS = 10000;
const COLORS = ['#b4e61d','#2f6b6b','#ff6fd8','#ffb347','#6c8cff','#8e44ad'];
const LABEL = {present:'Present',absent:'Absent',not_checked_in:'Not Checked In',away:'Away'};
const $ = id => document.getElementById('f4-dashboard-admin-' + id);
let data = null, lastOk = null, tab = 'all', busy = false;

const h = (tag, cls, text) => { const e = document.createElement(tag); if (cls) e.className = cls; if (text != null) e.textContent = text; return e; };
const hhmm = iso => iso ? new Date(iso).toLocaleTimeString('en-ZA',{hour:'2-digit',minute:'2-digit',hour12:false}) : '——';
const initials = n => n.split(/\s+/).map(w => w[0]).slice(0,2).join('').toUpperCase();
const isPrevDay = iso => iso && new Date(iso).toDateString() !== new Date().toDateString();
const onsite = s => s.status === 'present' && s.clock_in && !s.clock_out;
function ago(iso){ const m = Math.max(0, Math.round((Date.now() - new Date(iso)) / 60000));
  return m < 1 ? 'just now' : m < 60 ? m + 'm ago' : m < 1440 ? Math.floor(m/60) + 'h ago' : Math.floor(m/1440) + 'd ago'; }

function header(){
  const now = new Date(), hr = now.getHours();
  $('date').textContent = now.toLocaleDateString('en-ZA',{weekday:'long',day:'numeric',month:'long',year:'numeric'});
  $('greet-text').textContent = hr < 12 ? 'Good morning' : hr < 18 ? 'Good afternoon' : 'Good evening';
}

function setState(kind, msg){            // kind: loading | empty | error | null
  const s = $('state'); s.className = 'f4-dashboard-admin__state' + (kind === 'error' ? ' f4-dashboard-admin__state--error' : ''); s.replaceChildren();
  s.hidden = !kind; $('rows').parentElement.parentElement.setAttribute('aria-busy', kind === 'loading');
  if (!kind) return;
  if (kind === 'loading') s.append(h('div','f4-dashboard-admin__spin'));
  s.append(h('div',null,msg));
  if (kind === 'error'){ const b = h('button','f4-dashboard-admin__button','Try again'); b.onclick = () => load(); s.append(h('div'), b); }
}

function renderRows(){
  const body = $('rows'); body.replaceChildren();
  const list = data.staff.filter(s => tab === 'all' || s.status === tab);
  if (!list.length){ setState('empty', tab === 'all' ? 'No staff records for today.' : 'No staff with status “' + LABEL[tab] + '”.'); return; }
  setState(null);
  list.forEach(s => {
    const prev = onsite(s) && isPrevDay(s.clock_in);
    const tr = h('tr', prev ? 'f4-dashboard-admin__row--previous-day' : '');
    const m = h('td'); const w = h('div','f4-dashboard-admin__member'); const a = h('span','f4-dashboard-admin__av',initials(s.name));
    a.style.background = COLORS[s.id % COLORS.length]; w.append(a, h('span',null,s.name)); m.append(w);
    const st = h('td'); st.append(h('span','f4-dashboard-admin__pill f4-dashboard-admin__pill--' + s.status, LABEL[s.status] || s.status));
    const inn = h('td', null, hhmm(s.clock_in));
    if (prev){ const f = h('span','f4-dashboard-admin__flag','Previous day'); f.title = 'Clocked in ' + new Date(s.clock_in).toLocaleDateString('en-ZA',{day:'numeric',month:'short'}) + ' and not clocked out'; inn.append(f); }
    tr.append(m, h('td',null,s.department), st, inn, h('td',null,hhmm(s.clock_out)));
    body.append(tr);
  });
}

function renderStats(){
  const sm = data.summary || {}, on = sm.onsite ?? data.staff.filter(onsite).length;
  $('lead').textContent = 'All systems are operational. You have ' + (sm.pending_approvals ?? 0) + ' pending approvals and ' + (sm.new_signups ?? 0) + ' new user sign-ups today';
  $('s-on').textContent = on; $('s-off').textContent = sm.offsite ?? '–';
  $('s-nci').textContent = sm.not_checked_in ?? data.staff.filter(s => s.status === 'not_checked_in').length;
  $('s-late').textContent = sm.late ?? '–';
}

function renderFeed(){
  const f = $('feed'); f.replaceChildren(); const items = data.activity || [];
  $('feed-state').hidden = items.length > 0;
  items.forEach(a => { const li = h('li'); li.append(h('time',null,ago(a.at)), h('b',null,a.name), h('span',null,a.action + ' at ' + hhmm(a.at))); f.append(li); });
}

const BAR_COLORS = ['#2f9bff','#2cf24a','#ff2d4a','#ff8c1a','#ff1f87'];
const PIE_COLORS = ['#2f9bff','#ff3b4e','#2cf27a','#8a4dff','#ff9a2e','#ff5cf0'];
const lighten = (hex, t) => '#' + [1,3,5].map(k => Math.round(parseInt(hex.slice(k, k + 2), 16) * (1 - t) + 255 * t).toString(16).padStart(2, '0')).join('');
const sv = (tag, attrs, text) => { const e = document.createElementNS('http://www.w3.org/2000/svg', tag); for (const k in attrs) e.setAttribute(k, attrs[k]); if (text != null) e.textContent = text; return e; };

function renderCharts(){ renderWeek(); renderDepartments(); }

function renderWeek(){
  const host = $('bars'); host.replaceChildren();
  const wk = data.week || [], ymax = Math.ceil(Math.max(1, ...wk.map(d => d.count)) / 10) * 10;
  const plot = h('div','f4-dashboard-admin__plot'), cols = h('div','f4-dashboard-admin__cols'), days = h('div','f4-dashboard-admin__days');
  for (let i = 0; i <= 4; i++){ const g = h('div','f4-dashboard-admin__gridline'); g.style.bottom = (i * 25) + '%'; g.append(h('span',null,Math.round(ymax * i / 4))); plot.append(g); }
  wk.forEach((d,i) => {
    const c = BAR_COLORS[i % BAR_COLORS.length], col = h('div','f4-dashboard-admin__col'), val = h('em',null,d.count), bar = h('span');
    val.style.color = c; bar.style.height = (d.count / ymax * 100) + '%'; bar.style.background = 'linear-gradient(to bottom, ' + c + ', ' + lighten(c, .65) + ')';
    col.append(val, bar); cols.append(col); days.append(h('span',null,d.day));
  });
  plot.append(cols); host.append(plot, days);
  const wl = $('wk-leg'); wl.replaceChildren();
  wk.forEach((d,i) => { const x = h('span',null,d.day), dot = h('i'); dot.style.background = BAR_COLORS[i % BAR_COLORS.length]; x.prepend(dot); wl.append(x); });
}

function renderDepartments(){
  let list = data.departments;
  if (!Array.isArray(list)){ const m = {}; data.staff.filter(onsite).forEach(s => m[s.department] = (m[s.department] || 0) + 1); list = Object.keys(m).map(k => ({ name:k, count:m[k] })); }
  const total = list.reduce((n,d) => n + d.count, 0), host = $('pie'), lg = $('legend'); host.replaceChildren(); lg.replaceChildren();
  if (!total){ host.append(h('div','f4-dashboard-admin__empty-s','No department data.')); return; }
  const svg = sv('svg',{ viewBox:'0 0 340 250', role:'img', 'aria-label':'Staff by department' }), defs = sv('defs',{}), cx = 170, cy = 125, r = 80;
  svg.append(defs); let a0 = -Math.PI / 2;
  list.forEach((d,i) => {
    const c = PIE_COLORS[i % PIE_COLORS.length], a1 = a0 + d.count / total * 2 * Math.PI, id = 'f4da-g' + i;
    const g = sv('linearGradient',{ id:id, x1:0, y1:0, x2:0, y2:1 }); g.append(sv('stop',{ offset:'0%', 'stop-color':c }), sv('stop',{ offset:'100%', 'stop-color':lighten(c, .6) })); defs.append(g);
    if (list.length === 1) svg.append(sv('circle',{ cx:cx, cy:cy, r:r, fill:'url(#' + id + ')' }));
    else svg.append(sv('path',{ d:'M' + cx + ' ' + cy + ' L' + (cx + r * Math.cos(a0)) + ' ' + (cy + r * Math.sin(a0)) + ' A' + r + ' ' + r + ' 0 ' + (a1 - a0 > Math.PI ? 1 : 0) + ' 1 ' + (cx + r * Math.cos(a1)) + ' ' + (cy + r * Math.sin(a1)) + ' Z', fill:'url(#' + id + ')' }));
    const mid = (a0 + a1) / 2, cs = Math.cos(mid), sn = Math.sin(mid), lx = cx + (r + 12) * cs, ly = cy + (r + 12) * sn + (sn < -0.5 ? -6 : sn > 0.5 ? 6 : 0);
    const anchor = cs > 0.3 ? 'start' : cs < -0.3 ? 'end' : 'middle';
    svg.append(sv('text',{ x:lx, y:ly, 'text-anchor':anchor, 'font-size':10, fill:'#0e2b33' }, d.name + ':'), sv('text',{ x:lx, y:ly + 11, 'text-anchor':anchor, 'font-size':10, fill:'#0e2b33' }, String(d.count)));
    const li = h('li',null,d.name), dot = h('i'); dot.style.background = c; li.prepend(dot); lg.append(li);
    a0 = a1;
  });
  host.append(svg);
}

function renderEarly(){
  const e = data.early || {}, rows = e.rows || [], body = $('early-rows'); body.replaceChildren();
  $('early-sub').textContent = (e.week_label || '') + '. Early means clocked in before ' + (e.threshold || '08:00');
  $('early-state').hidden = rows.length > 0;
  rows.forEach((r,i) => { const tr = h('tr'); const m = h('td'); const w = h('div','f4-dashboard-admin__member'); w.append(h('span','f4-dashboard-admin__oval'), h('span',null,r.name)); m.append(w);
    tr.append(h('td',null,(i + 1) + '.'), m, h('td',null,r.department), h('td',null,r.early_days + ' OF ' + r.total_days), h('td',null,r.earliest)); body.append(tr); });
}
function renderSheets(){
  const s = data.sheets || {};
  $('sh-status').textContent = s.connected ? '◉ Connected' : 'Not connected';
  $('sh-sync').textContent = s.last_synced ? 'Last Synced ' + (isPrevDay(s.last_synced) ? 'on ' + new Date(s.last_synced).toLocaleDateString('en-ZA',{day:'numeric',month:'short'}) : 'today') + ' at ' + hhmm(s.last_synced) : 'Not synced yet';
  $('sh-open').href = s.url || '#';
}

function banner(msg, retry){
  const b = $('banner'); b.replaceChildren(); b.hidden = !msg; if (!msg) return;
  b.append(h('span',null,msg));
  if (retry){ const r = h('button',null,'Retry now'); r.onclick = () => load(); b.append(r); }
}

function render(){ renderStats(); renderRows(); renderFeed(); renderCharts(); renderEarly(); renderSheets();
  $('live-tag').title = 'Updated ' + lastOk.toLocaleTimeString('en-ZA',{hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false}); }

async function load(){
  if (busy) return; busy = true;
  if (!data) setState('loading', 'Loading staff onsite…');
  try {
    const r = await fetch(DATA_URL, { cache:'no-store', signal:AbortSignal.timeout(TIMEOUT_MS) });
    if (!r.ok) throw new Error('HTTP ' + r.status);
    const d = await r.json();
    if (!d || !Array.isArray(d.staff)) throw new Error('Unexpected data');
    data = d; lastOk = new Date(); banner(null); render();
  } catch (e) {
    console.error('Onsite refresh failed:', e);
    if (data) banner('Could not refresh. Showing data from ' + lastOk.toLocaleTimeString('en-ZA',{hour:'2-digit',minute:'2-digit',hour12:false}) + '. Trying again in 30 seconds.', true);
    else setState('error', 'Could not load staff data. Check your connection and try again.');
  } finally { busy = false; }
}

$('tabs').addEventListener('click', e => {
  const b = e.target.closest('button[data-t]'); if (!b) return; tab = b.dataset.t;
  document.querySelectorAll('[role=tab]').forEach(x => x.setAttribute('aria-selected', x === b));
  if (data) renderRows();
});
header(); load();
setInterval(() => { header(); load(); }, REFRESH_MS);
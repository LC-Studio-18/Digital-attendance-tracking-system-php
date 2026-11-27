/**
 * status-api.js  (S2-12A)  - API layer only. No DOM code here.
 *
 * One store per endpoint, shared by every badge on the page, so several
 * badges never trigger duplicate requests.
 *
 * !! CONTRACT TO CONFIRM WITH TEAM 6 (S2-12B) !!
 * The response format was not available when this was written. The three values
 * below are the ONLY place the format is assumed. Change them once S2-12B is agreed.
 * Anything other than exactly IN or OUT is treated as unusable -> "Status unavailable".
 */
(function (global) {
  'use strict';

  var CONTRACT = {
    statusField: 'status', // TO CONFIRM: JSON field holding the status
    inValue: 'IN',         // TO CONFIRM
    outValue: 'OUT',       // TO CONFIRM
    sinceField: 'since'    // TO CONFIRM (optional): time of the user's last clock action.
                           // Accepts "HH:MM" or an ISO 8601 timestamp. If absent/unusable, the
                           // "Since" line is simply not shown (a time is never invented).
  };
  var TIMEOUT_MS = 8000;
  var CLOCK_SUCCESS_EVENT = 'attendance:clock-success';

  var options = {
    // Return extra request headers if S2-12B needs them (e.g. an Authorization token).
    headers: function () { return {}; }
  };
  var stores = {};

  function parseStatus(json) {
    if (!json || typeof json !== 'object') return null;
    var value = json[CONTRACT.statusField];
    if (value === CONTRACT.inValue) return 'IN';
    if (value === CONTRACT.outValue) return 'OUT';
    return null;
  }

  function pad(n) { n = String(n); return n.length < 2 ? '0' + n : n; }

  // "08:30", "8:30:15" or an ISO timestamp -> "08:30" (browser local time). Anything else -> null.
  function formatSince(value) {
    if (typeof value !== 'string' || !value.trim()) return null;
    var text = value.trim();
    var m = /^(\d{1,2}):(\d{2})(:\d{2})?$/.exec(text);
    if (m) return pad(m[1]) + ':' + m[2];
    var d = new Date(text);
    if (isNaN(d.getTime())) return null;
    return pad(d.getHours()) + ':' + pad(d.getMinutes());
  }

  function createStore(endpoint) {
    var listeners = [];
    var state = { status: 'loading' };
    var started = false;
    var seq = 0;
    var controller = null;

    function emit(next) {
      state = next;
      listeners.slice().forEach(function (fn) { fn(state); });
    }

    function refresh() {
      started = true;
      var mySeq = ++seq;
      if (controller) controller.abort(); // a newer request replaces an older one
      var ctl = (controller = new AbortController());
      var timer = setTimeout(function () { ctl.abort(); }, TIMEOUT_MS);
      emit({ status: 'loading' });

      var headers = Object.assign({ Accept: 'application/json' }, options.headers());
      return fetch(endpoint, {
        method: 'GET',
        headers: headers,
        credentials: 'same-origin',
        cache: 'no-store',
        signal: ctl.signal
      })
        .then(function (res) {
          if (!res.ok) throw new Error('HTTP ' + res.status);
          return res.json();
        })
        .then(function (json) {
          if (mySeq !== seq) return; // superseded by a newer request
          var status = parseStatus(json);
          if (!status) throw new Error('Unusable status data');
          emit({ status: status, since: formatSince(json[CONTRACT.sinceField]) });
        })
        .catch(function (err) {
          if (mySeq !== seq) return;
          emit({ status: 'unavailable', reason: err && err.message });
        })
        .then(function () { clearTimeout(timer); });
    }

    return {
      refresh: refresh,
      subscribe: function (fn) {
        listeners.push(fn);
        fn(state);
        if (!started) refresh(); // first subscriber triggers the one initial request
      }
    };
  }

  function getStore(endpoint) {
    if (!stores[endpoint]) stores[endpoint] = createStore(endpoint);
    return stores[endpoint];
  }

  // Refresh once after the backend confirms a clock action succeeded.
  // Several events in the same tick are merged into one refresh per endpoint.
  var scheduled = false;
  global.addEventListener(CLOCK_SUCCESS_EVENT, function () {
    if (scheduled) return;
    scheduled = true;
    setTimeout(function () {
      scheduled = false;
      Object.keys(stores).forEach(function (key) { stores[key].refresh(); });
    }, 0);
  });

  global.StatusApi = {
    getStore: getStore,
    parseStatus: parseStatus,
    formatSince: formatSince,
    CLOCK_SUCCESS_EVENT: CLOCK_SUCCESS_EVENT,
    configure: function (opts) { Object.assign(options, opts || {}); }
  };
})(window);

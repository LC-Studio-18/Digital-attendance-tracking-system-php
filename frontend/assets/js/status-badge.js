/**
 * status-badge.js  (S2-12A)  - presentation only. Data comes from StatusApi.
 * Auto-initialises every element with [data-status-badge].
 */
(function (global) {
  'use strict';

  var LABELS = {
    loading: 'Checking...',
    IN: 'IN',
    OUT: 'OUT',
    unavailable: 'Status unavailable'
  };

  // Announced to screen readers only when the confirmed status changes.
  var ANNOUNCE = {
    IN: 'Attendance status updated: you are clocked in.',
    OUT: 'Attendance status updated: you are clocked out.',
    unavailable: 'Attendance status is unavailable.'
  };

  // Each state has its own shape, so state is never colour-only.
  var SVG = '<svg viewBox="0 0 20 20" width="20" height="20" focusable="false" aria-hidden="true">';
  var ICONS = {
    loading: SVG + '<circle cx="10" cy="10" r="7" fill="none" stroke="currentColor" stroke-width="2.5" stroke-dasharray="28 16" stroke-linecap="round"/></svg>',
    IN: SVG + '<circle cx="10" cy="10" r="9" fill="currentColor"/><path d="M5.8 10.4l2.8 2.8 5.6-5.8" fill="none" style="stroke:var(--sb-bg)" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    OUT: SVG + '<circle cx="10" cy="10" r="8" fill="none" stroke="currentColor" stroke-width="2.4"/><path d="M6 10h8" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg>',
    unavailable: SVG + '<path d="M10 2.2L18.6 17H1.4z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M10 7.6v4.2" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/><circle cx="10" cy="14.4" r="1.2" fill="currentColor"/></svg>'
  };

  // Hook points for the shared S2-13A loading/error components.
  // Not built here on purpose (no competing shared implementation).
  // Once S2-13A is available, assign these from the page, e.g.
  //   StatusBadge.feedback.onError = function (el, reason) { SharedFeedback.showError(...); };
  var feedback = {
    onLoading: function (el) {},
    onResolved: function (el, status) {},
    onError: function (el, reason) {}
  };

  function init(el) {
    if (el.__statusBadgeReady) return;
    el.__statusBadgeReady = true;

    var iconEl = el.querySelector('.status-badge__icon');
    var labelEl = el.querySelector('.status-badge__label');
    var wrap = el.closest('.status-badge-wrap');
    var liveEl = wrap ? wrap.querySelector('.status-badge__live') : null;
    var lastResolved = null;

    function apply(status) {
      el.setAttribute('data-state', status);
      labelEl.textContent = LABELS[status];
      iconEl.innerHTML = ICONS[status];
    }

    function announce(status) {
      if (!liveEl) return;
      var changed = lastResolved !== null && lastResolved !== status;
      var firstFailure = lastResolved === null && status === 'unavailable';
      if (changed || firstFailure) liveEl.textContent = ANNOUNCE[status];
      lastResolved = status;
    }

    var endpoint = el.getAttribute('data-endpoint');
    if (!endpoint) {
      console.warn('[status-badge] No endpoint set. Showing "Status unavailable".');
      apply('unavailable');
      feedback.onError(el, 'No endpoint configured');
      return;
    }

    apply('loading');
    global.StatusApi.getStore(endpoint).subscribe(function (state) {
      apply(state.status);
      if (state.status === 'loading') {
        feedback.onLoading(el);
        return;
      }
      announce(state.status);
      if (state.status === 'unavailable') feedback.onError(el, state.reason);
      else feedback.onResolved(el, state.status);
    });
  }

  function initAll(root) {
    (root || document).querySelectorAll('[data-status-badge]').forEach(init);
  }

  // Call this from the existing clock code ONLY after the backend confirms success.
  function notifyClockSuccess(action) {
    global.dispatchEvent(new CustomEvent(global.StatusApi.CLOCK_SUCCESS_EVENT, { detail: { action: action } }));
  }

  global.StatusBadge = { init: init, initAll: initAll, feedback: feedback, notifyClockSuccess: notifyClockSuccess };

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { initAll(); });
  else initAll();
})(window);

(function (global) {
  'use strict';

  // Don't show the spinner for the first 300ms, so fast actions don't flicker.
  const LOADING_DELAY = 300;
  // After 5s, add a "slow connection" note (the request keeps running).
  const SLOW_DELAY = 5000;

  // Unique ID for one user action. Reused on retry so the server can spot duplicates.
  function makeToken() {
    if (global.crypto && typeof global.crypto.randomUUID === 'function') {
      return global.crypto.randomUUID();
    }
    return String(Date.now()) + '-' + Math.random().toString(36).slice(2, 8);
  }

  // Makes text safe to put inside innerHTML (prevents XSS / broken markup).
  function escapeText(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  // Sends a POST with `request_token` in the JSON body (agreed with backend, S2-13B).
  // Backend replies { status: "success" | "error", code, message, data }.
  // On error we throw, so the runner shows the error card with the backend's plain-language message.
  async function postJson(url, body, token) {
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(Object.assign({}, body, { request_token: token }))
    });

    let payload = null;
    try {
      payload = await res.json();
    } catch (_e) {
      // Not JSON: handled as a generic error below.
    }

    if (!res.ok || !payload || payload.status !== 'success') {
      const err = new Error('Request failed');
      err.code = payload && payload.code;
      err.userMessage = payload && payload.message;
      throw err;
    }
    return payload;
  }

  // Draws one state ('idle', 'loading', 'success', 'error', 'offline') into the container.
  function renderState(container, opts) {
    if (!container) return;
    const cfg = opts || {};
    const state = cfg.state || 'idle';

    if (state === 'idle') {
      container.innerHTML = '';
      return;
    }

    if (state === 'loading') {
      const slowNote = cfg.isSlow
        ? `<p class="lc-status__slow">${escapeText(cfg.slowText)}</p>`
        : '';
      container.innerHTML = `
        <div class="lc-status lc-status--loading" role="status" aria-live="polite">
          <span class="lc-spinner" aria-hidden="true"></span>
          <p class="lc-status__text">${escapeText(cfg.loadingText)}</p>
          ${slowNote}
        </div>`;
      return;
    }

    if (state === 'success') {
      container.innerHTML = `
        <div class="lc-status lc-status--success" role="status" aria-live="polite">
          <p class="lc-status__text">${escapeText(cfg.successText)}</p>
        </div>`;
      return;
    }

    // Error and offline share one layout with a retry button.
    // Errors use "alert" so screen readers announce them immediately.
    const isOffline = state === 'offline';
    const text = isOffline ? cfg.offlineText : cfg.errorText;
    const aria = isOffline ? 'role="status" aria-live="polite"' : 'role="alert" aria-live="assertive"';
    const mod = isOffline ? 'lc-status--offline' : 'lc-status--error';

    container.innerHTML = `
      <div class="lc-status ${mod}" ${aria}>
        <p class="lc-status__text">${escapeText(text)}</p>
        <button type="button" class="lc-retry-btn">${escapeText(cfg.retryLabel)}</button>
      </div>`;

    const btn = container.querySelector('.lc-retry-btn');
    if (btn && typeof cfg.onRetry === 'function') {
      btn.addEventListener('click', function () {
        // Disable instantly so rapid taps only trigger one retry.
        if (btn.disabled) return;
        btn.disabled = true;
        btn.setAttribute('aria-disabled', 'true');
        cfg.onRetry();
      });
    }
  }

  // Runs an action with loading/slow timers, offline check, one-at-a-time lock and token reuse.
  function createActionRunner(options) {
    if (!options || typeof options.request !== 'function') {
      throw new Error('createActionRunner requires a request function.');
    }

    // Why the token matters: if a request reaches the server but the reply is lost, retrying with
    // the SAME token lets the backend see it's a duplicate instead of clocking the user in twice.
    // It's only replaced after a success (the next action gets a fresh one).
    let token = null;
    let busy = false;
    let loadingTimer = null;
    let slowTimer = null;

    function clearTimers() {
      global.clearTimeout(loadingTimer);
      global.clearTimeout(slowTimer);
    }

    function showLoading(isSlow) {
      renderState(options.container, {
        state: 'loading',
        isSlow: isSlow,
        loadingText: options.loadingText,
        slowText: options.slowText
      });
    }

    // Shows the error/offline card. Prefers the backend's message, falls back to the page's errorText.
    function showRetryable(stateName, activeToken, err) {
      renderState(options.container, {
        state: stateName,
        offlineText: options.offlineText,
        errorText: (err && err.userMessage) || options.errorText,
        retryLabel: options.retryLabel,
        onRetry: function () {
          attempt(activeToken);
        }
      });
    }

    async function attempt(activeToken) {
      // Ignore extra triggers while a request is already running.
      if (busy) return;

      // Offline: show the message and send nothing.
      if (global.navigator && global.navigator.onLine === false) {
        showRetryable('offline', activeToken);
        return;
      }

      busy = true;
      let isSlow = false;
      let spinnerShown = false;
      clearTimers();

      loadingTimer = global.setTimeout(function () {
        spinnerShown = true;
        showLoading(isSlow);
      }, LOADING_DELAY);

      slowTimer = global.setTimeout(function () {
        isSlow = true;
        if (spinnerShown) showLoading(true);
      }, SLOW_DELAY);

      try {
        // Pass the token to the caller's request. Use StatusStates.postJson to send it
        // as `request_token` in the JSON body.
        const data = await options.request({ token: activeToken });
        clearTimers();
        busy = false;
        token = null;
        renderState(options.container, {
          state: 'success',
          successText:
            typeof options.successText === 'function' ? options.successText(data) : options.successText
        });
      } catch (err) {
        clearTimers();
        busy = false;
        // Token is kept on failure, so "Try again" reuses it.
        const offline = global.navigator && global.navigator.onLine === false;
        showRetryable(offline ? 'offline' : 'error', activeToken, err);
      }
    }

    return {
      // Brand-new action: new token.
      start: function () {
        if (busy) return;
        token = makeToken();
        return attempt(token);
      },
      // Retry the failed action with the same token.
      retry: function () {
        if (busy) return;
        if (!token) token = makeToken();
        return attempt(token);
      },
      getToken: function () {
        return token;
      }
    };
  }

  global.StatusStates = {
    renderState: renderState,
    createActionRunner: createActionRunner,
    makeToken: makeToken,
    postJson: postJson
  };
})(window);
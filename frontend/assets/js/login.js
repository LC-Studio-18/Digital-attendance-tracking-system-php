(function () {
    'use strict';

    var form = document.getElementById('login-form');
    if (!form) {
        return;
    }

    var errorEl = document.getElementById('login-error');
    var submitBtn = form.querySelector('button[type="submit"]');

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        if (errorEl) {
            errorEl.textContent = '';
            errorEl.hidden = true;
        }
        if (submitBtn) {
            submitBtn.disabled = true;
        }

        var username = form.elements.username ? form.elements.username.value.trim() : '';
        var password = form.elements.password ? form.elements.password.value : '';

        fetch('/api/login', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ username: username, password: password })
        })
            .then(function (response) {
                if (!response.ok) {
                    if (response.status === 404 || response.status === 501) {
                        throw new Error('Login API is not connected yet.');
                    }
                    return response.json().catch(function () {
                        return null;
                    }).then(function (body) {
                        throw new Error(body && body.message ? body.message : 'Login failed. Please check your details.');
                    });
                }
                return response.json().catch(function () {
                    return {};
                });
            })
            .then(function (payload) {
                if (payload && payload.success === false) {
                    throw new Error(payload.message || 'Login failed. Please check your details.');
                }

                // Successful login sets the PHP session cookie; staff land on the dashboard.
                window.location.assign('index.php');
            })
            .catch(function (error) {
                if (errorEl) {
                    errorEl.textContent = error.message || 'Login is not available right now.';
                    errorEl.hidden = false;
                }
            })
            .then(function () {
                if (submitBtn) {
                    submitBtn.disabled = false;
                }
            });
    });
}());

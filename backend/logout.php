<?php

require_once __DIR__ . '/includes/session.php';

sendNoCacheHeaders();
destroySessionAndLogout();

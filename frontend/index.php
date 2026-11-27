<?php

declare(strict_types=1);

require_once __DIR__ . '/../backend/config/config.php';
require_once __DIR__ . '/../backend/includes/functions.php';

// Require the user to be logged in before accessing the dashboard.
requireLogin();

echo 'Attendance system setup complete.';
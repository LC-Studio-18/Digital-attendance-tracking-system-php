<?php

declare(strict_types=1);

// session_start();
// $_SESSION['staff_id'] = 4;

// // Close the session so the controller can start it again.
// session_write_close();

$_SERVER['REQUEST_METHOD'] = 'POST';

require __DIR__ . '/../backend/controllers/clock-in-status.php';
<?php

require_once __DIR__ . '/config/database.php'; 
require_once __DIR__ . '/includes/LoginController.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ../frontend/login.php');
  exit;
}

// Lisekho - Validate Login, return result
/** @var PDO $pdo Defined by config/database.php */
$pdo = $GLOBALS['pdo'];
$staffModel = new Staff ($pdo);
$login = new LoginController($staffModel);
$result = $login->handleLogin($_POST);

// TODO CALEB: SUCCESS
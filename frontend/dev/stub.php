<?php
/**
 * TEMPORARY LOCAL STUB - NOT the S2-12B endpoint. Do NOT commit or deploy.
 * Exists only so the badge can be tested before S2-12B is available.
 *
 *   stub.php?action=status                 -> {"status":"IN"|"OUT"}  (kept in the PHP session, starts OUT)
 *   stub.php?action=clockin | clockout     -> changes the stored status
 *   &force=error | invalid | badjson | slow  -> simulate failures (status action)
 *   &force=fail                            -> clock action fails and status is NOT changed
 */
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-store');

$action = $_GET['action'] ?? 'status';
$force  = $_GET['force'] ?? '';
if (!isset($_SESSION['stub_status'])) $_SESSION['stub_status'] = 'OUT';

if ($action === 'status') {
    if ($force === 'error')   { http_response_code(500); echo json_encode(['error' => 'stub failure']); exit; }
    if ($force === 'invalid') { echo json_encode(['unexpected' => 'data']); exit; }
    if ($force === 'badjson') { echo 'this is not json'; exit; }
    if ($force === 'slow')    { sleep(3); }
    echo json_encode(['status' => $_SESSION['stub_status']]);
    exit;
}

if ($action === 'clockin' || $action === 'clockout') {
    if ($force === 'fail') { http_response_code(500); echo json_encode(['success' => false]); exit; }
    $_SESSION['stub_status'] = ($action === 'clockin') ? 'IN' : 'OUT';
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'unknown action']);

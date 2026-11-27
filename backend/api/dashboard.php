<?php
declare(strict_types=1);

// ===== TASK 1: BASE ENDPOINT =====
date_default_timezone_set('Africa/Johannesburg');
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

// ⚠️ CHANGE #1: swap to Vuvu's real file when it arrives
// (this filename currently holds the stand-in queries)
require_once __DIR__ . '/../includes/dashboard-quesries.php';
require_once __DIR__ . '/../includes/session.php';

// ⚠️ CHANGE #2: confirm the late cutoff with the team
const LATE_AFTER = '08:00:00';

function respond(int $code, array $payload): void
{
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    respond(405, ['error' => 'Method not allowed']);
}

// ===== TASK 2: SESSION CHECK =====
startSession();

// ⚠️ CHANGE #3: confirm the session key with the login team
$staffId = $_SESSION['staff_id'] ?? null;

if (!is_int($staffId) && !(is_string($staffId) && ctype_digit($staffId))) {
    respond(401, ['error' => 'Not authenticated']);
}
$staffId = (int) $staffId;

try {
    $pdo = getDatabaseConnection();

    $tz  = new DateTimeZone('Africa/Johannesburg');
    $now = new DateTimeImmutable('now', $tz);
    $fmt = 'Y-m-d H:i:s';

    // --- Staff name ---
    $nameStmt = $pdo->prepare(
        'SELECT first_name, last_name FROM staff WHERE staff_id = :staff_id'
    );
    $nameStmt->execute([':staff_id' => $staffId]);
    $staff = $nameStmt->fetch(PDO::FETCH_ASSOC) ?: ['first_name' => '', 'last_name' => ''];

    // --- Days present + attendance rate (this month) ---
    $monthStart = $now->modify('first day of this month')->setTime(0, 0, 0);
    $monthEnd   = $monthStart->modify('+1 month');

    $presentStmt = $pdo->prepare(
        'SELECT COUNT(DISTINCT DATE(clock_in)) FROM attendance
         WHERE staff_id = :staff_id AND clock_in >= :start AND clock_in < :end'
    );
    $presentStmt->execute([
        ':staff_id' => $staffId,
        ':start'    => $monthStart->format($fmt),
        ':end'      => $monthEnd->format($fmt),
    ]);
    $daysPresent = (int) $presentStmt->fetchColumn();

    $workingDays = 0;
    for ($d = $monthStart; $d <= $now; $d = $d->modify('+1 day')) {
        if ((int) $d->format('N') <= 5) { // Mon-Fri
            $workingDays++;
        }
    }
    $attendanceRate = $workingDays > 0
        ? min(100.0, round($daysPresent / $workingDays * 100, 1))
        : 0.0;

    // --- Chart: hours per day, Monday to Sunday of this week ---
    $weekStart = $now->setTime(0, 0, 0)->modify('monday this week');
    $weekEnd   = $weekStart->modify('+7 days');

    $chartStmt = $pdo->prepare(
        'SELECT clock_in, clock_out FROM attendance
         WHERE staff_id = :staff_id AND clock_in >= :start AND clock_in < :end'
    );
    $chartStmt->execute([
        ':staff_id' => $staffId,
        ':start'    => $weekStart->format($fmt),
        ':end'      => $weekEnd->format($fmt),
    ]);

    $chartHours = array_fill(0, 7, 0.0);
    foreach ($chartStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $in  = new DateTimeImmutable($row['clock_in'], $tz);
        $out = $row['clock_out'] !== null
            ? new DateTimeImmutable($row['clock_out'], $tz)
            : $now;
        $chartHours[(int) $in->format('N') - 1] += max(0, $out->getTimestamp() - $in->getTimestamp()) / 3600;
    }
    $chart = [
        'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        'hours'  => array_map(static fn(float $h): float => round($h, 2), $chartHours),
    ];

    // --- Vuvu's functions ---
    $status  = getStaffCurrentStatus($pdo, $staffId);
    $hours   = getStaffHoursSummary($pdo, $staffId);
    $records = getRecentAttendanceRecords($pdo, $staffId);

    // ===== TASK 3: CORRECTED FLAGS + Late/Present per record =====
    $recentRecords = array_map(static fn(array $r): array => [
        'record_id'         => (int) $r['record_id'],
        'clock_in'          => $r['clock_in'],
        'clock_out'         => $r['clock_out'],   // null = open shift
        'hours'             => $r['hours'] !== null ? (float) $r['hours'] : null,
        'is_corrected'      => (bool) $r['is_corrected'],
        'attendance_status' => substr((string) $r['clock_in'], 11, 8) > LATE_AFTER ? 'Late' : 'Present',
    ], $records);

    // ===== TASK 4: FINAL JSON =====
    respond(200, [
        'staff'           => [
            'first_name' => $staff['first_name'],
            'last_name'  => $staff['last_name'],
        ],
        'status'          => $status,
        'hours'           => [
            'today' => (float) $hours['today'],
            'week'  => (float) $hours['week'],
        ],
        'days_present'    => $daysPresent,
        'attendance_rate' => $attendanceRate,
        'chart'           => $chart,
        'recent_records'  => $recentRecords,
        'generated_at'    => $now->format($fmt),
    ]);
} catch (Throwable $e) {
    error_log('[dashboard] ' . $e->getMessage());
    respond(500, ['error' => 'Something went wrong. Please try again.']);
}
<?php
// Strict typing: PHP won't silently convert types in function calls.
declare(strict_types=1);

// =====================================================================
// TASK 1: BASE ENDPOINT (route for GET /api/dashboard)
// =====================================================================

// Everything in the system uses SAST (UTC+2).
date_default_timezone_set('Africa/Johannesburg');

// Never print raw PHP errors to the browser; they are logged instead.
ini_set('display_errors', '0');

// The response body is always JSON.
header('Content-Type: application/json; charset=utf-8');

// Matthew's file: getDatabaseConnection()
require_once __DIR__ . '/../config/database.php';

// Vuvu's file: getStaffCurrentStatus(), getStaffHoursSummary(),
// getRecentAttendanceRecords()
require_once __DIR__ . '/../includes/dashboard-queries.php';

// Team 1's helper: startSession() (same session settings as login)
require_once __DIR__ . '/../includes/session.php';

// A clock-in after this time is shown as "Late" (confirm with the team).
const LATE_AFTER = '08:00:00';

// Send a JSON response with an HTTP status code, then stop.
function respond(int $code, array $payload): void
{
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

// Read-only endpoint: only GET is allowed.
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    respond(405, ['error' => 'Method not allowed']);
}

// =====================================================================
// TASK 2: SESSION CHECK
// =====================================================================

startSession();

// SECURITY: staff_id comes ONLY from the session, never from URL or body.
$staffId = $_SESSION['staff_id'] ?? null;

// Valid = an int, or a string of digits only. Anything else = not logged in.
if (!is_int($staffId) && !(is_string($staffId) && ctype_digit($staffId))) {
    respond(401, ['error' => 'Not authenticated']);
}
$staffId = (int) $staffId;

try {
    $pdo = getDatabaseConnection();

    $tz  = new DateTimeZone('Africa/Johannesburg');
    $now = new DateTimeImmutable('now', $tz);
    $fmt = 'Y-m-d H:i:s';

    // ----- Staff name -----
    $nameStmt = $pdo->prepare(
        'SELECT first_name, last_name FROM staff WHERE staff_id = :staff_id'
    );
    $nameStmt->execute([':staff_id' => $staffId]);
    $staff = $nameStmt->fetch(PDO::FETCH_ASSOC) ?: ['first_name' => '', 'last_name' => ''];

    // ----- Days present + attendance rate (this month) -----
    $monthStart = $now->modify('first day of this month')->setTime(0, 0, 0);
    $monthEnd   = $monthStart->modify('+1 month');

    // DISTINCT days, so two shifts on one day count as one day present.
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

    // Mon-Fri days from the 1st until today (format 'N': 1=Mon ... 7=Sun).
    $workingDays = 0;
    for ($d = $monthStart; $d <= $now; $d = $d->modify('+1 day')) {
        if ((int) $d->format('N') <= 5) {
            $workingDays++;
        }
    }

    // Percentage, one decimal, capped at 100, no divide-by-zero.
    $attendanceRate = $workingDays > 0
        ? min(100.0, round($daysPresent / $workingDays * 100, 1))
        : 0.0;

    // ----- Chart: hours per day, Monday to Sunday of this week -----
    $weekStart = $now->setTime(0, 0, 0)->modify('monday this week');
    $weekEnd   = $weekStart->modify('+7 days');

    // Every shift that overlaps this week (including ones started before Monday).
    $chartStmt = $pdo->prepare(
        'SELECT clock_in, clock_out FROM attendance
         WHERE staff_id = :staff_id
           AND clock_in < :end
           AND (clock_out IS NULL OR clock_out > :start)'
    );
    $chartStmt->execute([
        ':staff_id' => $staffId,
        ':start'    => $weekStart->format($fmt),
        ':end'      => $weekEnd->format($fmt),
    ]);

    // Seven buckets (Mon..Sun), all starting at 0 hours.
    $chartHours = array_fill(0, 7, 0.0);
    foreach ($chartStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $in  = new DateTimeImmutable($row['clock_in'], $tz);
        // Open shift: count up to the current time.
        $out = $row['clock_out'] !== null
            ? new DateTimeImmutable($row['clock_out'], $tz)
            : $now;
        // Split the shift across the days it covers (same rule as Vuvu's hours).
        for ($i = 0; $i < 7; $i++) {
            $dayStart = $weekStart->modify("+$i days")->getTimestamp();
            $dayEnd   = $weekStart->modify('+' . ($i + 1) . ' days')->getTimestamp();
            $from = max($in->getTimestamp(), $dayStart);
            $to   = min($out->getTimestamp(), $dayEnd);
            if ($to > $from) {
                $chartHours[$i] += ($to - $from) / 3600;
            }
        }
    }
    $chart = [
        'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        'hours'  => array_map(static fn(float $h): float => round($h, 2), $chartHours),
    ];

    // ----- Vuvu's query functions -----
    $status  = getStaffCurrentStatus($pdo, $staffId);      // "IN" or "OUT"
    $hours   = getStaffHoursSummary($pdo, $staffId);       // ['today', 'week']
    $records = getRecentAttendanceRecords($pdo, $staffId); // last 10 records

    // =================================================================
    // TASK 3: CORRECTED FLAGS (+ Late/Present per record)
    // =================================================================
    $recentRecords = array_map(static fn(array $r): array => [
        'record_id'    => (int) $r['record_id'],
        'clock_in'     => $r['clock_in'],
        'clock_out'    => $r['clock_out'], // null = shift still open
        // null while the shift is open (matches the JSON contract)
        'hours'        => ($r['clock_out'] !== null && $r['hours'] !== null) ? (float) $r['hours'] : null,
        // true if the record has an audit log; (bool) makes it real true/false
        'is_corrected' => (bool) $r['is_corrected'],
        // "HH:MM:SS" part of the clock-in; after the cutoff = Late
        'attendance_status' => substr((string) $r['clock_in'], 11, 8) > LATE_AFTER ? 'Late' : 'Present',
    ], $records);

    // =================================================================
    // TASK 4: FINAL JSON
    // =================================================================
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
    // Real error goes to the server log; the user sees a safe message.
    error_log('[dashboard] ' . $e->getMessage());
    respond(500, ['error' => 'Something went wrong. Please try again.']);
}
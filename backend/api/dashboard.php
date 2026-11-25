<?php
// api/dashboard.php (TEMPORARY STUB: to be removed before the real endpoint goes in)
declare(strict_types=1);
date_default_timezone_set('Africa/Johannesburg');
header('Content-Type: application/json');

$case = $_GET['case'] ?? 'in'; // stub only; the real endpoint reads nothing from the URL

$cases = [
    'in' => [
        'status' => 'IN',
        'hours'  => ['today' => 4.5, 'week' => 22.25],
        'recent_records' => [
            ['record_id' => 4, 'clock_in' => '2026-11-25 08:00:00', 'clock_out' => null, 'hours' => null, 'is_corrected' => false],
            ['record_id' => 3, 'clock_in' => '2026-11-24 08:05:00', 'clock_out' => '2026-11-24 16:30:00', 'hours' => 8.42, 'is_corrected' => true],
        ],
    ],
    'out' => [
        'status' => 'OUT',
        'hours'  => ['today' => 8.0, 'week' => 30.5],
        'recent_records' => [
            ['record_id' => 5, 'clock_in' => '2026-11-25 07:30:00', 'clock_out' => '2026-11-25 15:30:00', 'hours' => 8.0, 'is_corrected' => false],
        ],
    ],
    'empty' => [
        'status' => 'OUT',
        'hours'  => ['today' => 0, 'week' => 0],
        'recent_records' => [],
    ],
];

$data = $cases[$case] ?? $cases['in'];
$data['generated_at'] = date('Y-m-d H:i:s');

echo json_encode($data);
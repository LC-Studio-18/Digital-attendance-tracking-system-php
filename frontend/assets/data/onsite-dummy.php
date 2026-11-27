<?php
// Dummy data for S2-16b. Timestamps are generated relative to "now" so the page always looks live.
// Elijah's real endpoint must return the same JSON shape.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function ts(string $when): ?string
{
    return $when === '' ? null : date('c', strtotime($when));
}

$rows = [
    [1, 'Thandi Mbeki', 'LC Studio', 'present', 'today 08:30', ''],
    [2, 'Sipho Msinqa', 'LC Studio', 'present', 'today 08:15', ''],
    [3, 'Zizipho Centi', 'HR', 'present', 'today 08:15', ''],
    [4, 'Luvo Cala', 'Placements', 'present', 'today 08:15', ''],
    [5, 'Siphamandla Msindo', 'Placements', 'present', 'yesterday 08:15', ''],
    [6, 'Siphamandla Msindo', 'LCA', 'absent', '', ''],
    [7, 'Siphamandla Msindo', 'LCA', 'absent', '', ''],
    [8, 'Siphamandla Msindo', 'Finance', 'absent', '', ''],
    [9, 'Aphiwe Ndlovu', 'Finance', 'away', 'today 08:05', ''],
    [10, 'Kagiso Mokoena', 'HR', 'not_checked_in', '', ''],
];
$staff = [];
foreach ($rows as $r) {
    $staff[] = ['id' => $r[0], 'name' => $r[1], 'department' => $r[2], 'status' => $r[3], 'clock_in' => ts($r[4]), 'clock_out' => ts($r[5])];
}

$activity = [];
foreach ([['Thandi Mbeki', '-2 minutes'], ['Sipho Msinqa', '-15 minutes'], ['Zizipho Centi', '-25 minutes'], ['Zizipho Centi', '-40 minutes'], ['Zizipho Centi', '-55 minutes']] as $a) {
    $activity[] = ['name' => $a[0], 'action' => 'Checked in', 'at' => ts($a[1])];
}

$early = [];
for ($i = 0; $i < 5; $i++) {
    $early[] = ['name' => 'Zusipha Mkatshane', 'department' => 'LCA', 'early_days' => 5, 'total_days' => 5, 'earliest' => '07:25'];
}

echo json_encode([
    'generated_at' => ts('now'),
    'summary' => ['onsite' => 32, 'offsite' => 12, 'not_checked_in' => 8, 'late' => 5, 'pending_approvals' => 4, 'new_signups' => 2],
    'staff' => $staff,
    'activity' => $activity,
    'week' => [['day' => 'Mon', 'count' => 40], ['day' => 'Tue', 'count' => 70], ['day' => 'Wed', 'count' => 45], ['day' => 'Thur', 'count' => 90], ['day' => 'Fri', 'count' => 65]],
    'departments' => [['name' => 'LC Studio', 'count' => 40], ['name' => 'Finance', 'count' => 70], ['name' => 'Placements', 'count' => 45], ['name' => 'LCA', 'count' => 90], ['name' => 'HR', 'count' => 65], ['name' => "Leader's Quest", 'count' => 85]],
    'early' => ['week_label' => 'Week of 16 Nov to 20 Nov 2026', 'threshold' => '08:00', 'rows' => $early],
    'sheets' => ['connected' => true, 'last_synced' => ts('today 13:30'), 'url' => '#'],
]);

<?php
declare(strict_types=1);

/**
 * Builds reusable SQL filters for searching attendance history by date
 * and employee. The same filters can be used by the history endpoint
 * and the CSV export feature.
 *
 * @param array $filters Optional date and employee search values.
 * @return array SQL WHERE conditions and their prepared-statement parameters.
 */
function buildAttendanceHistoryFilters(array $filters): array
{
    $conditions = [];
    $params = [];

    // Read and clean the optional search values supplied by the admin.
    $dateFrom = trim((string) ($filters['date_from'] ?? ''));
    $dateTo = trim((string) ($filters['date_to'] ?? ''));
    $employee = trim((string) ($filters['employee'] ?? ''));

    // Filter records from the beginning of the selected start date.
    if ($dateFrom !== '') {
        if (!isValidAttendanceDate($dateFrom)) {
            throw new InvalidArgumentException(
                'Invalid start date. Use YYYY-MM-DD.'
            );
        }

        $conditions[] = 'a.clock_in >= :date_from';
        $params['date_from'] = $dateFrom . ' 00:00:00';
    }

    // Include the entire selected end date by using the following
    // day's midnight as an exclusive upper limit.
    if ($dateTo !== '') {
        if (!isValidAttendanceDate($dateTo)) {
            throw new InvalidArgumentException(
                'Invalid end date. Use YYYY-MM-DD.'
            );
        }

        $conditions[] = 'a.clock_in < :date_to';
        $params['date_to'] = (new DateTimeImmutable($dateTo))
            ->modify('+1 day')
            ->format('Y-m-d 00:00:00');
    }

    // Prevent searches where the start date is later than the end date.
    if (
        $dateFrom !== '' &&
        $dateTo !== '' &&
        $dateFrom > $dateTo
    ) {
        throw new InvalidArgumentException(
            'The start date cannot be later than the end date.'
        );
    }

    // Search for an employee using a partial match against their full name
    // or employee number.
    if ($employee !== '') {
        $conditions[] = "(
            CONCAT_WS(' ', s.first_name, s.last_name) LIKE :employee_name
            OR s.employee_number LIKE :employee_number
        )";

        $params['employee_name'] = '%' . $employee . '%';
        $params['employee_number'] = '%' . $employee . '%';
    }

    // Combine all active filters with AND so that every supplied
    // filter must match the attendance record.
    $whereSql = '';

    if (!empty($conditions)) {
        $whereSql = ' WHERE ' . implode(' AND ', $conditions);
    }

    // Return the SQL conditions separately from their values for safe PDO binding.
    return [
        'where_sql' => $whereSql,
        'params' => $params,
    ];
}

/**
 * Checks that a date is a valid calendar date in YYYY-MM-DD format.
 */
function isValidAttendanceDate(string $date): bool
{
    $dateObject = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

    return $dateObject !== false
        && $dateObject->format('Y-m-d') === $date;
}

/**
 * Retrieves filtered attendance history with pagination.
 *
 * @param PDO $pdo Active database connection.
 * @param array $filters Optional date and employee filters.
 * @param int $page Requested page number.
 * @return array Attendance records and pagination details.
 */
function getAttendanceHistory(
    PDO $pdo,
    array $filters,
    int $page = 1
): array {
    if ($page < 1) {
        throw new InvalidArgumentException('Page number must be at least 1.');
    }

    $perPage = 25;
    $offset = ($page - 1) * $perPage;

    // Build the reusable filters.
    $filterResult = buildAttendanceHistoryFilters($filters);
    $whereSql = $filterResult['where_sql'];
    $params = $filterResult['params'];

    // Count all records matching the filters.
    $countSql = "
        SELECT COUNT(*)
        FROM attendance a
        INNER JOIN staff s ON a.staff_id = s.staff_id
        $whereSql
    ";

    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalRecords = (int) $countStmt->fetchColumn();

    // Retrieve the attendance records for the requested page.
    $historySql = "
        SELECT
            a.record_id,
            s.employee_number,
            s.first_name,
            s.last_name,
            a.clock_in,
            a.clock_out,
            CASE
                WHEN a.clock_out IS NULL THEN 'Still IN'
                ELSE 'Completed'
            END AS status,
            CASE
                WHEN a.clock_out IS NULL THEN NULL
                ELSE TIMESTAMPDIFF(SECOND, a.clock_in, a.clock_out)
            END AS duration_seconds
        FROM attendance a
        INNER JOIN staff s ON a.staff_id = s.staff_id
        $whereSql
        ORDER BY a.clock_in DESC, a.record_id DESC
        LIMIT :limit OFFSET :offset
    ";

    $historyStmt = $pdo->prepare($historySql);

    // Bind the filter values safely.
    foreach ($params as $name => $value) {
        $historyStmt->bindValue(':' . $name, $value, PDO::PARAM_STR);
    }

    // Bind pagination values as integers.
    $historyStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $historyStmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $historyStmt->execute();
    $records = $historyStmt->fetchAll();

    // Format the duration for completed attendance records.
    foreach ($records as &$record) {
        if ($record['duration_seconds'] === null) {
            $record['duration'] = null;
        } else {
            $seconds = (int) $record['duration_seconds'];
            $hours = intdiv($seconds, 3600);
            $minutes = intdiv($seconds % 3600, 60);

            $record['duration'] = sprintf(
                '%d hours %d minutes',
                $hours,
                $minutes
            );
        }

        unset($record['duration_seconds']);
    }

    unset($record);

    return [
        'records' => $records,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $perPage,
            'total_records' => $totalRecords,
            'total_pages' => (int) ceil($totalRecords / $perPage),
        ],
    ];
}
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

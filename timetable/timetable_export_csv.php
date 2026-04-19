<?php
/**
 * Exportiert den Stundenplan des eingeloggten Nutzers als CSV.
 */
require_once __DIR__ . '/../includes/api_helper.php';
require_once __DIR__ . '/../includes/i18n.php';

$user_id = require_auth();
$locale = learnhub_get_locale();
$isEnglish = $locale === 'en';

$messages = [
    'load_error' => $isEnglish ? 'Error while loading timetable data' : 'Fehler beim Laden der Stundenplandaten',
    'invalid_response' => $isEnglish ? 'Invalid response from backend' : 'Ungültige Antwort vom Backend',
    'filename_prefix' => $isEnglish ? 'timetable' : 'stundenplan',
];

$headers = $isEnglish
    ? ['Day', 'Period', 'Time', 'Subject', 'Room']
    : ['Tag', 'Stunde', 'Zeit', 'Fach', 'Raum'];

$backend_url = BACKEND_BASE_URL . "/timetable/$user_id";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $backend_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || $response === false) {
    http_response_code($httpCode ?: 500);
    echo $messages['load_error'];
    exit();
}

$data = json_decode($response, true);
if (!is_array($data)) {
    http_response_code(500);
    echo $messages['invalid_response'];
    exit();
}

$dayOrder = [
    'monday' => 1,
    'tuesday' => 2,
    'wednesday' => 3,
    'thursday' => 4,
    'friday' => 5,
    'saturday' => 6,
    'sunday' => 7,
];

$dayLabels = [
    'monday' => $isEnglish ? 'Monday' : 'Montag',
    'tuesday' => $isEnglish ? 'Tuesday' : 'Dienstag',
    'wednesday' => $isEnglish ? 'Wednesday' : 'Mittwoch',
    'thursday' => $isEnglish ? 'Thursday' : 'Donnerstag',
    'friday' => $isEnglish ? 'Friday' : 'Freitag',
    'saturday' => $isEnglish ? 'Saturday' : 'Samstag',
    'sunday' => $isEnglish ? 'Sunday' : 'Sonntag',
];

usort($data, function ($a, $b) use ($dayOrder) {
    $dayA = $dayOrder[$a['day'] ?? ''] ?? 99;
    $dayB = $dayOrder[$b['day'] ?? ''] ?? 99;
    if ($dayA === $dayB) {
        return ((int)($a['period'] ?? 0)) <=> ((int)($b['period'] ?? 0));
    }
    return $dayA <=> $dayB;
});

$filename = $messages['filename_prefix'] . '_' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');
fputcsv($output, $headers, ';');

foreach ($data as $row) {
    $dayKey = strtolower((string)($row['day'] ?? ''));
    fputcsv($output, [
        $dayLabels[$dayKey] ?? ($row['day'] ?? ''),
        $row['period'] ?? '',
        $row['time'] ?? '',
        $row['subject'] ?? '',
        $row['room'] ?? '',
    ], ';');
}

fclose($output);
exit();

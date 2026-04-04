<?php
/**
 * Exportiert den Stundenplan des eingeloggten Nutzers als CSV.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();
$backend_url = BACKEND_BASE_URL . "/timetable/$user_id";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $backend_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || $response === false) {
    http_response_code($httpCode ?: 500);
    echo "Fehler beim Laden der Stundenplandaten";
    exit();
}

$data = json_decode($response, true);
if (!is_array($data)) {
    http_response_code(500);
    echo "Ungültige Antwort vom Backend";
    exit();
}

$dayOrder = [
    'Montag' => 1,
    'Dienstag' => 2,
    'Mittwoch' => 3,
    'Donnerstag' => 4,
    'Freitag' => 5,
    'Samstag' => 6,
    'Sonntag' => 7,
];

usort($data, function ($a, $b) use ($dayOrder) {
    $dayA = $dayOrder[$a['day'] ?? ''] ?? 99;
    $dayB = $dayOrder[$b['day'] ?? ''] ?? 99;
    if ($dayA === $dayB) {
        return ((int)($a['period'] ?? 0)) <=> ((int)($b['period'] ?? 0));
    }
    return $dayA <=> $dayB;
});

$filename = 'stundenplan_' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');
fputcsv($output, ['Tag', 'Stunde', 'Zeit', 'Fach', 'Raum'], ';');

foreach ($data as $row) {
    fputcsv($output, [
        $row['day'] ?? '',
        $row['period'] ?? '',
        $row['time'] ?? '',
        $row['subject'] ?? '',
        $row['room'] ?? '',
    ], ';');
}

fclose($output);
exit();

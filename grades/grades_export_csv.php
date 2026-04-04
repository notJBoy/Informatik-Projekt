<?php
/**
 * Exportiert die Noten des eingeloggten Nutzers als CSV.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();
$backend_url = BACKEND_BASE_URL . "/grades/$user_id";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $backend_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || $response === false) {
    http_response_code($httpCode ?: 500);
    echo "Fehler beim Laden der Notendaten";
    exit();
}

$data = json_decode($response, true);
if (!is_array($data)) {
    http_response_code(500);
    echo "Ungültige Antwort vom Backend";
    exit();
}

usort($data, function ($a, $b) {
    return strcmp((string)($a['subject'] ?? ''), (string)($b['subject'] ?? ''));
});

$filename = 'noten_' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');
fputcsv($output, ['Fach', 'Punkte', 'Gewichtung', 'Beschreibung', 'Datum'], ';');

foreach ($data as $row) {
    fputcsv($output, [
        $row['subject'] ?? '',
        $row['value'] ?? '',
        $row['weight'] ?? 1,
        $row['description'] ?? '',
        $row['date'] ?? '',
    ], ';');
}

fclose($output);
exit();

<?php
/**
 * Exportiert die Noten des eingeloggten Nutzers als CSV.
 */
require_once __DIR__ . '/../includes/api_helper.php';
require_once __DIR__ . '/../includes/i18n.php';

$user_id = require_auth();
$locale = learnhub_get_locale();

$isEnglish = $locale === 'en';
$messages = [
    'load_error' => $isEnglish ? 'Error while loading grade data' : 'Fehler beim Laden der Notendaten',
    'invalid_response' => $isEnglish ? 'Invalid response from backend' : 'Ungültige Antwort vom Backend',
    'filename_prefix' => $isEnglish ? 'grades' : 'noten',
];

$headers = $isEnglish
    ? ['Subject', 'Points', 'Weight', 'Description', 'Date']
    : ['Fach', 'Punkte', 'Gewichtung', 'Beschreibung', 'Datum'];

$backend_url = BACKEND_BASE_URL . "/grades/$user_id";

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

usort($data, function ($a, $b) {
    return strcmp((string)($a['subject'] ?? ''), (string)($b['subject'] ?? ''));
});

$filename = $messages['filename_prefix'] . '_' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');
fputcsv($output, $headers, ';');

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

<?php
/**
 * Exportiert die Noten des eingeloggten Nutzers als einfache PDF-Datei.
 */
require_once __DIR__ . '/../includes/api_helper.php';
require_once __DIR__ . '/../includes/i18n.php';

$user_id = require_auth();
$locale = learnhub_get_locale();
$isEnglish = $locale === 'en';

$messages = [
    'load_error' => $isEnglish ? 'Error while loading grade data' : 'Fehler beim Laden der Notendaten',
    'invalid_response' => $isEnglish ? 'Invalid response from backend' : 'Ungültige Antwort vom Backend',
    'title' => $isEnglish ? 'Grades Export' : 'Noten Export',
    'date_label' => $isEnglish ? 'Date' : 'Datum',
    'avg_label' => $isEnglish ? 'Weighted average' : 'Gewichteter Durchschnitt',
    'points' => $isEnglish ? 'points' : 'Punkte',
    'empty' => $isEnglish ? 'No grades available.' : 'Keine Noten vorhanden.',
    'filename_prefix' => $isEnglish ? 'grades' : 'noten',
    'point_suffix' => $isEnglish ? ' pts' : ' P',
];

$dateFormat = $isEnglish ? 'Y-m-d H:i' : 'd.m.Y H:i';

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

$totalWeighted = 0.0;
$sumWeighted = 0.0;
foreach ($data as $row) {
    $weight = (float)($row['weight'] ?? 1);
    if ($weight <= 0) {
        $weight = 1;
    }
    $totalWeighted += $weight;
    $sumWeighted += ((float)($row['value'] ?? 0)) * $weight;
}
$avg = $totalWeighted > 0 ? number_format($sumWeighted / $totalWeighted, 2, '.', '') : '0.00';

$lines = [
    $messages['title'],
    $messages['date_label'] . ': ' . date($dateFormat),
    $messages['avg_label'] . ': ' . $avg . ' ' . $messages['points'],
    str_repeat('-', 60),
];

if (empty($data)) {
    $lines[] = $messages['empty'];
} else {
    foreach ($data as $row) {
        $weight = (float)($row['weight'] ?? 1);
        if ($weight <= 0) {
            $weight = 1;
        }
        $lines[] = sprintf(
            '%s | %s P | x%s | %s',
            (string)($row['subject'] ?? '-'),
            (string)($row['value'] ?? '-') . $messages['point_suffix'],
            rtrim(rtrim(number_format($weight, 2, '.', ''), '0'), '.'),
            (string)($row['description'] ?? '')
        );
    }
}

function escape_pdf_text(string $text): string
{
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
}

$content = "BT\n/F1 11 Tf\n50 790 Td\n";
$first = true;
foreach ($lines as $line) {
    if (!$first) {
        $content .= "0 -15 Td\n";
    }
    $content .= '(' . escape_pdf_text($line) . ") Tj\n";
    $first = false;
}
$content .= "ET";

$objects = [];
$offsets = [];
$pdf = "%PDF-1.4\n";

$objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
$objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
$objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n";
$objects[] = "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
$objects[] = "5 0 obj\n<< /Length " . strlen($content) . " >>\nstream\n" . $content . "\nendstream\nendobj\n";

foreach ($objects as $obj) {
    $offsets[] = strlen($pdf);
    $pdf .= $obj;
}

$xrefPos = strlen($pdf);
$pdf .= "xref\n0 6\n";
$pdf .= "0000000000 65535 f \n";
foreach ($offsets as $offset) {
    $pdf .= sprintf("%010d 00000 n \n", $offset);
}
$pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n" . $xrefPos . "\n%%EOF";

$filename = $messages['filename_prefix'] . '_' . date('Y-m-d') . '.pdf';
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($pdf));

echo $pdf;
exit();

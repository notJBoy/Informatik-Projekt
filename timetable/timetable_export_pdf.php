<?php
/**
 * Exportiert den Stundenplan des eingeloggten Nutzers als einfache PDF-Datei.
 */
require_once __DIR__ . '/../includes/api_helper.php';
require_once __DIR__ . '/../includes/i18n.php';

$user_id = require_auth();
$locale = learnhub_get_locale();
$isEnglish = $locale === 'en';

$messages = [
    'load_error' => $isEnglish ? 'Error while loading timetable data' : 'Fehler beim Laden der Stundenplandaten',
    'invalid_response' => $isEnglish ? 'Invalid response from backend' : 'Ungültige Antwort vom Backend',
    'title' => $isEnglish ? 'Timetable Export' : 'Stundenplan Export',
    'date_label' => $isEnglish ? 'Date' : 'Datum',
    'empty' => $isEnglish ? 'No entries available.' : 'Keine Einträge vorhanden.',
    'period_label' => $isEnglish ? 'Period' : 'Std',
    'room_label' => $isEnglish ? 'Room' : 'Raum',
    'filename_prefix' => $isEnglish ? 'timetable' : 'stundenplan',
];

$dateFormat = $isEnglish ? 'Y-m-d H:i' : 'd.m.Y H:i';

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

$lines = [
    $messages['title'],
    $messages['date_label'] . ': ' . date($dateFormat),
    str_repeat('-', 60),
];

if (empty($data)) {
    $lines[] = $messages['empty'];
} else {
    foreach ($data as $row) {
        $dayKey = strtolower((string)($row['day'] ?? ''));
        $lines[] = sprintf(
            '%s | %s. %s | %s | %s | %s %s',
            (string)($dayLabels[$dayKey] ?? ($row['day'] ?? '-')),
            (string)($row['period'] ?? '-'),
            $messages['period_label'],
            (string)($row['time'] ?? '-'),
            (string)($row['subject'] ?? '-'),
            $messages['room_label'],
            (string)($row['room'] ?? '-')
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

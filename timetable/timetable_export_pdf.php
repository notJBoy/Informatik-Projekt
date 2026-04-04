<?php
/**
 * Exportiert den Stundenplan des eingeloggten Nutzers als einfache PDF-Datei.
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

$lines = [
    'Stundenplan Export',
    'Datum: ' . date('d.m.Y H:i'),
    str_repeat('-', 60),
];

if (empty($data)) {
    $lines[] = 'Keine Einträge vorhanden.';
} else {
    foreach ($data as $row) {
        $lines[] = sprintf(
            '%s | %s. Std | %s | %s | Raum %s',
            (string)($row['day'] ?? '-'),
            (string)($row['period'] ?? '-'),
            (string)($row['time'] ?? '-'),
            (string)($row['subject'] ?? '-'),
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

$filename = 'stundenplan_' . date('Y-m-d') . '.pdf';
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($pdf));

echo $pdf;
exit();

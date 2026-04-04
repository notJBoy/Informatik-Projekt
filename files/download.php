<?php
/**
 * Dateizweck: Endpoint oder Seite "download" im Modul "files".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();

if (!isset($_GET['file_id'])) {
    die("Keine Datei angegeben");
}

$file_id = $_GET['file_id'];
$backend_url = BACKEND_BASE_URL . "/files/download/$user_id/$file_id";

// CURL init
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $backend_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HEADER, true); // Header auch zurückbekommen
curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);

$response = curl_exec($ch);

// Header & Body trennen
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$header = substr($response, 0, $header_size);
$body = substr($response, $header_size);

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    die("Datei konnte nicht heruntergeladen werden. HTTP $http_code");
}

// Original-Dateiname aus Content-Disposition Header extrahieren
if (preg_match('/filename="(.+?)"/', $header, $matches)) {
    $filename = $matches[1];
} else {
    $filename = "downloaded_file";
}

// Download an Browser weitergeben
header("Content-Type: application/octet-stream");
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($body));
header('Cache-Control: no-cache');
header('Pragma: no-cache');

echo $body;
exit();
?>

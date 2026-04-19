<?php
/**
 * Dateizweck: Endpoint oder Seite "delete" im Modul "files".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$filesTabRedirect = "Location: ../current_dashboard.php?tab=dateien";

$user_id = require_auth();

if (!isset($_GET['file_id'])) {
    header($filesTabRedirect . "&delete=error");
    exit();
}

$file_id = $_GET['file_id'];

$backend_url = BACKEND_BASE_URL . "/files/$user_id/$file_id";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $backend_url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    curl_close($ch);
    header($filesTabRedirect . "&delete=error");
    exit();
}

curl_close($ch);

if ($httpCode < 200 || $httpCode >= 300) {
    header($filesTabRedirect . "&delete=error");
    exit();
}

header($filesTabRedirect . "&delete=success");
exit();

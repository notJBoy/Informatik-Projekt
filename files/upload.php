<?php
/**
 * Dateizweck: Endpoint oder Seite "upload" im Modul "files".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$filesTabRedirect = "Location: ../current_dashboard.php?tab=dateien";

$user_id = require_auth();

if (!isset($_FILES['file'])) {
    header($filesTabRedirect . "&upload=error");
    exit();
}

$subject = trim($_POST['subject'] ?? '');
if ($subject === '') {
    header($filesTabRedirect . "&upload=error");
    exit();
}

$file = $_FILES['file'];

$backend_url = BACKEND_BASE_URL . "/files/upload/$user_id";

$ch = curl_init();

$data = [
    'subject' => $subject,
    'file' => new CURLFile(
        $file['tmp_name'],
        $file['type'],
        $file['name']
    )
];

curl_setopt($ch, CURLOPT_URL, $backend_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    curl_close($ch);
    header($filesTabRedirect . "&upload=error");
    exit();
}

curl_close($ch);

if ($httpCode >= 200 && $httpCode < 300) {
    header($filesTabRedirect . "&upload=success");
    exit();
}

header($filesTabRedirect . "&upload=error");
exit();

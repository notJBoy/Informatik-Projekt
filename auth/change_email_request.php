<?php
/**
 * Dateizweck: Endpoint oder Seite "change_email_request" im Modul "auth".
 * Hinweis: Leitet die E-Mail-Aenderungsanfrage an das Backend weiter.
 */
require_once __DIR__ . '/../includes/api_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Methode nicht erlaubt"]);
    exit();
}

$user_id = require_auth();

$input = json_decode(file_get_contents('php://input'), true);
$new_email = trim((string)($input['new_email'] ?? ''));

if ($new_email === '') {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Neue E-Mail-Adresse fehlt"]);
    exit();
}

$payload = json_encode([
    'new_email' => $new_email
]);

backend_request('PUT', "/auth/change-email/$user_id", $payload);

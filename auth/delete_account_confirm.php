<?php
/**
 * Dateizweck: Endpoint oder Seite "delete_account_confirm" im Modul "auth".
 * Hinweis: Leitet die finale Account-Loeschung an das Backend weiter.
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
$verification_id = trim((string)($input['verification_id'] ?? ''));
$code = trim((string)($input['code'] ?? ''));

if ($verification_id === '' || $code === '') {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Verifizierungsdaten fehlen"]);
    exit();
}

$payload = json_encode([
    'verification_id' => $verification_id,
    'code' => $code
]);

backend_request('POST', "/auth/delete-account/confirm/$user_id", $payload);

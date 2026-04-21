<?php
/**
 * Dateizweck: Endpoint oder Seite "delete_account_request_code" im Modul "auth".
 * Hinweis: Leitet den Request fuer den Loesch-Code an das Backend weiter.
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
$password = (string)($input['password'] ?? '');

if ($password === '') {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Passwort fehlt"]);
    exit();
}

$payload = json_encode([
    'password' => $password
]);

backend_request('POST', "/auth/delete-account/request-code/$user_id", $payload);

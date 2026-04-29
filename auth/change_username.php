<?php
/**
 * Dateizweck: Endpoint oder Seite "change_username" im Modul "auth".
 * Hinweis: Leitet die Benutzername-Aenderung an das Backend weiter.
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
$new_username = trim((string)($input['new_username'] ?? ''));

if ($new_username === '') {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Neuer Benutzername fehlt"]);
    exit();
}

$payload = json_encode([
    'new_username' => $new_username,
]);

backend_request('PUT', "/auth/change-username/$user_id", $payload);

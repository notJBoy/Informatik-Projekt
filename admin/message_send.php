<?php
/**
 * Dateizweck: Endpoint oder Seite "message_send" im Modul "admin".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_admin();
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Ung\u00fcltige Eingabe"]);
    exit();
}

$title = trim((string)($input['title'] ?? ''));
$body = trim((string)($input['body'] ?? ''));
$recipient_user_id = trim((string)($input['recipient_user_id'] ?? ''));

if ($title === '') {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Titel fehlt"]);
    exit();
}

if ($body === '') {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Nachricht fehlt"]);
    exit();
}

$payload = json_encode([
    'title' => $title,
    'body' => $body,
    'recipient_user_id' => $recipient_user_id === '' ? null : $recipient_user_id
]);

backend_request('POST', "/admin/messages/$user_id", $payload);

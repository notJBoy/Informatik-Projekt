<?php
/**
 * Dateizweck: Endpoint oder Seite "todo_add" im Modul "todos".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['title'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Titel fehlt"]);
    exit();
}

$payload = json_encode([
    'title'    => $input['title'],
    'subject'  => $input['subject']  ?? '',
    'due_date' => $input['due_date'] ?? '',
    'priority' => $input['priority'] ?? 'medium'
]);

backend_request('POST', "/todos/$user_id", $payload);

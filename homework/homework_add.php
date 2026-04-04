<?php
/**
 * Dateizweck: Endpoint oder Seite "homework_add" im Modul "homework".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['day']) || empty($input['title']) || !isset($input['period'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Ung\u00fcltige Eingabedaten"]);
    exit();
}

$payload = json_encode([
    'day' => $input['day'],
    'period' => (int)$input['period'],
    'title' => $input['title']
]);

backend_request('POST', "/homework/$user_id", $payload);

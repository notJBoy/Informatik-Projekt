<?php
/**
 * Dateizweck: Endpoint oder Seite "subject_add" im Modul "subjects".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['name']) || empty($input['color'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Ung\u00fcltige Eingabedaten"]);
    exit();
}

$payload = json_encode([
    'name'  => $input['name'],
    'color' => $input['color']
]);

backend_request('POST', "/subjects/$user_id", $payload);

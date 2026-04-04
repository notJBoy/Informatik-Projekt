<?php
/**
 * Dateizweck: Endpoint oder Seite "grade_add" im Modul "grades".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['subject']) || $input['value'] === null) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Ung\u00fcltige Eingabedaten"]);
    exit();
}

$payload = json_encode([
    'subject'     => $input['subject'],
    'value'       => $input['value'],
    'weight'      => isset($input['weight']) && is_numeric($input['weight']) && $input['weight'] > 0 ? $input['weight'] : 1,
    'description' => $input['description'] ?? ''
]);

backend_request('POST', "/grades/$user_id", $payload);

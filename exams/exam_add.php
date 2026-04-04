<?php
/**
 * Dateizweck: Endpoint oder Seite "exam_add" im Modul "exams".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['subject']) || empty($input['date'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Ung\u00fcltige Eingabedaten"]);
    exit();
}

$payload = json_encode([
    'subject' => $input['subject'],
    'date' => $input['date'],
    'topic' => $input['topic'] ?? '',
    'period' => isset($input['period']) && $input['period'] !== '' ? (int)$input['period'] : null,
    'period_end' => isset($input['period_end']) && $input['period_end'] !== '' ? (int)$input['period_end'] : null
]);

backend_request('POST', "/exams/$user_id", $payload);

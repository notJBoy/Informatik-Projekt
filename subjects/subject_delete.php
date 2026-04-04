<?php
/**
 * Dateizweck: Endpoint oder Seite "subject_delete" im Modul "subjects".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['id'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Ung\u00fcltige Eingabedaten"]);
    exit();
}

$subject_id = $input['id'];
backend_request('DELETE', "/subjects/$user_id/$subject_id");

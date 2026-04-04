<?php
/**
 * Dateizweck: Endpoint oder Seite "exam_delete" im Modul "exams".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();

if (!isset($_GET['exam_id'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "exam_id fehlt"]);
    exit();
}

$exam_id = $_GET['exam_id'];
backend_request('DELETE', "/exams/$user_id/$exam_id");

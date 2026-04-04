<?php
/**
 * Dateizweck: Endpoint oder Seite "grade_delete" im Modul "grades".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();

if (!isset($_GET['grade_id'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "grade_id fehlt"]);
    exit();
}

$grade_id = $_GET['grade_id'];
backend_request('DELETE', "/grades/$user_id/$grade_id");

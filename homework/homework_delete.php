<?php
/**
 * Dateizweck: Endpoint oder Seite "homework_delete" im Modul "homework".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();

if (!isset($_GET['homework_id'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "homework_id fehlt"]);
    exit();
}

$homework_id = $_GET['homework_id'];
backend_request('DELETE', "/homework/$user_id/$homework_id");

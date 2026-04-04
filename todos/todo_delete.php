<?php
/**
 * Dateizweck: Endpoint oder Seite "todo_delete" im Modul "todos".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();

if (!isset($_GET['todo_id'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "todo_id fehlt"]);
    exit();
}

$todo_id = $_GET['todo_id'];
backend_request('DELETE', "/todos/$user_id/$todo_id");

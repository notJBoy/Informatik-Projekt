<?php
/**
 * Dateizweck: Endpoint oder Seite "message_delete" im Modul "admin".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_admin();

if (!isset($_GET['message_id'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "message_id fehlt"]);
    exit();
}

$message_id = $_GET['message_id'];
backend_request('DELETE', "/admin/messages/$user_id/$message_id");

http_response_code($httpCode ?: 500);
echo $response;
exit();

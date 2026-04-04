<?php
/**
 * Dateizweck: Endpoint oder Seite "user_role_update" im Modul "admin".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_admin();
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Ung\u00fcltige Eingabe"]);
    exit();
}

$target_user_id = trim((string)($input['user_id'] ?? ''));
$role = strtolower(trim((string)($input['role'] ?? '')));

if ($target_user_id === '') {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Nutzer-ID fehlt"]);
    exit();
}

if ($role !== 'admin' && $role !== 'user') {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Ung\u00fcltige Rolle"]);
    exit();
}

$payload = json_encode(['role' => $role]);
backend_request('PUT', "/admin/users/$user_id/$target_user_id/role", $payload);

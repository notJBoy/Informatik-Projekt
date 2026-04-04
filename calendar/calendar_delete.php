<?php
/**
 * Dateizweck: Endpoint oder Seite "calendar_delete" im Modul "calendar".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();

if (!isset($_GET['event_id'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "event_id fehlt"]);
    exit();
}

$event_id = $_GET['event_id'];
$delete_scope = $_GET['delete_scope'] ?? 'series';
$occurrence_date = $_GET['occurrence_date'] ?? null;

$query = http_build_query(array_filter([
    'delete_scope' => $delete_scope,
    'occurrence_date' => $occurrence_date,
], static fn ($value) => $value !== null && $value !== ''));

$path = "/calendar-extras/$user_id/$event_id" . ($query ? "?$query" : '');
backend_request('DELETE', $path);

<?php
/**
 * Dateizweck: Endpoint oder Seite "calendar_add" im Modul "calendar".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['title']) || empty($input['date'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Ung\u00fcltige Eingabedaten"]);
    exit();
}

$color = isset($input['color']) ? trim((string)$input['color']) : '#0d6efd';
if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
    $color = '#0d6efd';
}

$start_time = isset($input['start_time']) ? trim((string)$input['start_time']) : '';
$end_time = isset($input['end_time']) ? trim((string)$input['end_time']) : '';
$has_start = $start_time !== '';
$has_end = $end_time !== '';

if ($has_start xor $has_end) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Bitte Start- und Endzeit angeben"]);
    exit();
}

if ($has_start) {
    if (!preg_match('/^([01]\\d|2[0-3]):[0-5]\\d$/', $start_time) || !preg_match('/^([01]\\d|2[0-3]):[0-5]\\d$/', $end_time)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(["error" => "Ung\u00fcltiges Zeitformat"]);
        exit();
    }
    if ($end_time <= $start_time) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(["error" => "Endzeit muss nach Startzeit liegen"]);
        exit();
    }
}

$payload = json_encode([
    'title' => $input['title'],
    'date' => $input['date'],
    'recurrence' => $input['recurrence'] ?? 'none',
    'description' => $input['description'] ?? '',
    'color' => $color,
    'start_time' => $has_start ? $start_time : null,
    'end_time' => $has_start ? $end_time : null
]);

backend_request('POST', "/calendar-extras/$user_id", $payload);

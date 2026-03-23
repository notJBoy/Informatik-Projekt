<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Nicht eingeloggt"]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['title']) || empty($input['date'])) {
    http_response_code(400);
    echo json_encode(["error" => "Ungültige Eingabedaten"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$backend_url = "http://127.0.0.1:8000/calendar-extras/$user_id";

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
    echo json_encode(["error" => "Bitte Start- und Endzeit angeben"]);
    exit();
}

if ($has_start) {
    if (!preg_match('/^([01]\\d|2[0-3]):[0-5]\\d$/', $start_time) || !preg_match('/^([01]\\d|2[0-3]):[0-5]\\d$/', $end_time)) {
        http_response_code(400);
        echo json_encode(["error" => "Ungültiges Zeitformat"]);
        exit();
    }
    if ($end_time <= $start_time) {
        http_response_code(400);
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

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $backend_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

http_response_code($httpCode);
header('Content-Type: application/json');
echo $response;
exit();
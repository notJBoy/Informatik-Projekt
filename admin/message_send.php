<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Nicht eingeloggt"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(["error" => "Ungültige Eingabe"]);
    exit();
}

$title = trim((string)($input['title'] ?? ''));
$body = trim((string)($input['body'] ?? ''));
$recipient_user_id = trim((string)($input['recipient_user_id'] ?? ''));

if ($title === '') {
    http_response_code(400);
    echo json_encode(["error" => "Titel fehlt"]);
    exit();
}

if ($body === '') {
    http_response_code(400);
    echo json_encode(["error" => "Nachricht fehlt"]);
    exit();
}

$backend_url = "http://127.0.0.1:8000/admin/messages/$user_id";
$payload = json_encode([
    'title' => $title,
    'body' => $body,
    'recipient_user_id' => $recipient_user_id === '' ? null : $recipient_user_id
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

if ($response === false || $response === null) {
    http_response_code(502);
    echo json_encode(["error" => "Backend nicht erreichbar"]);
    exit();
}

http_response_code($httpCode ?: 500);
echo $response;
exit();
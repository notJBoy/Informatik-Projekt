<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Nicht eingeloggt"]);
    exit();
}

$user_id = $_SESSION['user_id'];

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['title'])) {
    http_response_code(400);
    echo json_encode(["error" => "Titel fehlt"]);
    exit();
}

$backend_url = "http://127.0.0.1:8000/todos/$user_id";

$payload = json_encode([
    'title'    => $input['title'],
    'subject'  => $input['subject']  ?? '',
    'due_date' => $input['due_date'] ?? '',
    'priority' => $input['priority'] ?? 'medium'
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

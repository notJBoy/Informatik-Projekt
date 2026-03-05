<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Nicht eingeloggt"]);
    exit();
}
$user_id = $_SESSION['user_id'];
$deck_id = $_GET['deck_id'] ?? '';
if (!$deck_id) {
    http_response_code(400);
    echo json_encode(["error" => "deck_id fehlt"]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['front']) || empty($input['back'])) {
    http_response_code(400);
    echo json_encode(["error" => "front und back erforderlich"]);
    exit();
}

$backend_url = "http://127.0.0.1:8000/flashcard-cards/$user_id/deck/" . urlencode($deck_id);
$payload = json_encode([
    'front' => $input['front'],
    'back'  => $input['back'],
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $backend_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

http_response_code($httpCode);
header('Content-Type: application/json');
echo $response;
exit();

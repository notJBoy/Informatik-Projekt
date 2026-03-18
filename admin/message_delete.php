<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Nicht eingeloggt"]);
    exit();
}

if (!isset($_GET['message_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "message_id fehlt"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$message_id = $_GET['message_id'];
$backend_url = "http://127.0.0.1:8000/admin/messages/$user_id/$message_id";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $backend_url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

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
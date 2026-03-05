<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Nicht eingeloggt"]);
    exit();
}

if (!isset($_GET['todo_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "todo_id fehlt"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$todo_id = $_GET['todo_id'];
$backend_url = "http://127.0.0.1:8000/todos/$user_id/$todo_id/toggle";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $backend_url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

http_response_code($httpCode);
header('Content-Type: application/json');
echo $response;
exit();

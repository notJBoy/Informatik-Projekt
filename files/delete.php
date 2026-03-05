<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['file_id'])) {
    header("Location: dashboard.php?delete=error");
    exit();
}

$user_id = $_SESSION['user_id'];
$file_id = $_GET['file_id'];

$backend_url = "http://127.0.0.1:8000/files/$user_id/$file_id";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $backend_url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    curl_close($ch);
    header("Location: dashboard.php?delete=error");
    exit();
}

curl_close($ch);

/*
WICHTIG:
DELETE gibt oft 204 zurück → response leer
Das ist trotzdem Erfolg!
*/

header("Location: ../current_dashboard.php");

exit();

<?php
session_start();

$filesTabRedirect = "Location: ../current_dashboard.php?tab=dateien";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_FILES['file'])) {
    header($filesTabRedirect . "&upload=error");
    exit();
}

$subject = $_POST['subject'];
$file = $_FILES['file'];

$backend_url = "http://127.0.0.1:8000/files/upload/$user_id";

$ch = curl_init();

$data = [
    'subject' => $subject,
    'file' => new CURLFile(
        $file['tmp_name'],
        $file['type'],
        $file['name']
    )
];

curl_setopt($ch, CURLOPT_URL, $backend_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    curl_close($ch);
    header($filesTabRedirect . "&upload=error");
    exit();
}

curl_close($ch);

if ($httpCode >= 200 && $httpCode < 300) {
    header($filesTabRedirect . "&upload=success");
    exit();
}

header($filesTabRedirect . "&upload=error");
exit();

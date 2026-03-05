<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Nicht eingeloggt");
}

$user_id = $_SESSION['user_id'];

if (!isset($_FILES['file'])) {
    die("Keine Datei empfangen");
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

if ($response === false) {
    die("cURL Fehler: " . curl_error($ch));
}

curl_close($ch);

echo "<pre>";
echo "Backend Antwort:\n";
echo $response;
echo "</pre>";
exit();

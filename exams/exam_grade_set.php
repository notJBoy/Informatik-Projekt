<?php
/**
 * Dateizweck: Endpoint oder Seite "exam_grade_set" im Modul "exams".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['exam_id']) || empty($input['subject']) || 
    $input['value'] === null || $input['value'] === '' || 
    $input['weight'] === null || $input['weight'] === '') {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Ungültige Eingabedaten"]);
    exit();
}

$exam_id = $input['exam_id'];
$subject = $input['subject'];
$value = $input['value'];
$weight = $input['weight'];
$description = $input['description'] ?? '';

// Validate values
if (!is_numeric($value) || $value < 0 || $value > 15) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Punkte müssen zwischen 0 und 15 liegen"]);
    exit();
}

if (!is_numeric($weight) || $weight <= 0) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Gewichtung muss größer als 0 sein"]);
    exit();
}

// Add description with exam context
$full_description = "Klassenarbeit - " . $description;

// First, delete any existing grade for this exam's subject with "Klassenarbeit" description
$backend_url = BACKEND_BASE_URL . "/grades/$user_id";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $backend_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$grades_response = curl_exec($ch);
curl_close($ch);

$existing_grades = json_decode($grades_response, true) ?: [];
foreach ($existing_grades as $grade) {
    if ($grade['subject'] === $subject && strpos($grade['description'] ?? '', 'Klassenarbeit') === 0) {
        // Delete this grade
        $delete_url = BACKEND_BASE_URL . "/grades/$user_id/" . $grade['id'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $delete_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_exec($ch);
        curl_close($ch);
        break;
    }
}

// Save new grade to grades table via API
$backend_url = BACKEND_BASE_URL . "/grades/$user_id";

$payload = json_encode([
    'subject' => $subject,
    'value' => (float)$value,
    'weight' => (float)$weight,
    'description' => $full_description
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $backend_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Also update exam with grade for reference
$update_backend_url = BACKEND_BASE_URL . "/exams/$user_id/$exam_id/grade";

$update_payload = json_encode([
    'grade' => (float)$value
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $update_backend_url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $update_payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

curl_exec($ch);
curl_close($ch);

http_response_code($httpCode);
header('Content-Type: application/json');
echo $response;
exit();

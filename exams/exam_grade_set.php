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
$grades_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($grades_response === false || $grades_http_code !== 200) {
    http_response_code($grades_http_code ?: 502);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Vorhandene Noten konnten nicht geladen werden"]);
    exit();
}

$existing_grades = json_decode($grades_response, true) ?: [];
$grade_to_replace = null;
$legacy_candidates = [];
foreach ($existing_grades as $grade) {
    if (($grade['source_exam_id'] ?? '') === $exam_id) {
        $grade_to_replace = $grade;
        break;
    }

    if (($grade['subject'] ?? '') === $subject && strpos($grade['description'] ?? '', 'Klassenarbeit') === 0) {
        $legacy_candidates[] = $grade;
    }
}

if ($grade_to_replace === null && count($legacy_candidates) === 1) {
    $grade_to_replace = $legacy_candidates[0];
}

if ($grade_to_replace !== null) {
    $delete_url = BACKEND_BASE_URL . "/grades/$user_id/" . $grade_to_replace['id'];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $delete_url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_exec($ch);
    $delete_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($delete_http_code < 200 || $delete_http_code >= 300) {
        http_response_code($delete_http_code ?: 502);
        header('Content-Type: application/json');
        echo json_encode(["error" => "Vorhandene Klassenarbeits-Note konnte nicht aktualisiert werden"]);
        exit();
    }
}

// Save new grade to grades table via API
$backend_url = BACKEND_BASE_URL . "/grades/$user_id";

$payload = json_encode([
    'subject' => $subject,
    'value' => (float)$value,
    'weight' => (float)$weight,
    'description' => $full_description,
    'source_exam_id' => $exam_id
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

if ($response === false || $httpCode < 200 || $httpCode >= 300) {
    http_response_code($httpCode ?: 502);
    header('Content-Type: application/json');
    echo $response !== false ? $response : json_encode(["error" => "Note konnte nicht gespeichert werden"]);
    exit();
}

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

$update_response = curl_exec($ch);
$update_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($update_response === false || $update_http_code < 200 || $update_http_code >= 300) {
    http_response_code($update_http_code ?: 502);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Die Klassenarbeit wurde gespeichert, aber die Verknüpfung zur Notenübersicht konnte nicht aktualisiert werden"]);
    exit();
}

http_response_code($httpCode);
header('Content-Type: application/json');
echo $response;
exit();

<?php
/**
 * Dateizweck: Endpoint oder Seite "card_add" im Modul "flashcards".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();
$deck_id = $_GET['deck_id'] ?? '';
if (!$deck_id) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "deck_id fehlt"]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['front']) || empty($input['back'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "front und back erforderlich"]);
    exit();
}

$payload = json_encode([
    'front' => $input['front'],
    'back'  => $input['back'],
]);

backend_request('POST', "/flashcard-cards/$user_id/deck/" . urlencode($deck_id), $payload);

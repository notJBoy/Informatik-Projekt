<?php
/**
 * Dateizweck: Endpoint oder Seite "deck_update" im Modul "flashcards".
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
if (!$input) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Kein Body"]);
    exit();
}

$payload = json_encode($input);
backend_request('PUT', "/flashcard-decks/$user_id/" . urlencode($deck_id), $payload);

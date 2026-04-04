<?php
/**
 * Dateizweck: Endpoint oder Seite "deck_copy" im Modul "flashcards".
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

backend_request('POST', "/flashcard-decks/$user_id/copy/" . urlencode($deck_id), '{}');

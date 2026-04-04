<?php
/**
 * Dateizweck: Endpoint oder Seite "deck_create" im Modul "flashcards".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['name'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Name fehlt"]);
    exit();
}

$payload = json_encode([
    'name'        => $input['name'],
    'subject'     => $input['subject']     ?? '',
    'description' => $input['description'] ?? '',
    'public'      => $input['public']      ?? false,
]);

backend_request('POST', "/flashcard-decks/$user_id", $payload);

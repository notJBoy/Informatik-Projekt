<?php
/**
 * Dateizweck: Endpoint oder Seite "card_delete" im Modul "flashcards".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();
$card_id = $_GET['card_id'] ?? '';
if (!$card_id) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["error" => "card_id fehlt"]);
    exit();
}

backend_request('DELETE', "/flashcard-cards/$user_id/" . urlencode($card_id));

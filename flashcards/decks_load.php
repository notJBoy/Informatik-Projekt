<?php
/**
 * Dateizweck: Endpoint oder Seite "decks_load" im Modul "flashcards".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();
backend_request('GET', "/flashcard-decks/$user_id");

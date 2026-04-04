<?php
/**
 * Dateizweck: Endpoint oder Seite "subjects_load" im Modul "subjects".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();
backend_request('GET', "/subjects/$user_id");

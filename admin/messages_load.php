<?php
/**
 * Dateizweck: Endpoint oder Seite "messages_load" im Modul "admin".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();
backend_request('GET', "/messages/$user_id");

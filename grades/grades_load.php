<?php
/**
 * Dateizweck: Endpoint oder Seite "grades_load" im Modul "grades".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();
backend_request('GET', "/grades/$user_id");

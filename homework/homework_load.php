<?php
/**
 * Dateizweck: Endpoint oder Seite "homework_load" im Modul "homework".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();
backend_request('GET', "/homework/$user_id");

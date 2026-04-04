<?php
/**
 * Dateizweck: Endpoint oder Seite "exams_load" im Modul "exams".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();
backend_request('GET', "/exams/$user_id");

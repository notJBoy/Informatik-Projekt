<?php
/**
 * Dateizweck: Endpoint oder Seite "timetable_save" im Modul "timetable".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();
$input = file_get_contents('php://input');
backend_request('POST', "/timetable/$user_id/bulk", $input);

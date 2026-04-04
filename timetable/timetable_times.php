<?php
/**
 * Dateizweck: Endpoint oder Seite "timetable_times" im Modul "timetable".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();
backend_request('GET', "/timetable_times/$user_id");

<?php
/**
 * Dateizweck: Endpoint oder Seite "calendar_load" im Modul "calendar".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();
backend_request('GET', "/calendar-extras/$user_id");

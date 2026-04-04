<?php
/**
 * Dateizweck: Endpoint oder Seite "admin_stats" im Modul "admin".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
session_start();
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_admin();
backend_request('GET', "/admin/stats/$user_id");

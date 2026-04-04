<?php
/**
 * Dateizweck: Endpoint oder Seite "todo_load" im Modul "todos".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();
backend_request('GET', "/todos/$user_id");

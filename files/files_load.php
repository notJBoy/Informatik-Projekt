<?php
/**
 * Dateizweck: Lädt die Dateien des eingeloggten Benutzers vom Backend.
 */
session_start();
require_once __DIR__ . '/../includes/api_helper.php';

$user_id = require_auth();
backend_request('GET', "/files/$user_id");

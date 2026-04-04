<?php
/**
 * Dateizweck: Gemeinsame Hilfsfunktionen fuer API-Endpoints.
 * - Session-Authentifizierung
 * - CSRF-Token-Validierung
 * - cURL-Fehlerbehandlung
 */

define('BACKEND_BASE_URL', 'http://127.0.0.1:8000');

/**
 * Stellt sicher, dass der Benutzer eingeloggt ist.
 * Gibt die user_id zurueck oder beendet mit 401.
 */
function require_auth(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(["error" => "Nicht eingeloggt"]);
        exit();
    }
    return $_SESSION['user_id'];
}

/**
 * Stellt sicher, dass der Benutzer Admin-Rolle hat.
 * Muss nach require_auth() aufgerufen werden.
 */
function require_admin(): string {
    $user_id = require_auth();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(["error" => "Nur Admins haben Zugriff"]);
        exit();
    }
    return $user_id;
}

/**
 * Fuehrt eine cURL-Anfrage an das Backend aus und gibt das Ergebnis zurueck.
 * Behandelt Fehler (Backend nicht erreichbar etc.).
 */
function backend_request(string $method, string $path, ?string $json_payload = null): void {
    $url = BACKEND_BASE_URL . $path;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    switch (strtoupper($method)) {
        case 'POST':
            curl_setopt($ch, CURLOPT_POST, true);
            if ($json_payload !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json_payload);
            }
            break;
        case 'PUT':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($json_payload !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json_payload);
            }
            break;
        case 'PATCH':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            if ($json_payload !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json_payload);
            }
            break;
        case 'DELETE':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;
        case 'GET':
        default:
            break;
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        http_response_code(502);
        header('Content-Type: application/json');
        echo json_encode(["error" => "Backend nicht erreichbar: " . $curlError]);
        exit();
    }

    http_response_code($httpCode ?: 500);
    header('Content-Type: application/json');
    echo $response;
    exit();
}

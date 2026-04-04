<?php
/**
 * Dateizweck: Endpoint oder Seite "logout" im Modul "auth".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
session_start();

// Alle Session-Variablen loeschen
$_SESSION = [];

// Session-Cookie loeschen
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Session zerstoeren
session_destroy();

header("Location: login.php");
exit();

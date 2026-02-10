<?php
// Einfache Session-Startung
session_start();

// Fehlernachricht (falls vorhanden)
$error_message = '';

// Wenn das Formular abgesendet wird
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // API-Endpunkt für Login
    $url = 'http://localhost:8000/auth/login';

    // POST-Daten vorbereiten
    $data = json_encode([
        'username' => $username,
        'password' => $password
    ]);

    // cURL-Anfrage zur API
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Überprüfen, ob die Anmeldung erfolgreich war
    if ($http_code == 200) {
        $_SESSION['username'] = $username;  // Benutzername speichern
        header('Location: current_dashboard.php');  // Weiterleitung
        exit();
    } else {
        $error_message = 'Ungültige Anmeldedaten';
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LearnHub Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .container h2 {
            text-align: center;
            margin-bottom: 1rem;
        }
        .input-field {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .button:hover {
            background-color: #45a049;
        }
        .error-message {
            color: red;
            text-align: center;
            font-size: 0.9rem;
        }
        .register-link {
            text-align: center;
            margin-top: 1rem;
        }
        .register-link a {
            text-decoration: none;
            color: #4CAF50;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Login</h2>

        <!-- Fehlernachricht anzeigen -->
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Login-Formular -->
        <form method="POST" action="login.php">
            <input type="text" name="username" class="input-field" placeholder="Benutzername" required><br>
            <input type="password" name="password" class="input-field" placeholder="Passwort" required><br>
            <button type="submit" class="button">Anmelden</button>
        </form>

        <div class="register-link">
            <p>Noch keinen Account? <a href="register.php">Jetzt registrieren</a></p>
        </div>
    </div>

</body>
</html>

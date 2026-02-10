<?php
// Fehlernachricht (falls vorhanden)
$error_message = '';
$success_message = '';

// Wenn das Formular abgesendet wird
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // API-Endpunkt für Registrierung
    $url = 'http://localhost:8000/auth/register';

    // POST-Daten vorbereiten
    $data = json_encode([
        'username' => $username,
        'email' => $email,
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

    // Überprüfen, ob die Registrierung erfolgreich war
    if ($http_code == 200) {
        $success_message = 'Registrierung erfolgreich! Du kannst dich jetzt einloggen.';
    } else {
        $error_message = 'Ein Fehler ist aufgetreten. Bitte versuche es erneut.';
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LearnHub Registrierung</title>
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
        .error-message, .success-message {
            text-align: center;
            font-size: 0.9rem;
        }
        .error-message {
            color: red;
        }
        .success-message {
            color: green;
        }
        .login-link {
            text-align: center;
            margin-top: 1rem;
        }
        .login-link a {
            text-decoration: none;
            color: #4CAF50;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Registrierung</h2>

        <!-- Erfolgsmeldung -->
        <?php if ($success_message): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <!-- Fehlernachricht -->
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Registrierungs-Formular -->
        <form method="POST" action="register.php">
            <input type="text" name="username" class="input-field" placeholder="Benutzername" required><br>
            <input type="email" name="email" class="input-field" placeholder="E-Mail" required><br>
            <input type="password" name="password" class="input-field" placeholder="Passwort" required><br>
            <button type="submit" class="button">Registrieren</button>
        </form>

        <div class="login-link">
            <p>Bereits ein Account? <a href="login.php">Jetzt einloggen</a></p>
        </div>
    </div>

</body>
</html>

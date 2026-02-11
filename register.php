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
<html lang="de" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LearnHub Registrierung</title>
    <style>
        /* Light Mode Colors */
        :root {
            --color-bg-primary:    #f8f9fa;
            --color-bg-secondary:  #ffffff;
            --color-bg-surface:    #ffffff;
            --color-bg-hover:      #f1f3f5;

            --color-text-primary:   #1a1a1a;
            --color-text-secondary: #6c757d;
            --color-text-muted:     #adb5bd;

            --color-primary:        #0d6efd;
            --color-primary-hover:  #0b5ed7;
            --color-primary-active: #0a58ca;

            --color-border:       #dee2e6;
            --color-border-light: #e9ecef;

            --color-success: #198754;
            --color-warning: #ffc107;
            --color-danger:  #dc3545;
            --color-info:    #0dcaf0;
        }

        /* Dark Mode Colors */
        [data-theme="dark"] {
            --color-bg-primary:    #0f172a;
            --color-bg-secondary:  #1e293b;
            --color-bg-surface:    #1e293b;
            --color-bg-hover:      #334155;

            --color-text-primary:   #f1f5f9;
            --color-text-secondary: #cbd5e1;
            --color-text-muted:     #94a3b8;

            --color-primary:        #38bdf8;
            --color-primary-hover:  #0ea5e9;
            --color-primary-active: #0284c7;

            --color-border:       #334155;
            --color-border-light: #475569;

            --color-success: #10b981;
            --color-warning: #f59e0b;
            --color-danger:  #ef4444;
            --color-info:    #06b6d4;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: var(--color-bg-primary);
            color: var(--color-text-primary);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background-color: var(--color-bg-surface);
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(15, 23, 42, 0.25);
            width: 320px;
            border: 1px solid var(--color-border);
        }

        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .container h2 {
            margin: 0;
            font-size: 1.4rem;
        }

        .theme-toggle {
            background: none;
            border: 1px solid var(--color-border-light);
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 0.8rem;
            cursor: pointer;
            color: var(--color-text-secondary);
            background-color: var(--color-bg-secondary);
        }

        .theme-toggle:hover {
            background-color: var(--color-bg-hover);
        }

        .input-field {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid var(--color-border);
            background-color: var(--color-bg-secondary);
            color: var(--color-text-primary);
        }

        .input-field::placeholder {
            color: var(--color-text-muted);
        }

        .input-field:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.15);
        }

        .button {
            width: 100%;
            padding: 10px;
            background-color: var(--color-primary);
            color: #ffffff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 0.5rem;
        }

        .button:hover {
            background-color: var(--color-primary-hover);
        }

        .error-message, .success-message {
            text-align: center;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .error-message {
            color: var(--color-danger);
        }

        .success-message {
            color: var(--color-success);
        }

        .login-link {
            text-align: center;
            margin-top: 1rem;
            color: var(--color-text-secondary);
            font-size: 0.9rem;
        }

        .login-link a {
            text-decoration: none;
            color: var(--color-primary);
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header-row">
            <h2>Registrierung</h2>
            <button class="theme-toggle" type="button" id="themeToggle">
                Darkmode
            </button>
        </div>

        <?php if ($success_message): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <input type="text" name="username" class="input-field" placeholder="Benutzername" required>
            <input type="email" name="email" class="input-field" placeholder="E-Mail" required>
            <input type="password" name="password" class="input-field" placeholder="Passwort" required>
            <button type="submit" class="button">Registrieren</button>
        </form>

        <div class="login-link">
            <p>Bereits ein Account? <a href="login.php">Jetzt einloggen</a></p>
        </div>
    </div>

    <script>
        (function () {
            const root = document.documentElement;
            const toggleBtn = document.getElementById('themeToggle');
            const storedTheme = localStorage.getItem('theme');

            if (storedTheme === 'dark') {
                root.setAttribute('data-theme', 'dark');
                toggleBtn.textContent = 'Lightmode';
            }

            toggleBtn.addEventListener('click', function () {
                const current = root.getAttribute('data-theme') || 'light';
                const next = current === 'light' ? 'dark' : 'light';
                root.setAttribute('data-theme', next);
                localStorage.setItem('theme', next);
                toggleBtn.textContent = next === 'dark' ? 'Lightmode' : 'Darkmode';
            });
        })();
    </script>

</body>
</html>

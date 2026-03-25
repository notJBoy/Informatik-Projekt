<?php
/**
 * Dateizweck: Endpoint oder Seite "register" im Modul "auth".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
// Sichere Session-Cookie-Konfiguration vor session_start()
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

// CSRF-Token generieren
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fehlernachricht (falls vorhanden)
$error_message = '';
$success_message = '';
$verification_id = '';
$username_value = '';
$email_value = '';
$is_confirm_step = false;


// Wenn das Formular abgesendet wird
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF-Token prüfen
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_message = 'Ungültige Anfrage. Bitte Seite neu laden und erneut versuchen.';
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } else {
    $action = $_POST['action'] ?? 'request_code';

    if ($action === 'request_code') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $username_value = $username;
        $email_value = $email;

        $url = 'http://localhost:8000/auth/register';
        $data = json_encode([
            'username' => $username,
            'email' => $email,
            'password' => $password
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = json_decode($response, true);

        if ($http_code == 200) {
            $verification_id = $json['verification_id'] ?? '';
            $success_message = 'Verifizierungscode wurde gesendet. Bitte Code eingeben, um die Registrierung abzuschließen.';
        } else {
            $error_message = $json['detail'] ?? 'Ein Fehler ist aufgetreten. Bitte versuche es erneut.';
        }
    } else {
        $verification_id = trim($_POST['verification_id'] ?? '');
        $code = trim($_POST['verification_code'] ?? '');

        $url = 'http://localhost:8000/auth/register/confirm';
        $data = json_encode([
            'verification_id' => $verification_id,
            'code' => $code
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = json_decode($response, true);

        if ($http_code == 200) {
            $success_message = 'Registrierung erfolgreich! Du kannst dich jetzt einloggen.';
            $verification_id = '';
            $username_value = '';
            $email_value = '';
        } else {
            $error_message = $json['detail'] ?? 'Verifizierung fehlgeschlagen. Bitte erneut versuchen.';
        }
    }
    } // Ende CSRF-else
}

$is_confirm_step = !empty($verification_id);
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

        .register-progress {
            position: relative;
            height: 6px;
            border-radius: 999px;
            background-color: var(--color-border-light);
            overflow: hidden;
            margin-bottom: 0.85rem;
        }

        .register-progress-fill {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--color-primary), var(--color-primary-hover));
        }

        .register-progress-fill.step-request {
            width: 50%;
            animation: progressStepOne 0.35s ease-out;
        }

        .register-progress-fill.step-confirm {
            width: 100%;
            animation: progressStepTwo 0.45s ease-out;
        }

        .step-label {
            font-size: 0.8rem;
            color: var(--color-text-secondary);
            margin-bottom: 0.5rem;
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

        .register-step {
            animation: stepFadeIn 0.28s ease-out;
        }

        @keyframes stepFadeIn {
            from {
                opacity: 0;
                transform: translateY(5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes progressStepOne {
            from {
                width: 0;
            }
            to {
                width: 50%;
            }
        }

        @keyframes progressStepTwo {
            from {
                width: 50%;
            }
            to {
                width: 100%;
            }
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

        <div class="register-progress" aria-hidden="true">
            <div class="register-progress-fill <?php echo $is_confirm_step ? 'step-confirm' : 'step-request'; ?>"></div>
        </div>
        <div class="step-label">
            <?php echo $is_confirm_step ? 'Schritt 2 von 2: Verifizierung bestätigen' : 'Schritt 1 von 2: Code anfordern'; ?>
        </div>

        <?php if ($success_message): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if (!$verification_id): ?>
            <form method="POST" action="register.php" class="register-step">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="action" value="request_code">
                <input type="text" name="username" class="input-field" placeholder="Benutzername" value="<?php echo htmlspecialchars($username_value); ?>" required>
                <input type="email" name="email" class="input-field" placeholder="E-Mail" value="<?php echo htmlspecialchars($email_value); ?>" required>
                <input type="password" name="password" class="input-field" placeholder="Passwort" required>
                <button type="submit" class="button">Code senden</button>
            </form>
        <?php else: ?>
            <form method="POST" action="register.php" class="register-step">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="action" value="confirm_code">
                <input type="hidden" name="verification_id" value="<?php echo htmlspecialchars($verification_id); ?>">
                <input type="text" name="verification_code" class="input-field" placeholder="Verifizierungscode" required>
                <button type="submit" class="button">Registrierung abschließen</button>
            </form>
        <?php endif; ?>

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



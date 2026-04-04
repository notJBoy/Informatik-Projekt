<?php
/**
 * Dateizweck: Endpoint oder Seite "login" im Modul "auth".
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
require_once __DIR__ . '/../includes/i18n.php';
require_once __DIR__ . '/../includes/api_helper.php';

if (isset($_GET['lang'])) {
    learnhub_set_locale($_GET['lang']);
}

$current_locale = learnhub_get_locale();
if (isset($_SESSION['user_id'])) {
    header("Location: ../current_dashboard.php");
    exit();
}

// CSRF-Token generieren
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fehlernachricht (falls vorhanden)
$error_message = '';

// Wenn das Formular abgesendet wird
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF-Token prüfen
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_message = t('auth.invalid_request');
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } else {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // API-Endpunkt für Login
    $url = BACKEND_BASE_URL . '/auth/login';

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

    if ($response === false) {
        $error_message = t('auth.server_unreachable');
    } else {
        $responseData = json_decode($response, true);

        if ($http_code == 200) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $responseData['user_id'] ?? '';
            $_SESSION['username'] = $responseData['username'] ?? '';
            $_SESSION['role'] = $responseData['role'] ?? 'user';

            header('Location: ../current_dashboard.php');
            exit();
        } else {
            if (isset($responseData['detail'])) {
                $error_message = $responseData['detail'];
            } else {
                $error_message = t('auth.login_failed');
            }
        }
    }

    curl_close($ch);
    } // Ende CSRF-else
}


?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($current_locale); ?>">
<head>
<script>
(function () {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        document.documentElement.setAttribute('data-theme', savedTheme);
    }
})();
</script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(t('site.login_title')); ?></title>
    <style>
        :root {
            --color-bg-primary:    #f0f4f8;
            --color-bg-secondary:  #ffffff;
            --color-bg-surface:    #ffffff;
            --color-bg-hover:      #e8edf3;
            --color-text-primary:   #0f172a;
            --color-text-secondary: #64748b;
            --color-text-muted:     #94a3b8;
            --color-primary:        #4f46e5;
            --color-primary-hover:  #4338ca;
            --color-border:       #e2e8f0;
            --color-border-light: #f1f5f9;
            --color-danger:  #dc2626;
        }

        [data-theme="dark"] {
            --color-bg-primary:    #0a0f1e;
            --color-bg-secondary:  #111827;
            --color-bg-surface:    #111827;
            --color-bg-hover:      #1e293b;
            --color-text-primary:   #f1f5f9;
            --color-text-secondary: #94a3b8;
            --color-text-muted:     #64748b;
            --color-primary:        #818cf8;
            --color-primary-hover:  #6366f1;
            --color-border:       #1e293b;
            --color-border-light: #263045;
            --color-danger:  #f87171;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--color-bg-primary);
            color: var(--color-text-primary);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 1.5rem;
        }

        /* Subtle mesh background */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 10%, rgba(79,70,229,0.1) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 85% 85%, rgba(99,102,241,0.08) 0%, transparent 55%);
            pointer-events: none;
            z-index: 0;
        }

        [data-theme="dark"] body::before {
            background:
                radial-gradient(ellipse 80% 60% at 20% 10%, rgba(79,70,229,0.2) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 85% 85%, rgba(129,140,248,0.12) 0%, transparent 55%);
        }

        .container {
            position: relative;
            z-index: 1;
            background-color: var(--color-bg-surface);
            padding: 2.5rem 2rem;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.12), 0 4px 12px rgba(15, 23, 42, 0.06);
            width: 100%;
            max-width: 380px;
            border: 1px solid var(--color-border);
            animation: cardIn 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes cardIn {
            from { opacity: 0; transform: translateY(24px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .logo-area {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 2rem;
            gap: 0.75rem;
        }

        .logo-icon {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            font-weight: 700;
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.35);
            letter-spacing: -0.02em;
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--color-text-primary);
            letter-spacing: -0.03em;
        }

        .logo-subtitle {
            font-size: 0.875rem;
            color: var(--color-text-secondary);
            margin-top: -0.5rem;
        }

        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .header-row h2 {
            font-size: 1.2rem;
            font-weight: 600;
            letter-spacing: -0.02em;
        }

        .theme-toggle {
            background: var(--color-bg-hover);
            border: 1px solid var(--color-border);
            border-radius: 999px;
            padding: 5px 12px;
            font-size: 0.775rem;
            font-weight: 500;
            cursor: pointer;
            color: var(--color-text-secondary);
            font-family: inherit;
            transition: all 0.2s ease;
        }

        .theme-toggle:hover {
            border-color: var(--color-primary);
            color: var(--color-primary);
        }

        .input-field {
            width: 100%;
            padding: 0.75rem 1rem;
            margin: 0.5rem 0;
            border-radius: 10px;
            border: 1px solid var(--color-border);
            background-color: var(--color-bg-primary);
            color: var(--color-text-primary);
            font-size: 0.9rem;
            font-family: inherit;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .input-field::placeholder {
            color: var(--color-text-muted);
        }

        .input-field:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }

        .button {
            width: 100%;
            padding: 0.8rem;
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 600;
            font-family: inherit;
            margin-top: 0.75rem;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
            letter-spacing: 0.01em;
        }

        .button:hover {
            opacity: 0.92;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.45);
        }

        [data-theme="dark"] .button {
            background: linear-gradient(135deg, #818cf8, #6366f1);
            box-shadow: 0 4px 14px rgba(129, 140, 248, 0.3);
        }

        .error-message {
            color: var(--color-danger);
            text-align: center;
            font-size: 0.875rem;
            margin-bottom: 0.75rem;
            padding: 0.6rem 0.9rem;
            background: rgba(220, 38, 38, 0.08);
            border-radius: 8px;
            border: 1px solid rgba(220, 38, 38, 0.2);
        }

        .register-link {
            text-align: center;
            margin-top: 1.25rem;
            color: var(--color-text-secondary);
            font-size: 0.875rem;
        }

        .register-link a {
            text-decoration: none;
            color: var(--color-primary);
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="logo-area">
            <div class="logo-icon">LH</div>
            <span class="logo-text">LearnHub</span>
            <span class="logo-subtitle">Deine Lernplattform</span>
        </div>

        <div class="header-row">
            <h2>Willkommen zurück</h2>
            <button class="theme-toggle" type="button" id="themeToggle">🌙 Dark</button>
        </div>

        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="text" name="username" class="input-field" placeholder="Benutzername" required autocomplete="username">
            <input type="password" name="password" class="input-field" placeholder="Passwort" required autocomplete="current-password">
            <button type="submit" class="button">Anmelden</button>
        </form>

        <div class="register-link">
            <p>Noch keinen Account? <a href="register.php">Jetzt registrieren</a></p>
        </div>
    </div>

    <script>
        (function () {
    const root = document.documentElement;
    const toggleBtn = document.getElementById('themeToggle');

    const currentTheme = root.getAttribute('data-theme') || 'light';
    toggleBtn.textContent = currentTheme === 'dark' ? '☀️ Light' : '🌙 Dark';

    toggleBtn.addEventListener('click', function () {
        const current = root.getAttribute('data-theme') || 'light';
        const next = current === 'light' ? 'dark' : 'light';

        root.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);

        toggleBtn.textContent = next === 'dark' ? '☀️ Light' : '🌙 Dark';
    });
})();
    </script>

</body>
</html>

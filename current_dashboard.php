<?php
/**
 * Dateizweck: Endpoint oder Seite "current_dashboard" im Modul "root".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
session_start();

require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/api_helper.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'user';
$is_admin = strtolower((string)$user_role) === 'admin';
$current_locale = learnhub_get_locale();

?>




<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($current_locale); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(t('site.dashboard_title')); ?></title>
    <script>
(function () {
    const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
})();

</script>

    <style>
        :root {
            --font-family-base: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            --font-family-mono: 'Fira Code', 'Courier New', monospace;

            /* Light Mode Colors */
            --color-bg-primary: #f0f4f8;
            --color-bg-secondary: #ffffff;
            --color-bg-surface: #ffffff;
            --color-bg-hover: #e8edf3;
            --color-text-primary: #0f172a;
            --color-text-secondary: #64748b;
            --color-text-muted: #94a3b8;
            --color-primary: #4f46e5;
            --color-primary-hover: #4338ca;
            --color-primary-active: #3730a3;
            --color-border: #e2e8f0;
            --color-border-light: #f1f5f9;
            --color-success: #059669;
            --color-warning: #d97706;
            --color-danger: #dc2626;
            --color-info: #0891b2;
            --shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.06), 0 1px 2px rgba(15, 23, 42, 0.04);
            --shadow-md: 0 4px 12px rgba(15, 23, 42, 0.08), 0 2px 4px rgba(15, 23, 42, 0.04);
            --shadow-lg: 0 20px 40px rgba(15, 23, 42, 0.12), 0 8px 16px rgba(15, 23, 42, 0.06);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --sidebar-width: 260px;
            --transition-fast: 0.15s ease;
            --transition-base: 0.2s ease;
            --transition-slow: 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        [data-theme="dark"] {
            --color-bg-primary: #0a0f1e;
            --color-bg-secondary: #111827;
            --color-bg-surface: #111827;
            --color-bg-hover: #1e293b;
            --color-text-primary: #f1f5f9;
            --color-text-secondary: #94a3b8;
            --color-text-muted: #64748b;
            --color-primary: #818cf8;
            --color-primary-hover: #6366f1;
            --color-primary-active: #4f46e5;
            --color-border: #1e293b;
            --color-border-light: #263045;
            --color-success: #10b981;
            --color-warning: #f59e0b;
            --color-danger: #f87171;
            --color-info: #22d3ee;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.4);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.5);
            --shadow-lg: 0 20px 40px rgba(0, 0, 0, 0.6);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family-base);
            background-color: var(--color-bg-primary);
            color: var(--color-text-primary);
            line-height: 1.6;
            overflow-x: hidden;
        }

        .app-container {
            display: flex;
            height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background-color: var(--color-bg-secondary);
            border-right: 1px solid var(--color-border);
            display: flex;
            flex-direction: column;
            transition: transform var(--transition-slow);
            box-shadow: var(--shadow-sm);
        }

        .sidebar-header {
            padding: 1.5rem 1.25rem;
            border-bottom: 1px solid var(--color-border);
        }

        .logo {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--color-primary);
            display: flex;
            align-items: center;
            gap: 0.65rem;
            letter-spacing: -0.02em;
        }

        .logo-icon {
            width: 34px;
            height: 34px;
            background: linear-gradient(135deg, var(--color-primary), var(--color-primary-active));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.85rem;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.35);
            letter-spacing: 0;
        }

        .sidebar-nav {
            flex: 1;
            padding: 1rem 0.75rem;
            overflow-y: auto;
        }

        .nav-section {
            margin-bottom: 1.25rem;
        }

        .nav-section-title {
            padding: 0.4rem 0.75rem;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--color-text-muted);
            letter-spacing: 0.08em;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.65rem 0.75rem;
            color: var(--color-text-secondary);
            text-decoration: none;
            transition: all var(--transition-base);
            cursor: pointer;
            border-radius: var(--radius-sm);
            margin-bottom: 2px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .nav-item:hover {
            background-color: var(--color-bg-hover);
            color: var(--color-text-primary);
            transform: translateX(2px);
        }

        .nav-item.active {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.12), rgba(99, 102, 241, 0.07));
            color: var(--color-primary);
            font-weight: 600;
        }

        [data-theme="dark"] .nav-item.active {
            background: linear-gradient(135deg, rgba(129, 140, 248, 0.18), rgba(99, 102, 241, 0.1));
        }

        .nav-icon {
            margin-right: 0.75rem;
            font-size: 1.1rem;
            width: 22px;
            text-align: center;
            flex-shrink: 0;
        }

        .sidebar-footer {
            padding: 0.75rem;
            border-top: 1px solid var(--color-border);
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .theme-toggle, .account-btn, .logout-btn {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 0.6rem 0.85rem;
            background: transparent;
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            color: var(--color-text-secondary);
            cursor: pointer;
            transition: all var(--transition-base);
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            font-family: var(--font-family-base);
        }

        .theme-toggle:hover, .account-btn:hover {
            background-color: var(--color-bg-hover);
            color: var(--color-text-primary);
            border-color: var(--color-primary);
        }

        .logout-btn {
            color: var(--color-danger);
            border-color: rgba(220, 38, 38, 0.3);
            justify-content: center;
        }

        .logout-btn:hover {
            background-color: rgba(220, 38, 38, 0.08);
            border-color: var(--color-danger);
        }

        /* Account Settings Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(10, 15, 30, 0.65);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            animation: overlayFadeIn 0.2s ease;
        }
        .modal-overlay.open {
            display: flex;
        }
        .modal-box {
            background: var(--color-bg-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-xl);
            padding: 2rem;
            width: 100%;
            max-width: 480px;
            box-shadow: var(--shadow-lg);
            max-height: 90vh;
            overflow-y: auto;
            animation: modalSlideUp 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        @keyframes modalSlideUp {
            from { opacity: 0; transform: translateY(20px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .modal-box h2 {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            letter-spacing: -0.02em;
        }
        .modal-section {
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            padding: 1.25rem;
            margin-bottom: 1rem;
            transition: border-color var(--transition-base);
        }
        .modal-section:focus-within {
            border-color: var(--color-primary);
        }
        .modal-section h3 {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--color-text-primary);
        }
        .modal-section input {
            width: 100%;
            padding: 0.65rem 0.9rem;
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            background: var(--color-bg-primary);
            color: var(--color-text-primary);
            font-size: 0.875rem;
            margin-bottom: 0.6rem;
            box-sizing: border-box;
            transition: border-color var(--transition-base), box-shadow var(--transition-base);
            font-family: var(--font-family-base);
        }
        .modal-section input:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }
        .modal-btn {
            padding: 0.55rem 1.2rem;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all var(--transition-base);
            font-family: var(--font-family-base);
        }
        .modal-btn-primary {
            background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover));
            color: white;
            box-shadow: 0 2px 8px rgba(79, 70, 229, 0.3);
        }
        .modal-btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
        .modal-btn-danger {
            background: var(--color-danger);
            color: white;
        }
        .modal-btn-danger:hover { opacity: 0.85; transform: translateY(-1px); }
        .modal-btn-close {
            background: transparent;
            border: 1px solid var(--color-border);
            color: var(--color-text-secondary);
            margin-left: 0.5rem;
        }
        .modal-btn-close:hover { background: var(--color-bg-hover); }
        .modal-msg {
            font-size: 0.82rem;
            margin-top: 0.4rem;
            min-height: 1.1em;
        }
        .modal-msg.success { color: var(--color-success); }
        .modal-msg.error   { color: var(--color-danger); }
        .modal-footer {
            text-align: right;
            margin-top: 1rem;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            overflow-y: auto;
            padding: 2rem;
            background-color: var(--color-bg-primary);
        }

        .content-header {
            margin-bottom: 2rem;
        }

        .content-header h1 {
            font-size: 1.85rem;
            font-weight: 700;
            margin-bottom: 0.4rem;
            letter-spacing: -0.03em;
            color: var(--color-text-primary);
        }

        .content-header p {
            color: var(--color-text-secondary);
            font-size: 0.95rem;
        }

        #overview .content-header-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            flex-wrap: wrap;
        }

        #overview .overview-customize-btn {
            margin-left: auto;
        }

        #overview .overview-customize-btn.active {
            background-color: var(--color-primary);
            color: white;
            border-color: var(--color-primary);
            position: relative;
            z-index: 11;
            box-shadow: 0 0 0 3px rgba(var(--color-primary-rgb, 13, 110, 253), 0.35), var(--shadow-md);
        }

        /* Bento Grid Layout */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .widget {
            background-color: var(--color-bg-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            transition: box-shadow var(--transition-slow), transform var(--transition-slow), border-color var(--transition-base);
        }

        .widget:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-3px);
            border-color: rgba(79, 70, 229, 0.2);
        }

        /* Customize Mode Overlay */
        #overview.customize-mode-active::before {
            content: '';
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            z-index: 10;
            pointer-events: none;
            animation: overlayFadeIn 0.25s ease forwards;
        }

        @keyframes overlayFadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        #overviewWidgetGrid.widget-customize-mode {
            position: relative;
            z-index: 11;
        }

        #overviewWidgetGrid.widget-customize-mode .widget[data-widget-id] {
            cursor: grab;
            z-index: 12;
            position: relative;
            box-shadow: var(--shadow-lg), 0 0 0 2px var(--color-primary);
            transform: translateY(-3px);
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }

        #overviewWidgetGrid.widget-customize-mode .widget[data-widget-id].widget-dragging {
            opacity: 0.55;
            cursor: grabbing;
        }

        #overviewWidgetGrid.widget-customize-mode .widget[data-widget-id].drop-swap-target {
            border: 2px solid #1f6fff;
            box-shadow: 0 0 8px rgba(31, 111, 255, 0.3);
        }

        .widget-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .widget-title {
            font-size: 1rem;
            font-weight: 650;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            letter-spacing: -0.01em;
        }

        .widget-icon {
            font-size: 1.2rem;
        }

        .widget-action {
            background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover));
            border: none;
            border-radius: var(--radius-sm);
            color: white;
            cursor: pointer;
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
            font-weight: 600;
            font-family: var(--font-family-base);
            box-shadow: 0 2px 8px rgba(79, 70, 229, 0.3);
            transition: all var(--transition-base);
            letter-spacing: 0.01em;
        }

        .widget-action:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4);
        }

        /* Stundenplan Widget */
        .timetable {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .timetable-day {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem;
            background-color: var(--color-bg-hover);
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .day-name {
            font-weight: 600;
            min-width: 80px;
            color: var(--color-primary);
        }

        .day-classes {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .class-badge {
            background-color: var(--color-primary);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Noten Widget */
        .grades-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .grade-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background-color: var(--color-bg-hover);
            border-radius: 8px;
        }

        .grade-subject {
            font-weight: 500;
        }

        .grade-value {
            font-size: 1.2rem;
            font-weight: 700;
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            background-color: var(--color-success);
            color: white;
        }

        .grade-value.warning {
            background-color: var(--color-warning);
        }

        .grade-value.danger {
            background-color: var(--color-danger);
        }

        .grade-average-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.65rem;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(12, 145, 255, 0.95), rgba(78, 30, 255, 0.95));
            color: white;
            box-shadow: 0 10px 18px rgba(7, 48, 96, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 190px;
        }

        .grade-average-title {
            font-size: 0.85rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            opacity: 0.95;
        }

        
        .grade-average-circle-inner {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
            display: grid;
            place-items: center;
            color: #ffffff;
            font-size: 1.2rem;
            font-weight: 700;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.18);
        }

        .grade-average-meta {
            font-size: 0.84rem;
            opacity: 0.95;
            letter-spacing: 0.02em;
        }

        .grade-average-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: linear-gradient(135deg, var(--color-primary-variant, #2a9fd6), var(--color-primary-dark, #1f6fb2));
            color: white;
            border-radius: 12px;
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.2);
            text-align: center;
            margin-bottom: 0.75rem;
        }

        .grade-average-circle {
            position: relative;
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: conic-gradient(var(--fill-color, #ffd700) var(--pct, 0%), rgba(255,255,255,0.15) 0%);
            display: grid;
            place-items: center;
        }

        .grade-average-circle::after {
            content: '';
            position: absolute;
            width: 74px;
            height: 74px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            z-index: 0;
        }

        .grade-average-circle > span {
            position: relative;
            z-index: 1;
            font-size: 1.15rem;
            font-weight: 700;
        }

        .grade-average-bar {
            width: 100%;
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
            overflow: hidden;
            height: 12px;
        }

        .grade-average-bar-fill {
            height: 100%;
            width: 0;
            border-radius: 8px;
            background: linear-gradient(90deg, #f4eb38, #f3b01f);
            transition: width 1.2s ease;
        }

        .grade-average-meta {
            font-size: 0.85rem;
            opacity: 0.9;
        }

        /* To-Do Widget */
        .todo-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            max-height: 300px;
            overflow-y: auto;
        }

        .todo-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background-color: var(--color-bg-hover);
            border-radius: 8px;
            transition: all 0.2s;
        }

        .todo-item:hover {
            background-color: var(--color-border);
        }

        .todo-checkbox {
            width: 20px;
            height: 20px;
            border: 2px solid var(--color-border);
            border-radius: 4px;
            cursor: pointer;
            flex-shrink: 0;
            transition: all 0.2s;
        }

        .todo-checkbox.checked {
            background-color: var(--color-success);
            border-color: var(--color-success);
        }

        .todo-text {
            flex: 1;
            font-size: 0.9rem;
        }

        .todo-text.completed {
            text-decoration: line-through;
            color: var(--color-text-muted);
        }

        .todo-priority {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .priority-high { background-color: var(--color-danger); }
        .priority-medium { background-color: var(--color-warning); }
        .priority-low { background-color: var(--color-info); }

        /* Todo Filter Buttons */
        .todo-filter-btn {
            padding: 0.35rem 0.85rem;
            background: transparent;
            border: 1px solid var(--color-border);
            border-radius: 6px;
            color: var(--color-text-secondary);
            cursor: pointer;
            font-size: 0.82rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        .todo-filter-btn:hover {
            background-color: var(--color-bg-hover);
            color: var(--color-text-primary);
        }
        .todo-filter-btn.active {
            background-color: var(--color-primary);
            color: white;
            border-color: var(--color-primary);
        }

        /* Todo Item Body */
        .todo-body {
            flex: 1;
            min-width: 0;
        }
        .todo-meta {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.2rem;
            flex-wrap: wrap;
            align-items: center;
        }
        .todo-tag {
            font-size: 0.72rem;
            padding: 0.1rem 0.45rem;
            background-color: rgba(13, 110, 253, 0.15);
            color: var(--color-primary);
            border-radius: 4px;
            font-weight: 500;
        }
        .todo-due {
            font-size: 0.72rem;
            color: var(--color-text-muted);
        }
        .todo-item.todo-done {
            opacity: 0.55;
        }

        /* Karteikarten Widget */
        .flashcard {
            perspective: 1000px;
            height: 200px;
            cursor: pointer;
        }

        .flashcard-inner {
            position: relative;
            width: 100%;
            height: 100%;
            transition: transform 0.6s;
            transform-style: preserve-3d;
        }

        .flashcard.flipped .flashcard-inner {
            transform: rotateY(180deg);
        }

        .flashcard-front, .flashcard-back {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background-color: var(--color-bg-hover);
            border-radius: 8px;
            text-align: center;
            font-size: 1.1rem;
        }

        .flashcard-back {
            transform: rotateY(180deg);
            background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover));
            color: white;
        }

        .flashcard-nav {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
        }

        .flashcard-btn {
            padding: 0.5rem 1rem;
            background-color: var(--color-primary);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .flashcard-btn:hover {
            background-color: var(--color-primary-hover);
        }

        /* Dateien Widget */
        .files-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            overflow: hidden;
        }

        .file-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background-color: var(--color-bg-hover);
            border-radius: 8px;
            transition: all 0.2s;
            cursor: pointer;
            min-width: 0;
        }

        .file-item:hover {
            background-color: var(--color-border);
        }

        .file-icon {
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .file-info {
            flex: 1;
            min-width: 0;
        }

        .file-name {
            font-weight: 500;
            font-size: 0.9rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .file-meta {
            font-size: 0.75rem;
            color: var(--color-text-muted);
        }

        /* Admin- Nachrichten Widget */
        .messages-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            max-height: 300px;
            overflow-y: auto;
        }

        .message-item {
            padding: 0.75rem;
            background-color: var(--color-bg-hover);
            border-radius: 8px;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .message-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .message-meta {
            font-size: 0.75rem;
            color: var(--color-text-muted);
            margin-bottom: 0.5rem;
        }

        .message-text {
            font-size: 0.9rem;
            white-space: pre-wrap;
        }

        .message-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            background-color: rgba(13, 110, 253, 0.12);
            color: var(--color-primary);
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .message-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .admin-user-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
        }

        .admin-user-main {
            min-width: 0;
            flex: 1 1 auto;
        }

        .admin-user-hint {
            margin-top: 0.45rem;
            font-size: 0.8rem;
            color: var(--color-text-muted);
        }

        .admin-user-title,
        .admin-user-meta {
            overflow-wrap: break-word;
            word-break: normal;
            hyphens: auto;
        }

        .admin-user-main > div {
            max-width: 100%;
        }

        .admin-user-actions {
            display: flex;
            align-items: flex-end;
            flex-direction: column;
            gap: 0.5rem;
            justify-content: flex-start;
            flex: 0 0 auto;
            margin-left: 1rem;
            text-align: right;
        }

        .admin-sent-messages-widget,
        .admin-users-widget {
            /* Nebeneinander auf gleicher Höhe */
        }

        @media (max-width: 900px) {
            .admin-user-row {
                flex-direction: column;
            }

            .admin-user-actions {
                align-items: flex-start;
                margin-left: 0;
                text-align: left;
            }
        }

        .message-empty {
            color: var(--color-text-muted);
            text-align: center;
            padding: 0.75rem;
        }

        /* Admin Panel Widget */
        .admin-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .stat-card {
            padding: 1rem;
            background-color: var(--color-bg-hover);
            border-radius: 8px;
            text-align: center;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--color-primary);
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.85rem;
            color: var(--color-text-secondary);
        }

        /* Input Styles */
        .input-group {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        input[type="text"], input[type="number"], select, textarea {
            flex: 1;
            padding: 0.7rem 0.9rem;
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            background-color: var(--color-bg-primary);
            color: var(--color-text-primary);
            font-size: 0.875rem;
            font-family: var(--font-family-base);
            transition: border-color var(--transition-base), box-shadow var(--transition-base);
        }

        .input-group input[type="file"] {
            flex: 1.2;
            min-height: 42px;
            padding: 0.35rem;
            border: 1px dashed color-mix(in srgb, var(--color-primary) 40%, var(--color-border));
            border-radius: var(--radius-sm);
            background: linear-gradient(180deg, color-mix(in srgb, var(--color-primary) 4%, var(--color-bg-primary)), var(--color-bg-primary));
            color: var(--color-text-secondary);
            cursor: pointer;
        }

        .input-group input[type="file"]:hover {
            border-color: color-mix(in srgb, var(--color-primary) 60%, var(--color-border));
            background: linear-gradient(180deg, color-mix(in srgb, var(--color-primary) 8%, var(--color-bg-primary)), var(--color-bg-primary));
        }

        .input-group input[type="file"]::file-selector-button {
            padding: 0.5rem 0.9rem;
            margin-right: 0.7rem;
            border: 1px solid transparent;
            border-radius: calc(var(--radius-sm) - 2px);
            background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover));
            color: #ffffff;
            font-family: var(--font-family-base);
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform var(--transition-base), box-shadow var(--transition-base), opacity var(--transition-base);
            box-shadow: 0 2px 8px rgba(79, 70, 229, 0.2);
        }

        .input-group input[type="file"]::file-selector-button:hover {
            transform: translateY(-1px);
            opacity: 0.94;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        textarea {
            resize: vertical;
            min-height: 120px;
            font-family: inherit;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
        }

        .form-status {
            margin: 0;
            min-height: 1.2rem;
            font-size: 0.85rem;
        }

        .btn-primary {
            padding: 0.7rem 1.5rem;
            background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover));
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 600;
            font-size: 0.875rem;
            font-family: var(--font-family-base);
            transition: all var(--transition-base);
            box-shadow: 0 2px 8px rgba(79, 70, 229, 0.25);
        }

        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
        }

        .btn-upload-action {
            letter-spacing: 0.01em;
            white-space: nowrap;
            box-shadow: 0 3px 10px rgba(79, 70, 229, 0.28);
        }

        .btn-icon {
            padding: 0.45rem 0.6rem;
            background: transparent;
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all var(--transition-base);
            font-family: var(--font-family-base);
        }

        .btn-icon:hover {
            background-color: var(--color-bg-hover);
            border-color: var(--color-primary);
            transform: scale(1.05);
        }

        /* Responsive */
        .mobile-menu-btn {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1001;
            width: 44px;
            height: 44px;
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            background: var(--color-bg-surface);
            color: var(--color-text-primary);
            font-size: 1.4rem;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-md);
            transition: background var(--transition-base);
        }
        .mobile-menu-btn:hover {
            background: var(--color-bg-hover);
        }
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 999;
            animation: overlayFadeIn 0.2s ease;
        }
        .sidebar-overlay.open { display: block; }

        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: flex;
            }

            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                z-index: 1000;
                height: 100vh;
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                padding: 1rem;
                padding-top: 4rem;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .content-header h1 {
                font-size: 1.4rem;
            }

            .input-group {
                flex-direction: column;
            }

            .modal-box {
                margin: 0.75rem;
                max-width: calc(100vw - 1.5rem);
                max-height: calc(100vh - 1.5rem);
            }

            #overview .overview-customize-btn {
                width: 100%;
            }

            .widget {
                padding: 1rem;
            }

            .btn-icon {
                min-width: 44px;
                min-height: 44px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 0.75rem;
                padding-top: 3.5rem;
            }

            .content-header h1 {
                font-size: 1.2rem;
            }

            .widget-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .grade-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }

        /* ===== Stundenplan ===== */
        .tt-grid {
            display: grid;
            grid-template-columns: 80px repeat(5, 1fr);
            gap: 4px;
            overflow-x: auto;
            min-width: 500px;
        }

        .tt-header-cell {
            padding: 0.5rem;
            text-align: center;
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--color-text-secondary);
            border-radius: 6px;
            min-height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .tt-header-cell.today-header {
            background-color: var(--color-primary);
            color: white;
            border-radius: 8px;
        }

        .tt-time-cell {
            padding: 0.4rem 0.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: var(--color-bg-hover);
            border-radius: 6px;
            font-size: 0.75rem;
            color: var(--color-text-secondary);
            min-height: 58px;
        }

        .tt-period-num {
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--color-text-primary);
        }

        .tt-time-label {
            font-size: 0.68rem;
            color: var(--color-text-muted);
            white-space: nowrap;
        }

        .tt-subject-cell {
            padding: 0.45rem 0.4rem;
            border-radius: 6px;
            background-color: var(--color-bg-hover);
            min-height: 58px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            transition: background 0.2s;
            gap: 2px;
            border: 1px solid transparent;
        }

        .tt-subject-cell.today-col {
            background-color: rgba(13, 110, 253, 0.08);
            border-color: rgba(13, 110, 253, 0.25);
        }

        [data-theme="dark"] .tt-subject-cell.today-col {
            background-color: rgba(56, 189, 248, 0.1);
            border-color: rgba(56, 189, 248, 0.3);
        }

        .tt-subject-cell.has-exam {
            background-color: rgba(220, 53, 69, 0.12);
            border-color: var(--color-danger);
            border-width: 1px;
            box-shadow: inset 0 0 0 1px rgba(220, 53, 69, 0.12);
        }

        .tt-subject-cell.has-exam.today-col {
            background-color: rgba(220, 53, 69, 0.16);
            border-color: var(--color-danger);
            border-width: 1px;
        }

        .tt-exam-badge {
            font-size: 0.62rem;
            background-color: var(--color-danger);
            color: white;
            padding: 2px 5px;
            border-radius: 4px;
            line-height: 1.3;
        }
        .tt-subject-cell.has-homework {
            background-color: rgba(25, 135, 84, 0.1); /* success tint */
            border-color: rgba(25, 135, 84, 0.3);
        }
        .tt-homework-badge {
            font-size: 0.62rem;
            background-color: var(--color-success);
            color: white;
            padding: 2px 5px;
            border-radius: 4px;
            line-height: 1.3;
        }

        .tt-subject-name {
            font-weight: 600;
            font-size: 0.82rem;
            color: var(--color-text-primary);
        }

        .tt-room {
            font-size: 0.7rem;
            color: var(--color-text-muted);
        }

        /* Hausaufgaben */
        .tt-homework-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 0.75rem;
            overflow-x: auto;
            min-width: 480px;
        }

        .tt-hw-day {
            background-color: var(--color-bg-hover);
            border-radius: 8px;
            padding: 0.75rem;
            border: 1.5px solid transparent;
        }

        .tt-hw-day.today {
            border-color: var(--color-primary);
        }

        .tt-hw-day-title {
            font-weight: 600;
            font-size: 0.82rem;
            color: var(--color-primary);
            margin-bottom: 0.5rem;
        }

        .tt-hw-item {
            display: flex;
            align-items: flex-start;
            gap: 0.35rem;
            padding: 0.28rem 0;
            font-size: 0.8rem;
            border-bottom: 1px solid var(--color-border-light);
        }

        .tt-hw-item:last-of-type {
            border-bottom: none;
        }

        .tt-hw-delete {
            background: none;
            border: none;
            color: var(--color-text-muted);
            cursor: pointer;
            padding: 0;
            font-size: 0.72rem;
            flex-shrink: 0;
            margin-top: 1px;
            line-height: 1;
        }

        .tt-hw-delete:hover { color: var(--color-danger); }

        .tt-hw-input-row {
            display: flex;
            gap: 0.35rem;
            margin-top: 0.5rem;
        }

        .tt-hw-input {
            flex: 1;
            padding: 0.3rem 0.5rem;
            font-size: 0.78rem;
            border: 1px solid var(--color-border);
            border-radius: 5px;
            background: var(--color-bg-primary);
            color: var(--color-text-primary);
            min-width: 0;
        }

        .tt-hw-input:focus { outline: none; border-color: var(--color-primary); }

        .tt-hw-add-btn {
            padding: 0.3rem 0.6rem;
            font-size: 0.85rem;
            font-weight: 600;
            background: var(--color-primary);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .tt-hw-add-btn:hover { background: var(--color-primary-hover); }

        /* Bearbeitungsmodus */
        .tt-edit-section-title {
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--color-text-primary);
        }

        .tt-times-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));
            gap: 0.65rem;
        }

        .tt-time-item {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }

        .tt-time-item label {
            font-size: 0.78rem;
            font-weight: 500;
            color: var(--color-text-secondary);
        }

        .tt-time-item input[type="time"],
        input[type="date"] {
            padding: 0.6rem 0.7rem;
            border: 1px solid var(--color-border);
            border-radius: 6px;
            background: var(--color-bg-primary);
            color: var(--color-text-primary);
            font-size: 0.85rem;
            width: 100%;
        }

        input[type="date"] {
            min-height: 42px;
            padding-right: 0.5rem;
            border-color: color-mix(in srgb, var(--color-primary) 22%, var(--color-border));
            background: linear-gradient(180deg, color-mix(in srgb, var(--color-primary) 5%, var(--color-bg-primary)), var(--color-bg-primary));
            cursor: pointer;
        }

        input[type="date"]:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.14);
        }

        input[type="date"]::-webkit-calendar-picker-indicator {
            padding: 0.2rem;
            border-radius: 6px;
            background-color: color-mix(in srgb, var(--color-primary) 14%, transparent);
            cursor: pointer;
            transition: background-color var(--transition-base), transform var(--transition-base);
        }

        input[type="date"]::-webkit-calendar-picker-indicator:hover {
            background-color: color-mix(in srgb, var(--color-primary) 24%, transparent);
            transform: scale(1.05);
        }

        .tt-time-item input[type="time"]:focus { outline: none; border-color: var(--color-primary); }

        .tt-editor-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 4px;
            min-width: 600px;
        }

        .tt-editor-table th {
            padding: 0.5rem;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--color-text-secondary);
            text-align: center;
            background: var(--color-bg-hover);
            border-radius: 6px;
        }

        .tt-editor-table td { vertical-align: top; }

        .tt-editor-cell {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .tt-editor-cell input {
            padding: 0.35rem 0.45rem;
            border: 1px solid var(--color-border);
            border-radius: 5px;
            background: var(--color-bg-primary);
            color: var(--color-text-primary);
            font-size: 0.78rem;
            width: 100%;
        }

        .tt-editor-cell input:focus { outline: none; border-color: var(--color-primary); }

        .tt-editor-cell input::placeholder {
            color: var(--color-text-muted);
            font-size: 0.73rem;
        }

        .tt-period-label {
            text-align: center;
            padding: 0.3rem;
            font-weight: 600;
            font-size: 0.82rem;
            color: var(--color-text-secondary);
        }

        /* Secondary Button */
        .btn-secondary {
            padding: 0.7rem 1.5rem;
            background-color: transparent;
            color: var(--color-text-secondary);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 500;
            font-size: 0.875rem;
            font-family: var(--font-family-base);
            transition: all var(--transition-base);
        }

        .btn-secondary:hover {
            background-color: var(--color-bg-hover);
            color: var(--color-text-primary);
            border-color: var(--color-primary);
        }

        /* Vergangene Klausur */
        .exam-past { opacity: 0.55; }

        /* Kalender Styling */
        #calendarLayout {
            display: grid;
            grid-template-columns: minmax(360px, 620px) minmax(240px, 1fr);
            gap: 1rem;
            align-items: start;
        }

        #calendarContainer {
            max-width: 620px;
        }

        #calendarControls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        #calendarSelectedHint {
            font-size: 0.85rem;
            color: var(--color-text-muted);
            margin-bottom: 0.45rem;
        }

        #calendarControls .btn-secondary {
            padding: 0.45rem 0.9rem;
        }

        #calendarGrid th, #calendarGrid td {
            border: 1px solid var(--color-border);
            width: 14.28%;
            height: 56px;
            vertical-align: top;
            padding: 0.2rem;
            position: relative;
        }

        #calendarGrid th {
            font-size: 0.8rem;
            color: var(--color-text-muted);
        }

        #calendarGrid td {
            cursor: pointer;
        }

        #calendarGrid td:hover {
            background-color: var(--color-bg-hover);
        }

        #calendarGrid td.cal-today {
            background-color: #2166f0;
            border-color: #4852e4;
        }

        #calendarGrid td.cal-today .cal-day-number {
            color: #f2f2f2;
        }

        #calendarGrid td.cal-selected {
            background-color: #cacaca;
            border-color: #cccccc;
        }

        #calendarGrid td.cal-selected .cal-day-number {
            color: #1f2937;
        }

        #calendarGrid td.cal-today.cal-selected {
            background-color: #0049da;
            border-color: #2848af;
        }

        #calendarGrid td.cal-today.cal-selected .cal-day-number {
            color: #f2f2f2;
        }

        .cal-day-number {
            font-weight: 600;
            margin-bottom: 0.1rem;
            font-size: 0.85rem;
        }

        .event-dots {
            position: absolute;
            right: 4px;
            bottom: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
            max-width: calc(100% - 8px);
        }

        .event-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: var(--event-dot-color, var(--color-primary));
            flex: 0 0 auto;
        }

        #calendarGrid td.cal-today .event-dot {
            border: 1px solid rgba(255, 255, 255, 0.9);
        }

        #calendarGrid td.cal-selected .event-dot {
            border: 1px solid rgba(31, 41, 55, 0.25);
        }

        #calendarDayEvents {
            min-height: 100%;
            border: 1px solid var(--color-border);
            border-radius: 12px;
            background: var(--color-bg-secondary);
        }

        #calendarDayLabel {
            margin-bottom: 0.6rem;
        }

        #calendarEventList .calendar-event-item {
            padding: 0.5rem;
            border-bottom: 1px solid var(--color-border-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        #calendarEventList .calendar-event-content {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            min-width: 0;
        }

        #calendarEventList .calendar-event-text {
            min-width: 0;
            overflow-wrap: anywhere;
        }

        #calendarEventList .calendar-event-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: var(--event-dot-color, var(--color-primary));
            flex: 0 0 auto;
        }

        .calendar-title-input-row {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            margin-bottom: 0.65rem;
        }

        .calendar-title-input-row input {
            margin-bottom: 0;
        }

        .calendar-title-input-row input[type="text"] {
            flex: 1;
            min-width: 0;
        }

        .calendar-title-input-row input[type="color"] {
            width: 42px;
            min-width: 42px;
            height: 36px;
            padding: 2px;
            border: 1px solid var(--color-border);
            border-radius: 8px;
            background: var(--color-bg-primary);
            cursor: pointer;
        }

        .calendar-title-preview-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: var(--event-dot-color, var(--color-primary));
            border: 2px solid color-mix(in srgb, var(--color-bg-secondary) 55%, transparent);
            box-shadow: 0 0 0 1px color-mix(in srgb, var(--color-border) 75%, transparent);
            flex: 0 0 auto;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .calendar-title-preview-dot:hover {
            transform: scale(1.25);
            box-shadow: 0 0 0 2px color-mix(in srgb, var(--color-border) 75%, transparent), 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        /* Keep native color picker available via dot click, but hide the input UI. */
        #quickEventColor {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            border: 0;
            clip: rect(0, 0, 0, 0);
            clip-path: inset(100%);
            overflow: hidden;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
        }

        .calendar-title-suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
            min-height: 0;
        }

        .calendar-title-suggestions:empty {
            display: none;
        }

        .calendar-title-suggestion {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.4rem 0.7rem;
            border-radius: 999px;
            border: 1px solid var(--color-border);
            background: var(--color-bg-hover);
            color: var(--color-text-primary);
            cursor: pointer;
            transition: border-color 0.2s ease, transform 0.2s ease, background-color 0.2s ease;
        }

        .calendar-title-suggestion:hover,
        .calendar-title-suggestion.active {
            border-color: var(--event-dot-color, var(--color-primary));
            background: color-mix(in srgb, var(--event-dot-color, var(--color-primary)) 12%, var(--color-bg-secondary));
            transform: translateY(-1px);
        }

        .calendar-title-suggestion-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background-color: var(--event-dot-color, var(--color-primary));
            flex: 0 0 auto;
        }

        .calendar-repeat-label {
            display: block;
            margin-bottom: 0.35rem;
            color: var(--color-text-secondary);
            font-size: 0.92rem;
        }

        .calendar-repeat-select {
            width: 100%;
            padding: 0.6rem 0.85rem;
            border: 1px solid var(--color-border);
            border-radius: 8px;
            background: var(--color-bg-primary);
            color: var(--color-text-primary);
            font-size: 0.9rem;
            margin-bottom: 0.2rem;
        }

        .calendar-repeat-select:focus {
            outline: none;
            border-color: var(--color-primary);
        }

        .calendar-time-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.65rem;
            margin-bottom: 0.65rem;
        }

        .calendar-time-field input[type="time"] {
            width: 100%;
            padding: 0.5rem 0.65rem;
            border: 1px solid var(--color-border);
            border-radius: 8px;
            background: var(--color-bg-primary);
            color: var(--color-text-primary);
            font-size: 0.9rem;
        }

        .calendar-time-field input[type="time"]:focus {
            outline: none;
            border-color: var(--color-primary);
        }

        #calendarDeleteModal .modal-footer {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 0.6rem;
        }

        #calendarDeleteModal .modal-btn-close {
            margin-left: 0;
        }

        @media (max-width: 1024px) {
            #calendarLayout {
                grid-template-columns: 1fr;
            }

            #calendarContainer {
                max-width: 100%;
            }
        }

        #calendarEventList .calendar-event-item.exam { background-color: var(--color-info)33; }
        #calendarEventList .calendar-event-item.todo { background-color: var(--color-success)33; }
        #calendarEventList .calendar-event-item.extra { background-color: var(--color-primary)33; }
        #calendarEventList .calendar-event-item.holiday { background-color: rgba(185, 28, 28, 0.12); }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--color-border);
            border-radius: 99px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--color-text-muted);
        }

        /* Tab Content Fade-In */
        .tab-view {
            animation: tabFadeIn 0.25s ease;
        }

        @keyframes tabFadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Todo/Grade/File items */
        .todo-item, .grade-item, .file-item, .message-item {
            transition: background-color var(--transition-base), transform var(--transition-base);
        }

        .todo-item:hover, .file-item:hover {
            transform: translateX(2px);
        }

        /* ===== Toast Notification System ===== */
        .toast-container {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 9999;
            display: flex;
            flex-direction: column-reverse;
            gap: 0.5rem;
            pointer-events: none;
        }
        .toast {
            pointer-events: auto;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            min-width: 280px;
            max-width: 420px;
            padding: 0.85rem 1.1rem;
            border-radius: var(--radius-md);
            background: var(--color-bg-surface);
            border: 1px solid var(--color-border);
            box-shadow: var(--shadow-lg);
            font-size: 0.875rem;
            color: var(--color-text-primary);
            animation: toastSlideIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            transition: opacity 0.25s ease, transform 0.25s ease;
        }
        .toast.toast-removing {
            opacity: 0;
            transform: translateX(30px);
        }
        .toast-icon {
            font-size: 1.15rem;
            flex-shrink: 0;
        }
        .toast-message {
            flex: 1;
            line-height: 1.4;
        }
        .toast-close {
            background: none;
            border: none;
            color: var(--color-text-muted);
            cursor: pointer;
            font-size: 1rem;
            padding: 0.2rem;
            border-radius: 4px;
            transition: color 0.15s, background 0.15s;
            flex-shrink: 0;
        }
        .toast-close:hover {
            color: var(--color-text-primary);
            background: var(--color-bg-hover);
        }
        .toast-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            border-radius: 0 0 var(--radius-md) var(--radius-md);
            animation: toastProgress var(--toast-duration, 4s) linear forwards;
        }
        .toast.toast-success { border-left: 4px solid var(--color-success); }
        .toast.toast-success .toast-progress { background: var(--color-success); }
        .toast.toast-error   { border-left: 4px solid var(--color-danger); }
        .toast.toast-error .toast-progress   { background: var(--color-danger); }
        .toast.toast-warning { border-left: 4px solid var(--color-warning); }
        .toast.toast-warning .toast-progress { background: var(--color-warning); }
        .toast.toast-info    { border-left: 4px solid var(--color-info); }
        .toast.toast-info .toast-progress    { background: var(--color-info); }

        @keyframes toastSlideIn {
            from { opacity: 0; transform: translateX(40px) scale(0.95); }
            to   { opacity: 1; transform: translateX(0) scale(1); }
        }
        @keyframes toastProgress {
            from { width: 100%; }
            to   { width: 0%; }
        }

        /* ===== Empty State ===== */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1.5rem;
            text-align: center;
            color: var(--color-text-muted);
        }
        .empty-state-icon {
            font-size: 2.5rem;
            margin-bottom: 0.75rem;
            opacity: 0.7;
        }
        .empty-state-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--color-text-secondary);
            margin-bottom: 0.35rem;
        }
        .empty-state-text {
            font-size: 0.85rem;
            max-width: 280px;
            line-height: 1.5;
        }

        /* ===== Loading Spinner ===== */
        .loading-spinner {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            gap: 0.75rem;
            color: var(--color-text-muted);
        }
        .spinner {
            width: 32px;
            height: 32px;
            border: 3px solid var(--color-border);
            border-top-color: var(--color-primary);
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }
        .loading-spinner span {
            font-size: 0.85rem;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .toast-container {
                left: 1rem;
                right: 1rem;
                bottom: 1rem;
            }
            .toast {
                min-width: unset;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>
    <div class="app-container">
        <!-- Mobile Menu Button -->
        <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Menü öffnen">☰</button>
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <div class="logo-icon">LH</div>
                    <span>LearnHub</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Dashboard</div>
                    <a class="nav-item active" data-view="overview">
                        <span class="nav-icon">📊</span>
                        <span><?php echo htmlspecialchars(t('nav.overview')); ?></span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title"><?php echo htmlspecialchars(t('nav.modules')); ?></div>
                    <a class="nav-item" data-view="timetable">
                        <span class="nav-icon">🕒</span>
                        <span><?php echo htmlspecialchars(t('nav.timetable')); ?></span>
                    </a>
                    <a class="nav-item" data-view="homework">
                        <span class="nav-icon">✏️</span>
                        <span><?php echo htmlspecialchars(t('nav.homework')); ?></span>
                    </a>
                    <a class="nav-item" data-view="subjects">
                        <span class="nav-icon">📚</span>
                        <span><?php echo htmlspecialchars(t('nav.subjects')); ?></span>
                    </a>
                    <a class="nav-item" data-view="grades">
                        <span class="nav-icon">📈</span>
                        <span><?php echo htmlspecialchars(t('nav.grades')); ?></span>
                    </a>
                    <a class="nav-item" data-view="exams">
                        <span class="nav-icon">📝</span>
                        <span><?php echo htmlspecialchars(t('nav.exams')); ?></span>
                    </a>
                    <a class="nav-item" data-view="calendar">
                        <span class="nav-icon">📆</span>
                        <span><?php echo htmlspecialchars(t('nav.calendar')); ?></span>
                    </a>
                    <a class="nav-item" data-view="todos">
                        <span class="nav-icon">✅</span>
                        <span><?php echo htmlspecialchars(t('nav.todos')); ?></span>
                    </a>
                    <a class="nav-item" data-view="flashcards">
                        <span class="nav-icon">🎴</span>
                        <span><?php echo htmlspecialchars(t('nav.flashcards')); ?></span>
                    </a>
                    <a class="nav-item" data-view="files">
                        <span class="nav-icon">📁</span>
                        <span><?php echo htmlspecialchars(t('nav.files')); ?></span>
                    </a>
                    <a class="nav-item" data-view="admin-messages">
                        <span class="nav-icon">💬</span>
                        <span><?php echo htmlspecialchars(t('nav.admin_messages')); ?></span>
                    </a>
                    <?php if ($is_admin): ?>
                    <a class="nav-item" data-view="admin">
                        <span class="nav-icon">⚙️</span>
                        <span><?php echo htmlspecialchars(t('nav.admin_panel')); ?></span>
                    </a>
                    <?php endif; ?>
                </div>
            </nav>

            <div class="sidebar-footer">
                <button class="theme-toggle" id="themeToggle">
                    <span id="themeIcon">🌙</span>
                    <span id="themeText" style="margin-left: 0.5rem;"><?php echo htmlspecialchars(t('sidebar.dark_mode')); ?></span>
                </button>
                <button class="account-btn" onclick="openAccountModal()">
                    <span>👤</span>
                    <span style="margin-left: 0.5rem;"><?php echo htmlspecialchars(t('sidebar.account')); ?></span>
                </button>
                <a class="logout-btn" href="auth/logout.php">
                    <span>🚪</span>
                    <span style="margin-left: 0.5rem;"><?php echo htmlspecialchars(t('sidebar.logout')); ?></span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div id="contentArea">
                <?php include __DIR__ . '/tabs/overview.php'; ?>
                <?php include __DIR__ . '/tabs/subjects.php'; ?>
                <?php include __DIR__ . '/tabs/timetable.php'; ?>
                <?php include __DIR__ . '/tabs/grades.php'; ?>
                <?php include __DIR__ . '/tabs/todos.php'; ?>
                <?php include __DIR__ . '/tabs/exams.php'; ?>
                <?php include __DIR__ . '/tabs/homework.php'; ?>
                <?php include __DIR__ . '/tabs/calendar.php'; ?>
                <?php include __DIR__ . '/tabs/flashcards.php'; ?>
                <?php include __DIR__ . '/tabs/files.php'; ?>
                <?php include __DIR__ . '/tabs/admin-messages.php'; ?>
                <?php if ($is_admin): ?>
                <?php include __DIR__ . '/tabs/admin.php'; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- ===== ACCOUNT SETTINGS MODAL ===== -->
    <div class="modal-overlay" id="accountModal">
        <div class="modal-box">
            <h2>&#128100; <?php echo htmlspecialchars(t('account.title')); ?></h2>

            <div class="modal-section">
                <h3>&#127760; <?php echo htmlspecialchars(t('account.language_title')); ?></h3>
                <p style="font-size:0.9rem;color:var(--color-text-secondary);margin-bottom:0.75rem;">
                    <?php echo htmlspecialchars(t('account.language_description')); ?>
                </p>
                <form action="auth/set_language.php" method="POST">
                    <input type="hidden" name="redirect" value="../current_dashboard.php">
                    <label for="localeSelect" style="display:block;font-size:0.9rem;margin-bottom:0.4rem;"><?php echo htmlspecialchars(t('account.language_label')); ?></label>
                    <select id="localeSelect" name="locale" style="width:100%;margin-bottom:0.75rem;">
                        <option value="de" <?php echo $current_locale === 'de' ? 'selected' : ''; ?>><?php echo htmlspecialchars(t('language.de')); ?></option>
                        <option value="en" <?php echo $current_locale === 'en' ? 'selected' : ''; ?>><?php echo htmlspecialchars(t('language.en')); ?></option>
                    </select>
                    <div>
                        <button class="modal-btn modal-btn-primary" type="submit"><?php echo htmlspecialchars(t('account.language_button')); ?></button>
                    </div>
                </form>
            </div>

            <!-- Benutzername ändern -->
            <div class="modal-section">
                <h3>&#9999;&#65039; <?php echo htmlspecialchars(t('account.username_title')); ?></h3>
                <input type="text" id="newUsername" placeholder="<?php echo htmlspecialchars(t('account.username_placeholder')); ?>">
                <div>
                    <button class="modal-btn modal-btn-primary" onclick="changeUsername()"><?php echo htmlspecialchars(t('common.save')); ?></button>
                </div>
                <div class="modal-msg" id="msgUsername"></div>
            </div>

            <!-- Passwort ändern -->
            <div class="modal-section">
                <h3>&#128274; <?php echo htmlspecialchars(t('account.password_title')); ?></h3>
                <input type="password" id="oldPassword" placeholder="<?php echo htmlspecialchars(t('account.password_old')); ?>">
                <input type="password" id="newPassword" placeholder="<?php echo htmlspecialchars(t('account.password_new')); ?>">
                <input type="password" id="newPassword2" placeholder="<?php echo htmlspecialchars(t('account.password_repeat')); ?>">
                <div>
                    <button class="modal-btn modal-btn-primary" onclick="changePassword()"><?php echo htmlspecialchars(t('common.save')); ?></button>
                </div>
                <div class="modal-msg" id="msgPassword"></div>
            </div>

            <!-- E-Mail ändern -->
            <div class="modal-section">
                <h3>&#128231; <?php echo htmlspecialchars(t('account.email_title')); ?></h3>
                <input type="email" id="newEmail" placeholder="<?php echo htmlspecialchars(t('account.email_new')); ?>">
                <input type="text" id="emailVerificationCode" placeholder="<?php echo htmlspecialchars(t('account.email_code')); ?>">
                <div>
                    <button class="modal-btn modal-btn-primary" onclick="requestEmailChangeCode()"><?php echo htmlspecialchars(t('account.send_code')); ?></button>
                    <button class="modal-btn modal-btn-primary" onclick="confirmEmailChange()"><?php echo htmlspecialchars(t('account.change_email')); ?></button>
                </div>
                <div class="modal-msg" id="msgEmail"></div>
            </div>

            <!-- Account löschen -->
            <div class="modal-section" style="border-color: var(--color-danger);">
                <h3 style="color: var(--color-danger);">&#128465;&#65039; <?php echo htmlspecialchars(t('account.delete_title')); ?></h3>
                <p style="font-size:0.85rem;color:var(--color-text-secondary);margin-bottom:0.75rem;">
                    <?php echo htmlspecialchars(t('account.delete_warning')); ?>
                </p>
                <input type="password" id="deletePassword" placeholder="<?php echo htmlspecialchars(t('account.delete_password')); ?>">
                <input type="text" id="deleteVerificationCode" placeholder="<?php echo htmlspecialchars(t('account.email_code')); ?>">
                <div>
                    <button class="modal-btn modal-btn-danger" onclick="requestDeleteAccountCode()"><?php echo htmlspecialchars(t('account.send_code')); ?></button>
                    <button class="modal-btn modal-btn-danger" onclick="confirmDeleteAccount()"><?php echo htmlspecialchars(t('account.delete_button')); ?></button>
                </div>
                <div class="modal-msg" id="msgDelete"></div>
            </div>

            <div class="modal-footer">
                <button class="modal-btn modal-btn-close" onclick="closeAccountModal()"><?php echo htmlspecialchars(t('common.close')); ?></button>
            </div>
        </div>
    </div>

    <!-- Exam Grade Modal -->
    <div class="modal-overlay" id="examGradeModal">
        <div class="modal-box">
            <h2 id="examGradeTitle">📝 <?php echo htmlspecialchars(t('grades.add_title')); ?></h2>

            <input type="hidden" id="examGradeExamId">
            <div class="modal-section">
                <h3><?php echo htmlspecialchars(t('nav.subjects')); ?></h3>
                <input type="text" id="examGradeSubject" placeholder="" readonly style="background:var(--color-bg-hover);cursor:default;">
            </div>
            <div class="modal-section">
                <h3><?php echo htmlspecialchars(t('grades.value_placeholder')); ?></h3>
                <input type="number" id="examGradeValue" placeholder="<?php echo htmlspecialchars(t('grades.value_placeholder')); ?>" min="0" max="15" step="1">
            </div>
            <div class="modal-section">
                <h3><?php echo htmlspecialchars(t('grades.weight_placeholder')); ?></h3>
                <input type="number" id="examGradeWeight" placeholder="<?php echo htmlspecialchars(t('grades.weight_placeholder')); ?>" min="0.5" step="0.5" value="1">
            </div>
            <div class="modal-section">
                <h3>Beschreibung (optional)</h3>
                <input type="text" id="examGradeDescription" placeholder="z.B. Klassenarbeit Datum...">
            </div>
            <div class="modal-section">
                <div>
                    <button class="modal-btn modal-btn-primary" onclick="setExamGrade()">Speichern</button>
                    <button class="modal-btn modal-btn-close" onclick="closeExamGradeModal()">Abbrechen</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ===== Toast Notification System =====
        function showToast(message, type = 'info', duration = 4000) {
            const container = document.getElementById('toastContainer');
            if (!container) return;
            const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.style.setProperty('--toast-duration', duration + 'ms');
            toast.style.position = 'relative';
            toast.style.overflow = 'hidden';
            toast.innerHTML = `
                <span class="toast-icon">${icons[type] || icons.info}</span>
                <span class="toast-message">${message.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</span>
                <button class="toast-close" aria-label="Schließen">&times;</button>
                <div class="toast-progress"></div>
            `;
            toast.querySelector('.toast-close').addEventListener('click', () => removeToast(toast));
            container.appendChild(toast);
            const timer = setTimeout(() => removeToast(toast), duration);
            toast._timer = timer;
        }
        function removeToast(toast) {
            if (toast._removed) return;
            toast._removed = true;
            clearTimeout(toast._timer);
            toast.classList.add('toast-removing');
            setTimeout(() => toast.remove(), 260);
        }

        // Theme Toggle
        // Theme Toggle (mit localStorage)
const themeToggle = document.getElementById('themeToggle');
const themeIcon = document.getElementById('themeIcon');
const themeText = document.getElementById('themeText');

function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);

    if (theme === 'dark') {
        themeIcon.textContent = '☀️';
        themeText.textContent = 'Light Mode';
    } else {
        themeIcon.textContent = '🌙';
        themeText.textContent = 'Dark Mode';
    }
}

// Initial Theme setzen
const currentTheme = localStorage.getItem('theme') || 'light';
applyTheme(currentTheme);

// Toggle Button
themeToggle.addEventListener('click', () => {
    const activeTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = activeTheme === 'dark' ? 'light' : 'dark';
    applyTheme(newTheme);
});


        // Navigation
        const navItems = document.querySelectorAll('.nav-item');
        const viewContents = document.querySelectorAll('.view-content');

        // Mobile sidebar
        (function() {
            const menuBtn = document.getElementById('mobileMenuBtn');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            function openSidebar() { sidebar.classList.add('open'); overlay.classList.add('open'); }
            function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('open'); }
            if (menuBtn) menuBtn.addEventListener('click', () => sidebar.classList.contains('open') ? closeSidebar() : openSidebar());
            if (overlay) overlay.addEventListener('click', closeSidebar);
            // Close sidebar when a nav item is clicked on mobile
            document.querySelectorAll('.nav-item').forEach(item => {
                item.addEventListener('click', () => { if (window.innerWidth <= 768) closeSidebar(); });
            });
        })();

        function mapTabParamToView(tabParam) {
            if (!tabParam) return 'overview';

            const normalized = String(tabParam).trim().toLowerCase();
            const tabMap = {
                overview: 'overview',
                ueberblick: 'overview',
                'uberblick': 'overview',
                subjects: 'subjects',
                fächer: 'subjects',
                facher: 'subjects',
                timetable: 'timetable',
                stundenplan: 'timetable',
                grades: 'grades',
                noten: 'grades',
                todos: 'todos',
                todo: 'todos',
                aufgaben: 'todos',
                exams: 'exams',
                klassenarbeiten: 'exams',
                homework: 'homework',
                hausaufgaben: 'homework',
                calendar: 'calendar',
                kalender: 'calendar',
                flashcards: 'flashcards',
                karteikarten: 'flashcards',
                files: 'files',
                dateien: 'files',
                'admin-messages': 'admin-messages',
                adminmessages: 'admin-messages',
                admin: 'admin'
            };

            return tabMap[normalized] || 'overview';
        }

        function mapViewToTabParam(viewId) {
            const viewMap = {
                overview: 'overview',
                subjects: 'subjects',
                timetable: 'stundenplan',
                grades: 'grades',
                todos: 'todos',
                exams: 'klassenarbeiten',
                homework: 'hausaufgaben',
                calendar: 'kalender',
                flashcards: 'flashcards',
                files: 'dateien',
                'admin-messages': 'admin-messages',
                admin: 'admin'
            };

            return viewMap[viewId] || 'overview';
        }

        function syncTabParam(viewId) {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', mapViewToTabParam(viewId));
            window.history.replaceState({}, '', url);
        }

        function refreshViewState(viewId) {
            if (viewId === 'overview') {
                renderOverview();
            } else if (viewId === 'subjects') {
                loadSubjects();
            } else if (viewId === 'calendar') {
                initCalendar();
            } else if (viewId === 'timetable') {
                renderTimetableView();
                renderHomework(false, 'homeworkGridTimetable');
            } else if (viewId === 'admin-messages') {
                loadAdminMessages();
            } else if (viewId === 'admin') {
                loadAdminPanel();
                loadAdminMessageManagement();
                loadAdminUsers();
            } else if (viewId === 'flashcards') {
                if (typeof fcShowDecksView === 'function') {
                    fcShowDecksView();
                } else if (typeof fcLoadDecks === 'function') {
                    fcLoadDecks();
                }
            }
        }

        function openViewById(viewId) {
            const targetNav = document.querySelector(`.nav-item[data-view="${viewId}"]`);
            if (targetNav) {
                targetNav.click();
                return;
            }

            const fallbackNav = document.querySelector('.nav-item[data-view="overview"]');
            if (fallbackNav) fallbackNav.click();
        }

        navItems.forEach(item => {
            item.addEventListener('click', () => {
                const viewId = item.getAttribute('data-view');
                
                navItems.forEach(nav => nav.classList.remove('active'));
                item.classList.add('active');
                
                viewContents.forEach(view => {
                    view.style.display = 'none';
                });
                
                const targetView = document.getElementById(viewId);
                if (targetView) {
                    targetView.style.display = 'block';
                }
                syncTabParam(viewId);
                refreshViewState(viewId);
            });
        });
        
        document.querySelectorAll('.widget-action').forEach(button => {
            button.addEventListener('click', () => {
                const viewId = button.getAttribute('data-view');
                 const targetNav = document.querySelector(`.nav-item[data-view="${viewId}"]`);
        
                if (targetNav) {
                    targetNav.click();
                }
            });
        });
        
        // ===== TO-DO API =====
        let todosData = null;
        let todoFilter = 'all';

        async function loadTodos() {
            try {
                const res = await fetch('todos/todo_load.php');
                if (!res.ok) { todosData = []; return; }
                todosData = await res.json();
                renderTodosUI();
                renderOverviewTodos();
                renderOverviewHomeworks();
                renderCalendar();
                renderOverviewCalendar();
            } catch {
                todosData = [];
                renderTodosUI();
                renderOverviewTodos();
                renderOverviewHomeworks();
                renderCalendar();
                renderOverviewCalendar();
            }
        }

        // ===== STUNDENPLAN API =====
        async function loadTimetable() {
            try {
                // entries
                const res = await fetch('timetable/timetable_load.php');
                if (res.ok) {
                    const rows = await res.json();
                    // only replace if backend actually returned something
                    if (rows && rows.length) {
                        timetableData = {};
                        rows.forEach(r => {
                            if (!timetableData[r.day]) timetableData[r.day] = {};
                            timetableData[r.day][r.period] = {
                                subject: r.subject || '',
                                room: r.room || ''
                            };
                        });
                        localStorage.setItem('timetable_data', JSON.stringify(timetableData));
                    }
                }
                // times table via PHP proxy
                const timesRes = await fetch('timetable/timetable_times.php');
                if (timesRes.ok) {
                    const timesObj = await timesRes.json();
                    if (timesObj && Object.keys(timesObj).length) {
                        timetableTimes = { ...DEFAULT_TIMES, ...timesObj };
                        localStorage.setItem('timetable_times', JSON.stringify(timetableTimes));
                    }
                }

                normalizeHomeworkData();
            } catch (e) {
                console.error('Timetable load error', e);
            }
            renderTimetableView();
            renderHomework(true, 'homeworkGrid');
            renderHomework(false, 'homeworkGridTimetable');
            renderOverviewTimetable();
            renderOverviewHomeworks();
        }

        function setTodoFilter(filter, btn) {
            todoFilter = filter;
            document.querySelectorAll('.todo-filter-btn').forEach(b => b.classList.remove('active'));
            if (btn) btn.classList.add('active');
            renderTodosUI();
        }

        function formatTodoDate(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr + 'T00:00:00');
            return d.toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' });
        }

        function getPriorityLabel(p) {
            return { high: 'Hoch', medium: 'Mittel', low: 'Niedrig' }[p] || p;
        }

        function renderTodosUI() {
            const list = document.getElementById('todosDetailList');
            if (!list) return;

            if (todosData === null) {
                list.innerHTML = '<div class="loading-spinner"><div class="spinner"></div><span>Lädt…</span></div>';
                return;
            }

            let filtered = todosData;
            if (todoFilter === 'open') filtered = todosData.filter(t => !t.done);
            if (todoFilter === 'done') filtered = todosData.filter(t =>  t.done);

            if (!filtered.length) {
                const msgs = {
                    done: { icon: '📋', title: 'Noch nichts erledigt', text: 'Erledigte Aufgaben erscheinen hier.' },
                    open: { icon: '🎉', title: 'Alles erledigt!', text: 'Super, keine offenen Aufgaben mehr.' },
                    all:  { icon: '✅', title: 'Keine Aufgaben', text: 'Erstelle deine erste Aufgabe oben.' }
                };
                const m = msgs[todoFilter] || msgs.all;
                list.innerHTML = `<div class="empty-state">
                    <div class="empty-state-icon">${m.icon}</div>
                    <div class="empty-state-title">${m.title}</div>
                    <div class="empty-state-text">${m.text}</div>
                </div>`;
                return;
            }

            const pOrder = { high: 0, medium: 1, low: 2 };
            const sorted = [...filtered].sort((a, b) => {
                if (!!a.done !== !!b.done) return a.done ? 1 : -1;
                return (pOrder[a.priority] ?? 1) - (pOrder[b.priority] ?? 1);
            });

            list.innerHTML = sorted.map(todo => `
                <div class="todo-item ${todo.done ? 'todo-done' : ''}" id="todo-${todo.id}">
                    <div class="todo-checkbox ${todo.done ? 'checked' : ''}" onclick="toggleTodoById('${escapeHtml(todo.id)}')"></div>
                    <div class="todo-body">
                        <div class="todo-text ${todo.done ? 'completed' : ''}">${escapeHtml(todo.title)}</div>
                        <div class="todo-meta">
                            ${todo.subject ? `<span class="todo-tag">${escapeHtml(todo.subject)}</span>` : ''}
                            ${todo.due_date ? `<span class="todo-due">📅 ${formatTodoDate(todo.due_date)}</span>` : ''}
                        </div>
                    </div>
                    <div class="todo-priority priority-${todo.priority}" title="${getPriorityLabel(todo.priority)}"></div>
                    <button class="btn-icon" onclick="deleteTodoById('${escapeHtml(todo.id)}')" title="Löschen" aria-label="Aufgabe löschen">🗑️</button>
                </div>
            `).join('');
        }

        async function addTodo() {
            const titleEl    = document.getElementById('todoTitle');
            const subjectEl  = document.getElementById('todoSubject');
            const dueDateEl  = document.getElementById('todoDueDate');
            const priorityEl = document.getElementById('todoPriority');
            if (!titleEl || !titleEl.value.trim()) return;
            try {
                const res = await fetch('todos/todo_add.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        title:    titleEl.value.trim(),
                        subject:  subjectEl  ? subjectEl.value.trim()  : '',
                        due_date: dueDateEl  ? (dueDateEl.value || '')  : '',
                        priority: priorityEl ? priorityEl.value         : 'medium'
                    })
                });
                if (res.ok) {
                    titleEl.value = '';
                    if (subjectEl)  subjectEl.value  = '';
                    if (dueDateEl)  dueDateEl.value  = '';
                    await loadTodos();
                }
            } catch { /* Server nicht erreichbar */ }
        }

        async function toggleTodoById(todoId) {
            try {
                const res = await fetch(`todos/todo_toggle.php?todo_id=${encodeURIComponent(todoId)}`, {
                    method: 'POST'
                });
                if (res.ok) await loadTodos();
            } catch { /* Server nicht erreichbar */ }
        }

        async function deleteTodoById(todoId) {
            try {
                const res = await fetch(`todos/todo_delete.php?todo_id=${encodeURIComponent(todoId)}`, {
                    method: 'POST'
                });
                if (res.ok) {
                    todosData = (todosData || []).filter(t => t.id !== todoId);
                    renderTodosUI();
                    renderOverviewTodos();
                    renderCalendar();
                    renderOverviewCalendar();
                }
            } catch { /* Server nicht erreichbar */ }
        }


        // Flashcard Functionality
        const OVERVIEW_LAST_FLASHCARD_DECK_KEY = 'last_flashcard_studied_deck';
        const OVERVIEW_FLASHCARD_LOADING = <?php echo json_encode(t('common.loading')); ?>;
        const OVERVIEW_FLASHCARD_NO_DECKS = <?php echo json_encode(trim(strip_tags(t('flashcards.no_decks')))); ?>;
        const OVERVIEW_FLASHCARD_NO_CARDS = <?php echo json_encode(trim(strip_tags(t('flashcards.no_cards')))); ?>;
        const OVERVIEW_FLASHCARD_QUESTION_LABEL = <?php echo json_encode(t('overview.flashcard_question_label')); ?>;
        let currentCard = 0;
        let overviewFlashcards = [];

        function getLastStudiedFlashcardDeckId() {
            const stored = readScopedJson(OVERVIEW_LAST_FLASHCARD_DECK_KEY, null, true);
            if (stored && typeof stored === 'object') {
                return stored.id || null;
            }
            return typeof stored === 'string' ? stored : null;
        }

        function updateOverviewFlashcardDeckLabel(label) {
            const deckNameEl = document.getElementById('overviewFlashcardDeckName');
            if (deckNameEl) {
                deckNameEl.textContent = label || '';
            }
        }

        function updateOverviewFlashcardNavigation() {
            const nav = document.getElementById('overviewFlashcardNav');
            if (nav) {
                nav.style.visibility = overviewFlashcards.length > 1 ? 'visible' : 'hidden';
            }
        }

        function setOverviewFlashcardMessage(message) {
            const flashcardElement = document.getElementById('flashcard');
            if (!flashcardElement) return;

            const front = flashcardElement.querySelector('.flashcard-front');
            const back = flashcardElement.querySelector('.flashcard-back');
            flashcardElement.classList.remove('flipped');

            if (front) front.innerHTML = `<p>${escapeHtml(message)}</p>`;
            if (back) back.innerHTML = `<p>${escapeHtml(message)}</p>`;

            updateCardCounter();
            updateOverviewFlashcardNavigation();
        }

        async function fetchOverviewDeckCards(deckId) {
            try {
                const res = await fetch(`flashcards/cards_load.php?deck_id=${encodeURIComponent(deckId)}`);
                return res.ok ? await res.json() : [];
            } catch {
                return [];
            }
        }

        async function renderOverviewFlashcards() {
            updateOverviewFlashcardDeckLabel(OVERVIEW_FLASHCARD_LOADING);
            setOverviewFlashcardMessage(OVERVIEW_FLASHCARD_LOADING);

            let decks = [];
            try {
                const res = await fetch('flashcards/decks_load.php');
                decks = res.ok ? await res.json() : [];
            } catch {
                decks = [];
            }

            if (!decks.length) {
                overviewFlashcards = [];
                updateOverviewFlashcardDeckLabel('');
                setOverviewFlashcardMessage(OVERVIEW_FLASHCARD_NO_DECKS);
                return;
            }

            const preferredDeckId = getLastStudiedFlashcardDeckId();
            const preferredDeck = decks.find(deck => deck.id === preferredDeckId) || null;
            const candidateDecks = preferredDeck
                ? [preferredDeck].concat(decks.filter(deck => deck.id !== preferredDeck.id))
                : decks.slice();

            let selectedDeck = null;
            let selectedCards = [];

            for (const deck of candidateDecks) {
                if (Number(deck.card_count || 0) <= 0) {
                    continue;
                }

                const cards = await fetchOverviewDeckCards(deck.id);
                if (cards.length) {
                    selectedDeck = deck;
                    selectedCards = cards;
                    break;
                }
            }

            if (!selectedDeck) {
                selectedDeck = preferredDeck || decks[0];
                selectedCards = await fetchOverviewDeckCards(selectedDeck.id);
            }

            overviewFlashcards = selectedCards.map(card => ({
                question: card.front,
                answer: card.back
            }));
            currentCard = 0;

            updateOverviewFlashcardDeckLabel(selectedDeck ? selectedDeck.name : '');

            if (!overviewFlashcards.length) {
                setOverviewFlashcardMessage(OVERVIEW_FLASHCARD_NO_CARDS);
                return;
            }

            updateFlashcard();
            updateCardCounter();
            updateOverviewFlashcardNavigation();
        }

        function flipCard(cardId) {
            const card = document.getElementById(cardId);
            if (card) {
                card.classList.toggle('flipped');
            }
        }

        function nextCard() {
            if (!overviewFlashcards.length) return;
            currentCard = (currentCard + 1) % overviewFlashcards.length;
            updateFlashcard();
            updateCardCounter();
        }

        function previousCard() {
            if (!overviewFlashcards.length) return;
            currentCard = (currentCard - 1 + overviewFlashcards.length) % overviewFlashcards.length;
            updateFlashcard();
            updateCardCounter();
        }

        function updateCardCounter() {
            const counter = document.getElementById('cardCounter');
            if (counter) {
                counter.textContent = overviewFlashcards.length ? `${currentCard + 1} / ${overviewFlashcards.length}` : '';
            }
        }

        function updateFlashcard() {
            if (!overviewFlashcards.length) {
                updateOverviewFlashcardNavigation();
                return;
            }

            const card = overviewFlashcards[currentCard];
            const flashcardElement = document.getElementById('flashcard');
            const flashcardDetail = document.getElementById('flashcardDetail');
            
            // Remove flipped state
            if (flashcardElement) flashcardElement.classList.remove('flipped');
            if (flashcardDetail) flashcardDetail.classList.remove('flipped');
            
            // Update overview card
            if (flashcardElement) {
                const front = flashcardElement.querySelector('.flashcard-front');
                const back = flashcardElement.querySelector('.flashcard-back');
                if (front) front.innerHTML = `<p><strong>${escapeHtml(OVERVIEW_FLASHCARD_QUESTION_LABEL)}</strong> ${escapeHtml(card.question)}</p>`;
                if (back) back.innerHTML = `<p>${escapeHtml(card.answer)}</p>`;
            }
            
            // Update detail view card
            if (flashcardDetail) {
                const frontDetail = flashcardDetail.querySelector('.flashcard-front');
                const backDetail = flashcardDetail.querySelector('.flashcard-back');
                if (frontDetail) frontDetail.innerHTML = `<p><strong>${escapeHtml(OVERVIEW_FLASHCARD_QUESTION_LABEL)}</strong> ${escapeHtml(card.question)}</p>`;
                if (backDetail) backDetail.innerHTML = `<p>${escapeHtml(card.answer)}</p>`;
            }

            updateOverviewFlashcardNavigation();
        }

        // Files Functionality

        // ===== HILFSFUNKTION =====
        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        // ===== STUNDENPLAN =====
        const TT_DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        const TT_DAY_NAMES = {
            monday: <?php echo json_encode(t('timetable.day.monday')); ?>,
            tuesday: <?php echo json_encode(t('timetable.day.tuesday')); ?>,
            wednesday: <?php echo json_encode(t('timetable.day.wednesday')); ?>,
            thursday: <?php echo json_encode(t('timetable.day.thursday')); ?>,
            friday: <?php echo json_encode(t('timetable.day.friday')); ?>
        };
        const HW_NONE_MSG          = <?php echo json_encode(t('homework.none')); ?>;
        const HW_PLACEHOLDER       = <?php echo json_encode(t('homework.input_placeholder')); ?>;
        const HW_NONE_ENTERED_MSG  = <?php echo json_encode(t('homework.none_entered')); ?>;
        const TT_PERIOD_LABEL      = <?php echo json_encode(t('timetable.period_label')); ?>;
        const HW_DELETE_TITLE      = <?php echo json_encode(t('js.delete')); ?>;

        // ===== KALENDER-LOKALISIERUNG =====
        const CAL_LOCALE = <?php echo json_encode($current_locale === 'en' ? 'en-GB' : 'de-DE'); ?>;
        const CAL_MONTH_NAMES = <?php echo json_encode([
            t('calendar.month.jan'), t('calendar.month.feb'), t('calendar.month.mar'),
            t('calendar.month.apr'), t('calendar.month.may'), t('calendar.month.jun'),
            t('calendar.month.jul'), t('calendar.month.aug'), t('calendar.month.sep'),
            t('calendar.month.oct'), t('calendar.month.nov'), t('calendar.month.dec'),
        ]); ?>;
        const CAL_DAY_SHORT = <?php echo json_encode([
            t('calendar.day_short.mo'), t('calendar.day_short.tu'), t('calendar.day_short.we'),
            t('calendar.day_short.th'), t('calendar.day_short.fr'), t('calendar.day_short.sa'),
            t('calendar.day_short.su'),
        ]); ?>;
        const CAL_NO_DAY_SELECTED   = <?php echo json_encode(t('calendar.no_day_selected')); ?>;
        const CAL_SELECT_A_DAY      = <?php echo json_encode(t('calendar.select_a_day')); ?>;
        const CAL_SELECTED_PREFIX   = <?php echo json_encode(t('calendar.selected_prefix')); ?>;
        const CAL_NO_DAY            = <?php echo json_encode(t('calendar.no_day')); ?>;
        const CAL_NO_EVENTS         = <?php echo json_encode(t('calendar.no_events')); ?>;
        const CAL_DELETE_OR_SERIES  = <?php echo json_encode(t('calendar.delete_or_series')); ?>;
        const CAL_REC_WEEKLY        = <?php echo json_encode(t('calendar.recurrence.weekly')); ?>;
        const CAL_REC_MONTHLY       = <?php echo json_encode(t('calendar.recurrence.monthly')); ?>;
        const CAL_REC_YEARLY        = <?php echo json_encode(t('calendar.recurrence.yearly')); ?>;
        const CAL_REC_DELETE_CONFIRM = <?php echo json_encode(t('calendar.recurring_delete_confirm')); ?>;
        const CAL_NO_UPCOMING       = <?php echo json_encode(t('calendar.no_upcoming')); ?>;
        const TT_DAY_INDEX = { monday: 1, tuesday: 2, wednesday: 3, thursday: 4, friday: 5 };

        const DEFAULT_TIMES = {
            1: '07:45', 2: '08:30', 3: '09:15', 4: '10:15', 5: '11:00',
            6: '11:45', 7: '12:45', 8: '13:30', 9: '14:15', 10: '15:00'
        };

        const CURRENT_USER_ID = "<?php echo htmlspecialchars($_SESSION['user_id']); ?>";
        const CONFIG_BACKEND_URL = "<?php echo defined('BACKEND_BASE_URL') ? BACKEND_BASE_URL : ''; ?>";
        function resolveBackendUrl() {
            const { protocol, hostname } = window.location;
            const isPageLocal = hostname === 'localhost' || hostname === '127.0.0.1';

            if (CONFIG_BACKEND_URL) {
                try {
                    const cfg = new URL(CONFIG_BACKEND_URL);
                    const isCfgLocal = cfg.hostname === 'localhost' || cfg.hostname === '127.0.0.1';
                    // In Remote-Umgebungen (z. B. Codespaces) darf localhost aus PHP nicht erzwungen werden.
                    if (!(isCfgLocal && !isPageLocal)) {
                        return CONFIG_BACKEND_URL;
                    }
                } catch {
                    // Bei ungültiger URL auf automatische Erkennung zurückfallen.
                }
            }

            const codespacesMatch = hostname.match(/^(.*)-\d+(\..+)$/);
            if (codespacesMatch) {
                return `${protocol}//${codespacesMatch[1]}-8000${codespacesMatch[2]}`;
            }

            if (isPageLocal) {
                return 'http://127.0.0.1:8000';
            }

            return `${protocol}//${hostname}:8000`;
        }
        const BACKEND_URL = resolveBackendUrl();

        function getScopedStorageKey(baseKey) {
            return `${baseKey}_${CURRENT_USER_ID}`;
        }

        function readScopedJson(baseKey, fallback, includeLegacy = false) {
            const scopedRaw = localStorage.getItem(getScopedStorageKey(baseKey));
            if (scopedRaw !== null) {
                try {
                    return JSON.parse(scopedRaw);
                } catch {
                    return fallback;
                }
            }

            if (includeLegacy) {
                const legacyRaw = localStorage.getItem(baseKey);
                if (legacyRaw !== null) {
                    try {
                        return JSON.parse(legacyRaw);
                    } catch {
                        return fallback;
                    }
                }
            }

            return fallback;
        }

        function writeScopedJson(baseKey, value) {
            localStorage.setItem(getScopedStorageKey(baseKey), JSON.stringify(value));
        }

        function createClientTempId() {
            if (window.crypto && typeof window.crypto.randomUUID === 'function') {
                return window.crypto.randomUUID();
            }
            return `tmp-${Date.now()}-${Math.random().toString(16).slice(2)}`;
        }

        function normalizeHomeworkEntry(entry) {
            if (typeof entry === 'string') {
                const title = entry.trim();
                return title ? { id: createClientTempId(), title } : null;
            }
            if (!entry || typeof entry !== 'object') return null;

            const title = String(entry.title || entry.text || '').trim();
            if (!title) return null;

            return {
                id: entry.id || createClientTempId(),
                title
            };
        }

        let timetableData  = JSON.parse(localStorage.getItem('timetable_data'))  || {};
        let timetableTimes = JSON.parse(localStorage.getItem('timetable_times')) || { ...DEFAULT_TIMES };
        let homework       = readScopedJson('homework_data', {}, true);
        let exams          = readScopedJson('exams_data', [], true);
        // zusätzliche Kalendereinträge, unabhängig von Hausaufgaben/Klassenarbeiten
        let calendarExtras = readScopedJson('calendar_extras', [], true);

        function getScheduledPeriodsForDay(day) {
            const dayData = timetableData[day] || {};
            return Object.keys(dayData)
                .map(p => parseInt(p, 10))
                .filter(p => Number.isInteger(p) && p >= 1 && p <= 10 && dayData[p] && dayData[p].subject)
                .sort((a, b) => a - b);
        }

        function normalizeHomeworkData() {
            const source = homework && typeof homework === 'object' ? homework : {};
            const normalized = {};

            TT_DAYS.forEach(day => {
                const dayRaw = source[day];
                const dayOut = {};
                const scheduled = getScheduledPeriodsForDay(day);
                const fallbackPeriod = scheduled.length ? String(scheduled[0]) : null;

                const pushItems = (periodKey, values) => {
                    const clean = Array.isArray(values)
                        ? values.map(normalizeHomeworkEntry).filter(Boolean)
                        : [];
                    if (!clean.length) return;

                    let target = periodKey;
                    if (!target || !scheduled.includes(parseInt(target, 10))) {
                        if (!fallbackPeriod) return;
                        target = fallbackPeriod;
                    }
                    if (!dayOut[target]) dayOut[target] = [];
                    dayOut[target].push(...clean);
                };

                if (Array.isArray(dayRaw)) {
                    // Legacy format: { monday: ["...", "..."] }
                    pushItems(fallbackPeriod, dayRaw);
                } else if (dayRaw && typeof dayRaw === 'object') {
                    Object.entries(dayRaw).forEach(([periodKey, values]) => {
                        const parsed = parseInt(periodKey, 10);
                        pushItems(Number.isInteger(parsed) ? String(parsed) : null, values);
                    });
                }

                normalized[day] = dayOut;
            });

            homework = normalized;
            writeScopedJson('homework_data', homework);
        }

        normalizeHomeworkData();

        function cacheExamData() {
            writeScopedJson('exams_data', exams);
        }

        function cacheCalendarExtras() {
            writeScopedJson('calendar_extras', calendarExtras);
        }

        function normalizeCalendarExtraEntry(entry) {
            if (!entry || typeof entry !== 'object') return null;

            const title = String(entry.title || '').trim();
            const date = String(entry.date || '').trim();
            if (!title || !date) return null;

            return {
                ...entry,
                id: entry.id || null,
                title,
                date,
                description: String(entry.description || '').trim(),
                color: normalizeHexColor(entry.color),
                recurrence: normalizeCalendarRecurrence(entry),
                exception_dates: normalizeCalendarExceptionDates(entry.exception_dates),
                start_time: normalizeTimeValue(entry.start_time),
                end_time: normalizeTimeValue(entry.end_time)
            };
        }

        function normalizeCalendarRecurrence(entry) {
            const recurrence = String(entry?.recurrence || '').trim().toLowerCase();
            if (recurrence === 'weekly' || recurrence === 'monthly' || recurrence === 'yearly') return recurrence;
            if (entry?.repeat_weekly === true || entry?.repeat_weekly === 1 || entry?.repeat_weekly === '1') {
                return 'weekly';
            }
            return 'none';
        }

        function normalizeCalendarExceptionDates(value) {
            let parsed = value;
            if (typeof value === 'string') {
                try {
                    parsed = JSON.parse(value || '[]');
                } catch {
                    parsed = [];
                }
            }

            if (!Array.isArray(parsed)) return [];
            return parsed
                .map(item => String(item || '').trim())
                .filter(Boolean)
                .sort();
        }

        function parseDateOnly(dateStr) {
            const [year, month, day] = String(dateStr || '').split('-').map(part => parseInt(part, 10));
            if (!year || !month || !day) return null;
            return new Date(year, month - 1, day);
        }

        function addDaysToDateStr(dateStr, days) {
            const date = parseDateOnly(dateStr);
            if (!date) return '';
            date.setDate(date.getDate() + days);
            return toDateStr(date);
        }

        function getDaysInMonth(year, monthIndex) {
            return new Date(year, monthIndex + 1, 0).getDate();
        }

        function addMonthsToDateStr(baseDateStr, monthOffset) {
            const baseDate = parseDateOnly(baseDateStr);
            if (!baseDate) return '';

            const baseDay = baseDate.getDate();
            const targetMonthIndex = (baseDate.getMonth() + monthOffset);
            const targetYear = baseDate.getFullYear() + Math.floor(targetMonthIndex / 12);
            const normalizedMonthIndex = ((targetMonthIndex % 12) + 12) % 12;
            const targetDay = Math.min(baseDay, getDaysInMonth(targetYear, normalizedMonthIndex));

            return toDateStr(new Date(targetYear, normalizedMonthIndex, targetDay));
        }

        function getMonthDifference(startDateStr, endDateStr) {
            const start = parseDateOnly(startDateStr);
            const end = parseDateOnly(endDateStr);
            if (!start || !end) return NaN;

            return (end.getFullYear() - start.getFullYear()) * 12 + (end.getMonth() - start.getMonth());
        }

        function getYearsInDateRange(startDateStr = null, endDateStr = null) {
            const today = new Date();
            const fallbackStart = startDateStr || `${today.getFullYear()}-01-01`;
            const fallbackEnd = endDateStr || `${today.getFullYear()}-12-31`;
            const startYear = parseInt(String(fallbackStart).slice(0, 4), 10);
            const endYear = parseInt(String(fallbackEnd).slice(0, 4), 10);
            if (!Number.isInteger(startYear) || !Number.isInteger(endYear)) {
                return [today.getFullYear()];
            }

            const years = [];
            const minYear = Math.min(startYear, endYear);
            const maxYear = Math.max(startYear, endYear);
            for (let year = minYear; year <= maxYear; year++) {
                years.push(year);
            }
            return years;
        }

        function getEasterSunday(year) {
            const a = year % 19;
            const b = Math.floor(year / 100);
            const c = year % 100;
            const d = Math.floor(b / 4);
            const e = b % 4;
            const f = Math.floor((b + 8) / 25);
            const g = Math.floor((b - f + 1) / 3);
            const h = (19 * a + b - d - g + 15) % 30;
            const i = Math.floor(c / 4);
            const k = c % 4;
            const l = (32 + 2 * e + 2 * i - h - k) % 7;
            const m = Math.floor((a + 11 * h + 22 * l) / 451);
            const month = Math.floor((h + l - 7 * m + 114) / 31);
            const day = ((h + l - 7 * m + 114) % 31) + 1;
            return toDateStr(new Date(year, month - 1, day));
        }

        function buildGermanHolidayItems(startDateStr = null, endDateStr = null) {
            const items = [];

            getYearsInDateRange(startDateStr, endDateStr).forEach(year => {
                const easterSunday = getEasterSunday(year);
                const holidays = [
                    { date: `${year}-01-01`, title: 'Neujahr' },
                    { date: addDaysToDateStr(easterSunday, -2), title: 'Karfreitag' },
                    { date: addDaysToDateStr(easterSunday, 1), title: 'Ostermontag' },
                    { date: `${year}-05-01`, title: 'Tag der Arbeit' },
                    { date: addDaysToDateStr(easterSunday, 39), title: 'Christi Himmelfahrt' },
                    { date: addDaysToDateStr(easterSunday, 50), title: 'Pfingstmontag' },
                    { date: `${year}-10-03`, title: 'Tag der Deutschen Einheit' },
                    { date: `${year}-12-25`, title: '1. Weihnachtstag' },
                    { date: `${year}-12-26`, title: '2. Weihnachtstag' }
                ];

                holidays.forEach(holiday => {
                    if (!isDateWithinRange(holiday.date, startDateStr, endDateStr)) return;
                    items.push({
                        date: holiday.date,
                        title: holiday.title,
                        description: 'Deutscher Feiertag',
                        type: 'holiday'
                    });
                });
            });

            return items;
        }

        function dateDiffInDays(startDateStr, endDateStr) {
            const start = parseDateOnly(startDateStr);
            const end = parseDateOnly(endDateStr);
            if (!start || !end) return NaN;
            const msPerDay = 1000 * 60 * 60 * 24;
            return Math.round((end.getTime() - start.getTime()) / msPerDay);
        }

        function isDateWithinRange(dateStr, startDateStr, endDateStr) {
            if (startDateStr && dateStr < startDateStr) return false;
            if (endDateStr && dateStr > endDateStr) return false;
            return true;
        }

        function buildCalendarExtraItems(startDateStr = null, endDateStr = null) {
            const items = [];

            calendarExtras.forEach(entry => {
                const ev = normalizeCalendarExtraEntry(entry);
                if (!ev) return;

                if (ev.recurrence === 'none') {
                    if (isDateWithinRange(ev.date, startDateStr, endDateStr)) {
                        items.push({
                            id: ev.id,
                            date: ev.date,
                            title: ev.title,
                            description: ev.description,
                            start_time: ev.start_time,
                            end_time: ev.end_time,
                            color: ev.color,
                            type: 'extra',
                            recurrence: 'none',
                            exception_dates: ev.exception_dates
                        });
                    }
                    return;
                }

                const rangeEnd = endDateStr || addDaysToDateStr(ev.date, 365);
                const rangeStart = startDateStr && startDateStr > ev.date ? startDateStr : ev.date;

                const pushOccurrence = (occurrenceDate) => {
                    if (!occurrenceDate || ev.exception_dates.includes(occurrenceDate)) return;
                    items.push({
                        id: ev.id,
                        date: occurrenceDate,
                        title: ev.title,
                        description: ev.description,
                        start_time: ev.start_time,
                        end_time: ev.end_time,
                        color: ev.color,
                        type: 'extra',
                        recurrence: ev.recurrence,
                        exception_dates: ev.exception_dates,
                        base_date: ev.date
                    });
                };

                if (ev.recurrence === 'weekly') {
                    const diffFromBase = dateDiffInDays(ev.date, rangeStart);
                    const offsetDays = Number.isNaN(diffFromBase) ? 0 : ((diffFromBase % 7) + 7) % 7;
                    let occurrenceDate = offsetDays === 0 ? rangeStart : addDaysToDateStr(rangeStart, 7 - offsetDays);

                    if (occurrenceDate < ev.date) {
                        occurrenceDate = ev.date;
                    }

                    while (occurrenceDate && occurrenceDate <= rangeEnd) {
                        pushOccurrence(occurrenceDate);
                        occurrenceDate = addDaysToDateStr(occurrenceDate, 7);
                    }
                    return;
                }

                if (ev.recurrence === 'yearly') {
                    const rangeStartDate = parseDateOnly(rangeStart);
                    const baseDate = parseDateOnly(ev.date);
                    let yearOffset = 0;

                    if (rangeStartDate && baseDate) {
                        yearOffset = Math.max(0, rangeStartDate.getFullYear() - baseDate.getFullYear());
                    }

                    let occurrenceDate = addMonthsToDateStr(ev.date, yearOffset * 12);
                    while (occurrenceDate && occurrenceDate < rangeStart) {
                        yearOffset += 1;
                        occurrenceDate = addMonthsToDateStr(ev.date, yearOffset * 12);
                    }

                    while (occurrenceDate && occurrenceDate <= rangeEnd) {
                        pushOccurrence(occurrenceDate);
                        yearOffset += 1;
                        occurrenceDate = addMonthsToDateStr(ev.date, yearOffset * 12);
                    }
                    return;
                }

                const monthStartOffset = Math.max(0, Number.isNaN(getMonthDifference(ev.date, rangeStart)) ? 0 : getMonthDifference(ev.date, rangeStart));
                let monthOffset = monthStartOffset;
                let occurrenceDate = addMonthsToDateStr(ev.date, monthOffset);
                while (occurrenceDate && occurrenceDate < rangeStart) {
                    monthOffset += 1;
                    occurrenceDate = addMonthsToDateStr(ev.date, monthOffset);
                }

                while (occurrenceDate && occurrenceDate <= rangeEnd) {
                    pushOccurrence(occurrenceDate);
                    monthOffset += 1;
                    occurrenceDate = addMonthsToDateStr(ev.date, monthOffset);
                }
            });

            return items;
        }

        function buildHomeworkMap(rows) {
            const mapped = {};
            TT_DAYS.forEach(day => {
                mapped[day] = {};
            });

            (rows || []).forEach(row => {
                if (!row || !TT_DAYS.includes(row.day)) return;
                const period = parseInt(row.period, 10);
                if (!Number.isInteger(period) || period < 1 || period > 10) return;
                const key = String(period);
                if (!mapped[row.day][key]) mapped[row.day][key] = [];
                mapped[row.day][key].push({
                    id: row.id,
                    title: String(row.title || '').trim()
                });
            });

            return mapped;
        }

        async function loadHomeworkData() {
            try {
                const res = await fetch('homework/homework_load.php');
                if (!res.ok) throw new Error();
                const rows = await res.json();
                homework = buildHomeworkMap(rows);
                normalizeHomeworkData();
            } catch {
                normalizeHomeworkData();
            }

            renderHomework(true, 'homeworkGrid');
            renderHomework(false, 'homeworkGridTimetable');
            renderTimetableView();
            renderOverviewHomeworks();
            renderCalendar();
            renderOverviewCalendar();
        }

        async function loadExamsData() {
            try {
                const res = await fetch('exams/exams_load.php');
                if (!res.ok) throw new Error();
                const rows = await res.json();
                exams = Array.isArray(rows) ? rows : [];
                cacheExamData();
            } catch {
                exams = Array.isArray(exams) ? exams : [];
                cacheExamData();
            }

            renderExams();
            renderOverviewExams();
            renderCalendar();
            renderOverviewCalendar();
        }

        async function loadCalendarExtras() {
            try {
                const res = await fetch('calendar/calendar_load.php');
                if (!res.ok) throw new Error();
                const rows = await res.json();
                calendarExtras = Array.isArray(rows)
                    ? rows.map(normalizeCalendarExtraEntry).filter(Boolean)
                    : [];
                cacheCalendarExtras();
            } catch {
                calendarExtras = Array.isArray(calendarExtras)
                    ? calendarExtras.map(normalizeCalendarExtraEntry).filter(Boolean)
                    : [];
                cacheCalendarExtras();
            }

            updateCalendarTitleSuggestions();

            renderCalendar();
            if (currentSelectedDate) showEventsForDate(currentSelectedDate, false);
            renderOverviewCalendar();
        }

        // Hilfsfunktion: liefert alle kalenderbezogenen Objekte (extras, exams, todos)
        function getAllCalendarItems(startDateStr = null, endDateStr = null) {
            let items = [];
            items.push(...buildGermanHolidayItems(startDateStr, endDateStr));
            // extras
            items.push(...buildCalendarExtraItems(startDateStr, endDateStr));
            // exams
            exams.forEach(ev => {
                if (!isDateWithinRange(ev.date, startDateStr, endDateStr)) return;
                items.push({
                    date: ev.date,
                    title: ev.subject,
                    description: ev.topic || '',
                    type: 'exam'
                });
            });
            // todos (nutzt due_date)
            if (todosData) {
                todosData.forEach(td => {
                    if (td.due_date) {
                        if (!isDateWithinRange(td.due_date, startDateStr, endDateStr)) return;
                        items.push({
                            date: td.due_date,
                            title: td.title,
                            description: td.subject ? td.subject : '',
                            type: 'todo'
                        });
                    }
                });
            }
            return items;
        }

        function getCurrentDayKey() {
            const idx = new Date().getDay(); // 0=Sun
            return ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'][idx];
        }

        function getMaxPeriod() {
            let max = 6;
            TT_DAYS.forEach(day => {
                const d = timetableData[day] || {};
                Object.keys(d).forEach(p => {
                    if (d[p] && d[p].subject) max = Math.max(max, parseInt(p));
                });
            });
            return Math.min(max, 10);
        }

        /** Gibt ein Objekt { "monday-3": [exam, ...] } für die aktuelle Woche zurück */
        function getExamHighlights() {
            const now = new Date();
            const dow = now.getDay();
            const monday = new Date(now);
            monday.setDate(now.getDate() - (dow === 0 ? 6 : dow - 1));
            monday.setHours(0, 0, 0, 0);
            const friday = new Date(monday);
            friday.setDate(monday.getDate() + 4);
            friday.setHours(23, 59, 59, 999);

            const highlights = {};
            exams.forEach(exam => {
                const examDate = new Date(exam.date + 'T00:00:00');
                if (examDate >= monday && examDate <= friday) {
                    const dayKey = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'][examDate.getDay()];
                    const start = parseInt(exam.period, 10);
                    if (!Number.isInteger(start) || start < 1 || start > 10) return;

                    let end = parseInt(exam.end_period, 10);
                    if (!Number.isInteger(end)) end = start;
                    if (end < start) end = start;
                    end = Math.min(end, 10);

                    for (let p = start; p <= end; p++) {
                        const key = `${dayKey}-${p}`;
                        if (!highlights[key]) highlights[key] = [];
                        highlights[key].push(exam);
                    }
                }
            });
            return highlights;
        }

        function getHomeworkHighlights() {
            // returns an object { "monday-3": ["homework1", ...], ... }
            const highlights = {};
            TT_DAYS.forEach(day => {
                const dayData = homework[day] || {};
                Object.entries(dayData).forEach(([period, list]) => {
                    if (Array.isArray(list) && list.length) {
                        highlights[`${day}-${period}`] = list.map(item => item.title);
                    }
                });
            });
            return highlights;
        }


        function renderTimetableView() {
            const grid = document.getElementById('timetableGrid');
            if (!grid) return;
            const maxPeriod     = getMaxPeriod();
            const todayKey      = getCurrentDayKey();
            const examHighlights = getExamHighlights();
            const homeworkHighlights = getHomeworkHighlights();

            let html = '<div class="tt-grid">';
            // Kopfzeile
            html += '<div class="tt-header-cell"></div>';
            TT_DAYS.forEach(day => {
                const isToday = day === todayKey;
                html += `<div class="tt-header-cell ${isToday ? 'today-header' : ''}">${TT_DAY_NAMES[day]}</div>`;
            });

            // Stunden-Zeilen
            for (let p = 1; p <= maxPeriod; p++) {
                const time = timetableTimes[p] || '';
                html += `<div class="tt-time-cell">
                    <span class="tt-period-num">${p}.</span>
                    <span class="tt-time-label">${escapeHtml(time)}</span>
                </div>`;

                TT_DAYS.forEach(day => {
                    const isToday  = day === todayKey;
                    const cell     = (timetableData[day] || {})[p] || {};
                    const subject  = cell.subject || '';
                    const room     = cell.room     || '';
                    const examKey  = `${day}-${p}`;
                    const hwKey    = `${day}-${p}`;
                    const hasExam  = !!examHighlights[examKey];
                    const hwList   = homeworkHighlights[hwKey] || [];

                    let cls = 'tt-subject-cell';
                    if (isToday) cls += ' today-col';
                    if (hasExam) cls += ' has-exam';
                    if (!hasExam && hwList.length) cls += ' has-homework';

                    html += `<div class="${cls}">`;
                    if (subject) {
                        html += `<span class="tt-subject-name">${escapeHtml(subject)}</span>`;
                        if (room) html += `<span class="tt-room">${escapeHtml(room)}</span>`;
                    }
                    if (!hasExam) {
                        hwList.forEach(hw => {
                            html += `<span class="tt-homework-badge">📚 ${escapeHtml(hw)}</span>`;
                        });
                    }
                    html += '</div>';
                });
            }
            html += '</div>';
            grid.innerHTML = html;
        }

        function renderTimetableEdit() {
            // Zeiten-Editor
            const timesEditor = document.getElementById('periodTimesEditor');
            if (timesEditor) {
                let html = '<div class="tt-times-grid">';
                for (let p = 1; p <= 10; p++) {
                    const t = timetableTimes[p] || '';
                    html += `<div class="tt-time-item">
                        <label>${p}. Stunde</label>
                        <input type="time" id="periodTime_${p}" value="${escapeHtml(t)}">
                    </div>`;
                }
                html += '</div>';
                timesEditor.innerHTML = html;
            }

            // Stunden-Tabelle
            const editor = document.getElementById('timetableEditor');
            if (!editor) return;
            let html = '<table class="tt-editor-table"><thead><tr><th>Std.</th>';
            TT_DAYS.forEach(day => {
                html += `<th>${TT_DAY_NAMES[day]}</th>`;
            });
            html += '</tr></thead><tbody>';
            for (let p = 1; p <= 10; p++) {
                html += `<tr><td class="tt-period-label">${p}.</td>`;
                TT_DAYS.forEach(day => {
                    const cell    = (timetableData[day] || {})[p] || {};
                    const subject = cell.subject || '';
                    const room    = cell.room    || '';
                    html += `<td><div class="tt-editor-cell">
                        <select id="tt_${day}_${p}_subject" data-subject-dropdown style="width: 100%; margin-bottom: 0.25rem;">
                            <option value="${escapeHtml(subject)}">${escapeHtml(subject) || '-- Fach wählen --'}</option>
                        </select>
                        <input type="text" id="tt_${day}_${p}_room"    value="${escapeHtml(room)}"    placeholder="Raum" style="width: 100%;">
                    </div></td>`;
                });
                html += '</tr>';
            }
            html += '</tbody></table>';
            editor.innerHTML = html;
            // Dropdowns mit Fächern füllen
            setTimeout(() => populateSubjectDropdowns(), 100);
        }

        function toggleTimetableEdit() {
            const viewMode = document.getElementById('timetableViewMode');
            const editMode = document.getElementById('timetableEditMode');
            const btn      = document.getElementById('timetableEditBtn');
            if (editMode.style.display === 'none') {
                viewMode.style.display = 'none';
                editMode.style.display = 'block';
                btn.textContent = '👁️ Ansicht';
                renderTimetableEdit();
            } else {
                cancelTimetableEdit();
            }
        }

        function cancelTimetableEdit() {
            document.getElementById('timetableViewMode').style.display = 'block';
            document.getElementById('timetableEditMode').style.display = 'none';
            document.getElementById('timetableEditBtn').textContent = '✏️ Bearbeiten';
        }

        async function saveTimetable() {
            // Zeiten speichern
            for (let p = 1; p <= 10; p++) {
                const inp = document.getElementById(`periodTime_${p}`);
                if (inp) timetableTimes[p] = inp.value;
            }
            localStorage.setItem('timetable_times', JSON.stringify(timetableTimes));

            // Fächer speichern
            const newData = {};
            TT_DAYS.forEach(day => {
                newData[day] = {};
                for (let p = 1; p <= 10; p++) {
                    const sInp = document.getElementById(`tt_${day}_${p}_subject`);
                    const rInp = document.getElementById(`tt_${day}_${p}_room`);
                    const s = sInp ? sInp.value.trim() : '';
                    const r = rInp ? rInp.value.trim() : '';
                    if (s || r) newData[day][p] = { subject: s, room: r };
                }
            });
            timetableData = newData;
            localStorage.setItem('timetable_data', JSON.stringify(timetableData));
            normalizeHomeworkData();

            // Synchronisation mit Backend
            try {
                const entries = [];
                TT_DAYS.forEach(day => {
                    for (let p = 1; p <= 10; p++) {
                        const cell = (timetableData[day] || {})[p];
                        if (cell && cell.subject) {
                            entries.push({
                                day: day,
                                period: p,
                                time: timetableTimes[p] || '',
                                subject: cell.subject,
                                room: cell.room || ''
                            });
                        }
                    }
                });
                await fetch('timetable/timetable_save.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ entries: entries, times: timetableTimes })
                });
            } catch (err) {
                console.error('Fehler beim Speichern des Stundenplans auf dem Server', err);
            }

            cancelTimetableEdit();
            renderTimetableView();
            renderOverviewTimetable();
        }

        // ===== HAUSAUFGABEN =====
        // renderHomework(editable, containerId)
        function renderHomework(editable = true, containerId = 'homeworkGrid') {
            const grid = document.getElementById(containerId);
            if (!grid) return;
            const todayKey = getCurrentDayKey();
            let html = '<div class="tt-homework-grid">';
            TT_DAYS.forEach(day => {
                const isToday = day === todayKey;
                const dayHw   = homework[day] || {};
                const periods = getScheduledPeriodsForDay(day);
                const hasHomework = Object.values(dayHw).some(list => Array.isArray(list) && list.length > 0);
                html += `<div class="tt-hw-day ${isToday ? 'today' : ''}">
                    <div class="tt-hw-day-title">${TT_DAY_NAMES[day]}</div>`;

                if (!periods.length) {
                    html += '<p style="font-size:0.82rem;color:var(--color-text-muted);">' + HW_NONE_MSG + '</p>';
                } else if (!editable && !hasHomework) {
                    html += '<p style="font-size:0.82rem;color:var(--color-text-muted);">' + HW_NONE_MSG + '</p>';
                } else {
                    periods.forEach(period => {
                        const key = String(period);
                        const periodHomework = dayHw[key] || [];
                        html += `<div style="margin-bottom:0.65rem;">
                            ${editable ? `<div style="font-size:0.78rem;color:var(--color-text-muted);margin-bottom:0.25rem;">${i18nFormat(TT_PERIOD_LABEL, {period: period})}</div>` : ''}`;

                        periodHomework.forEach(hw => {
                            html += `<div class="tt-hw-item">
                                <button class="tt-hw-delete" onclick="deleteHomework('${escapeHtml(hw.id)}')" title="${HW_DELETE_TITLE}">✕</button>
                                <span>${escapeHtml(hw.title)}</span>
                            </div>`;
                        });

                        if (editable) {
                            html += `<div class="tt-hw-input-row">
                                <input class="tt-hw-input" type="text" id="hwInput_${day}_${period}" placeholder="${HW_PLACEHOLDER}"
                                       onkeydown="if(event.key==='Enter') addHomework('${day}',${period})">
                                <button class="tt-hw-add-btn" onclick="addHomework('${day}',${period})">+</button>
                            </div>`;
                        }

                        html += '</div>';
                    });
                }
                html += '</div>';
            });
            html += '</div>';
            grid.innerHTML = html;
        }

        async function addHomework(day, period) {
            const input = document.getElementById(`hwInput_${day}_${period}`);
            if (!input || !input.value.trim()) return;

            try {
                const res = await fetch('homework/homework_add.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        day,
                        period,
                        title: input.value.trim()
                    })
                });
                if (!res.ok) throw new Error();

                input.value = '';
                await loadHomeworkData();
            } catch (err) {
                console.error('Hausaufgabe konnte nicht gespeichert werden', err);
            }
        }

        async function deleteHomework(homeworkId) {
            if (!homeworkId) return;

            try {
                const res = await fetch(`homework/homework_delete.php?homework_id=${encodeURIComponent(homeworkId)}`, {
                    method: 'POST'
                });
                if (!res.ok) throw new Error();

                await loadHomeworkData();
            } catch (err) {
                console.error('Hausaufgabe konnte nicht gelöscht werden', err);
            }
        }

        // ===== KLASSENARBEITEN =====
        function updateExamPeriodRangeOptions() {
            const periodEl = document.getElementById('examPeriod');
            const periodEndEl = document.getElementById('examPeriodEnd');
            if (!periodEl || !periodEndEl) return;

            const start = parseInt(periodEl.value, 10);
            if (!Number.isInteger(start) || start < 1 || start > 10) {
                periodEndEl.innerHTML = '<option value="">bis zur ...</option>';
                periodEndEl.value = '';
                periodEndEl.style.display = 'none';
                return;
            }

            let options = '<option value="">bis zur ...</option>';
            for (let p = start; p <= 10; p++) {
                options += `<option value="${p}">${p}. Stunde</option>`;
            }
            periodEndEl.innerHTML = options;
            periodEndEl.value = '';
            periodEndEl.style.display = '';
        }

        async function addExam() {
            const subjectEl = document.getElementById('examSubject');
            const dateEl    = document.getElementById('examDate');
            const topicEl   = document.getElementById('examTopic');
            const periodEl  = document.getElementById('examPeriod');
            const periodEndEl = document.getElementById('examPeriodEnd');

            if (!subjectEl.value.trim() || !dateEl.value) return;

            const period = periodEl && periodEl.value ? parseInt(periodEl.value, 10) : null;
            const periodEndRaw = periodEndEl && periodEndEl.value ? parseInt(periodEndEl.value, 10) : null;
            const periodEnd = period && periodEndRaw && periodEndRaw >= period ? periodEndRaw : null;

            try {
                const res = await fetch('exams/exam_add.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        subject: subjectEl.value.trim(),
                        date: dateEl.value,
                        topic: topicEl ? topicEl.value.trim() : '',
                        period: period,
                        period_end: periodEnd
                    })
                });
                if (!res.ok) throw new Error();

                subjectEl.value = '';
                dateEl.value    = '';
                if (topicEl)  topicEl.value  = '';
                if (periodEl) periodEl.value = '';
                updateExamPeriodRangeOptions();

                await loadExamsData();
            } catch (err) {
                console.error('Klassenarbeit konnte nicht gespeichert werden', err);
            }
        }

        function formatExamPeriod(exam) {
            const start = parseInt(exam.period, 10);
            if (!Number.isInteger(start) || start < 1 || start > 10) return '';

            let end = parseInt(exam.end_period, 10);
            if (!Number.isInteger(end) || end < start) end = start;
            end = Math.min(end, 10);

            return end > start
                ? ` · ${start}. bis ${end}. Stunde`
                : ` · ${start}. Stunde`;
        }

        async function deleteExam(examId) {
            if (!examId) return;

            try {
                const res = await fetch(`exams/exam_delete.php?exam_id=${encodeURIComponent(examId)}`, {
                    method: 'POST'
                });
                if (!res.ok) throw new Error();

                await loadExamsData();
            } catch (err) {
                console.error('Klassenarbeit konnte nicht gelöscht werden', err);
            }
        }

        function openExamGradeModal(examId, subject) {
            const modal = document.getElementById('examGradeModal');
            const examIdInput = document.getElementById('examGradeExamId');
            const subjectInput = document.getElementById('examGradeSubject');
            const valueInput = document.getElementById('examGradeValue');
            const weightInput = document.getElementById('examGradeWeight');
            const descInput = document.getElementById('examGradeDescription');
            const titleEl = document.getElementById('examGradeTitle');
            
            if (!modal || !examIdInput || !subjectInput || !valueInput) return;
            
            examIdInput.value = examId;
            subjectInput.value = subject;
            valueInput.value = '';
            weightInput.value = '1';
            if (descInput) descInput.value = '';
            titleEl.textContent = `Note eintragen: ${subject}`;
            
            // Find existing grade if any
            const exam = exams.find(e => e.id === examId);
            if (exam && exam.grade !== null && exam.grade !== undefined) {
                valueInput.value = exam.grade;
            }
            
            modal.classList.add('open');
            valueInput.focus();
        }

        function closeExamGradeModal() {
            const modal = document.getElementById('examGradeModal');
            if (modal) modal.classList.remove('open');
        }

        async function setExamGrade() {
            const examIdInput = document.getElementById('examGradeExamId');
            const subjectInput = document.getElementById('examGradeSubject');
            const valueInput = document.getElementById('examGradeValue');
            const weightInput = document.getElementById('examGradeWeight');
            const descInput = document.getElementById('examGradeDescription');
            
            if (!examIdInput.value || !subjectInput.value || !valueInput.value) {
                showToast('Bitte Fach und Punkte ausfüllen', 'warning');
                return;
            }

            const value = parseFloat(valueInput.value);
            const weight = parseFloat(weightInput.value);
            if (isNaN(value) || value < 0 || value > 15) {
                showToast('Punkte müssen zwischen 0 und 15 liegen', 'warning');
                return;
            }
            if (isNaN(weight) || weight <= 0) {
                showToast('Gewichtung muss > 0 sein', 'warning');
                return;
            }

            try {
                const res = await fetch('exams/exam_grade_set.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        exam_id: examIdInput.value,
                        subject: subjectInput.value.trim(),
                        value: value,
                        weight: weight,
                        description: descInput ? descInput.value.trim() : ''
                    })
                });
                if (!res.ok) throw new Error();

                closeExamGradeModal();
                await loadExamsData();
                // Reload grades tab to show new grade
                if (typeof loadGrades === 'function') loadGrades();
            } catch (err) {
                console.error('Note konnte nicht gespeichert werden', err);
                showToast('Fehler beim Speichern der Note', 'error');
            }
        }

        function renderExams() {
            const list = document.getElementById('examsList');
            if (!list) return;
            if (!exams.length) {
                list.innerHTML = `<div class="empty-state">
                    <div class="empty-state-icon">📝</div>
                    <div class="empty-state-title">Keine Klassenarbeiten</div>
                    <div class="empty-state-text">Trage oben deine nächste Klassenarbeit ein.</div>
                </div>`;
                return;
            }
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const sorted = [...exams]
                .sort((a, b) => new Date(a.date) - new Date(b.date));

            list.innerHTML = sorted.map(exam => {
                const d       = new Date(exam.date + 'T00:00:00');
                const dateStr = d.toLocaleDateString('de-DE', { weekday: 'short', day: '2-digit', month: '2-digit', year: 'numeric' });
                const isPast  = d < today;
                const period  = formatExamPeriod(exam);
                const topic   = exam.topic  ? ` · ${escapeHtml(exam.topic)}` : '';
                const gradeDisplay = exam.grade !== null && exam.grade !== undefined 
                    ? `<span style="font-size:0.9rem;color:var(--color-success);font-weight:600;">${exam.grade}</span>`
                    : '';
                const badge   = isPast
                    ? '<span style="font-size:0.8rem;color:var(--color-text-muted);">vergangen</span>'
                    : '<span style="font-size:0.8rem;color:var(--color-warning);">⏳ bald</span>';
                const gradeButton = isPast
                    ? `<button class="btn-icon" onclick="openExamGradeModal('${escapeHtml(exam.id)}', '${escapeHtml(exam.subject)}')" title="Note eintragen">📝</button>`
                    : '';

                return `<div class="grade-item ${isPast ? 'exam-past' : ''}">
                    <div>
                        <div class="grade-subject">${escapeHtml(exam.subject)}</div>
                        <div style="font-size:0.8rem;color:var(--color-text-muted);">${dateStr}${period}${topic}</div>
                    </div>
                    <div style="display:flex;align-items:center;gap:0.5rem;">
                        ${gradeDisplay}
                        ${badge}
                        ${gradeButton}
                        <button class="btn-icon" onclick="deleteExam('${escapeHtml(exam.id)}')" title="Löschen">🗑️</button>
                    </div>
                </div>`;
            }).join('');
        }

        // ===== CALENDAR (Monatsansicht) =====
        let currentCalMonth;
        let currentCalYear;
        let currentSelectedDate = null; // für Anzeige der Tagesereignisse
        let pendingCalendarDelete = null;
        const DEFAULT_CALENDAR_EVENT_COLOR = '#0d6efd';
        const CALENDAR_TITLE_COLORS = ['#e11d48', '#f97316', '#ca8a04', '#65a30d', '#0f766e', '#0284c7', '#1d4ed8', '#7c3aed', '#c026d3', '#db2777', '#dc2626', '#0891b2', '#4f46e5', '#059669', '#d97706', '#4338ca'];

        function toDateStr(dateObj) {
            return `${dateObj.getFullYear()}-${String(dateObj.getMonth() + 1).padStart(2,'0')}-${String(dateObj.getDate()).padStart(2,'0')}`;
        }

        function normalizeCalendarTitle(title) {
            return String(title || '').trim().toLocaleLowerCase('de-DE');
        }

        function normalizeHexColor(colorValue, fallback = DEFAULT_CALENDAR_EVENT_COLOR) {
            const color = String(colorValue || '').trim();
            return /^#[0-9a-fA-F]{6}$/.test(color) ? color.toLowerCase() : fallback;
        }

        function normalizeTimeValue(timeValue, fallback = '') {
            const time = String(timeValue || '').trim();
            return /^([01]\d|2[0-3]):[0-5]\d$/.test(time) ? time : fallback;
        }

        function getCalendarEventTimeLabel(eventItem) {
            const startTime = normalizeTimeValue(eventItem?.start_time);
            const endTime = normalizeTimeValue(eventItem?.end_time);
            if (!startTime || !endTime || endTime <= startTime) return '';
            return `${startTime} - ${endTime}`;
        }

        function getUniqueCalendarTitleEntries(titles) {
            const titleMap = new Map();

            titles.forEach(title => {
                const displayTitle = String(title || '').trim();
                const normalizedTitle = normalizeCalendarTitle(displayTitle);
                if (!normalizedTitle) return;

                const existing = titleMap.get(normalizedTitle);
                if (existing) {
                    existing.count += 1;
                    return;
                }

                titleMap.set(normalizedTitle, {
                    normalizedTitle,
                    displayTitle,
                    count: 1
                });
            });

            return Array.from(titleMap.values()).sort((a, b) => {
                if (b.count !== a.count) return b.count - a.count;
                return a.displayTitle.localeCompare(b.displayTitle, 'de');
            });
        }

        function generateCalendarColorFallback(index, total) {
            const hue = Math.round((index * 360) / Math.max(total, 1));
            const saturation = 72 - ((index % 3) * 6);
            const lightness = 50 + ((index % 2) * 6);
            return `hsl(${hue} ${saturation}% ${lightness}%)`;
        }

        function getCalendarTitleColorMap() {
            const titles = getUniqueCalendarTitleEntries([
                ...calendarExtras.map(item => item?.title),
                ...exams.map(item => item?.subject),
                ...(Array.isArray(todosData) ? todosData.map(item => item?.title) : [])
            ]);
            const colorMap = new Map();

            titles.forEach((entry, index) => {
                const color = CALENDAR_TITLE_COLORS[index] || generateCalendarColorFallback(index, titles.length);
                colorMap.set(entry.normalizedTitle, color);
            });

            return colorMap;
        }

        function getCalendarTitleColor(title) {
            const normalizedTitle = normalizeCalendarTitle(title);
            if (!normalizedTitle) return 'var(--color-primary)';

            return getCalendarTitleColorMap().get(normalizedTitle) || 'var(--color-primary)';
        }

        function getCalendarItemColor(item) {
            if (item?.type === 'holiday') return '#b91c1c';
            if (item?.type === 'extra') return normalizeHexColor(item?.color);
            return getCalendarTitleColor(item?.title || '');
        }

        function getStoredCalendarTitles() {
            return getUniqueCalendarTitleEntries(calendarExtras.map(ev => ev?.title)).map(entry => entry.displayTitle);
        }

        function getCalendarRecurrenceLabel(recurrence) {
            if (recurrence === 'weekly') return CAL_REC_WEEKLY;
            if (recurrence === 'monthly') return CAL_REC_MONTHLY;
            if (recurrence === 'yearly') return CAL_REC_YEARLY;
            return '';
        }

        function updateCalendarTitlePreview(title) {
            const preview = document.getElementById('calendarTitleColorPreview');
            if (!preview) return;
            const colorInput = document.getElementById('quickEventColor');
            const selectedColor = normalizeHexColor(colorInput?.value);
            preview.style.setProperty('--event-dot-color', selectedColor || getCalendarTitleColor(title));
        }

        function updateCalendarTitleSuggestions(filterText = '') {
            const container = document.getElementById('calendarTitleSuggestions');
            if (!container) return;

            const normalizedFilter = normalizeCalendarTitle(filterText);
            const exactMatch = normalizedFilter && getStoredCalendarTitles().some(title => normalizeCalendarTitle(title) === normalizedFilter);
            const matchingTitles = getStoredCalendarTitles().filter(title => {
                if (!normalizedFilter) return true;
                return normalizeCalendarTitle(title).includes(normalizedFilter);
            });

            updateCalendarTitlePreview(filterText);

            if (!matchingTitles.length || exactMatch) {
                container.innerHTML = '';
                return;
            }

            container.innerHTML = matchingTitles.slice(0, 8).map(title => {
                const normalizedTitle = normalizeCalendarTitle(title);
                const isActive = normalizedFilter && normalizedTitle === normalizedFilter;
                return `<button type="button" class="calendar-title-suggestion${isActive ? ' active' : ''}" style="--event-dot-color:${getCalendarTitleColor(title)}" data-title="${escapeHtml(title)}" onclick="selectCalendarTitleSuggestion(this.dataset.title)"><span class="calendar-title-suggestion-dot"></span><span>${escapeHtml(title)}</span></button>`;
            }).join('');
        }

        function selectCalendarTitleSuggestion(title) {
            const titleEl = document.getElementById('quickEventTitle');
            if (!titleEl) return;

            titleEl.value = title;
            updateCalendarTitleSuggestions(title);
            titleEl.focus();
            titleEl.setSelectionRange(title.length, title.length);
        }

        function renderCalendarDots(events) {
            const uniqueItems = [];
            const seenKeys = new Set();

            events.forEach(event => {
                const normalizedTitle = normalizeCalendarTitle(event.title);
                const uniqueKey = `${event.type || 'default'}:${normalizedTitle}`;
                if (!normalizedTitle || seenKeys.has(uniqueKey)) return;
                seenKeys.add(uniqueKey);
                uniqueItems.push(event);
            });

            if (!uniqueItems.length) return '';

            return `<div class="event-dots">${uniqueItems.slice(0, 4).map(event => `<span class="event-dot" style="--event-dot-color:${getCalendarItemColor(event)}"></span>`).join('')}</div>`;
        }

        function getMonthPrefix(year, month) {
            return `${year}-${String(month + 1).padStart(2, '0')}-`;
        }

        function getVisibleDefaultDate() {
            const today = new Date();
            if (today.getFullYear() === currentCalYear && today.getMonth() === currentCalMonth) {
                return toDateStr(today);
            }
            return `${currentCalYear}-${String(currentCalMonth + 1).padStart(2, '0')}-01`;
        }

        function initCalendar() {
            const today = new Date();
            currentCalMonth = today.getMonth();
            currentCalYear = today.getFullYear();
            currentSelectedDate = toDateStr(today);
            renderCalendar();
            showEventsForDate(currentSelectedDate, false);
        }

        function renderCalendar() {
            const container = document.getElementById('calendarGrid');
            const label = document.getElementById('calendarMonthLabel');
            if (!container || !label) return;

            // Monat/Jahr anzeigen
            label.textContent = CAL_MONTH_NAMES[currentCalMonth] + ' ' + currentCalYear;

            // erster Wochentag des Monats (Montag = 0, Sonntag = 6)
            const firstDayJs = new Date(currentCalYear, currentCalMonth, 1).getDay(); // 0=Sonntag
            const firstDay = (firstDayJs + 6) % 7;
            const daysInMonth = new Date(currentCalYear, currentCalMonth + 1, 0).getDate();

            // grid aufbauen
            let html = '<tr>';
            CAL_DAY_SHORT.forEach(d => html += '<th>'+d+'</th>');
            html += '</tr>';

            let day = 1;
            const todayStr = toDateStr(new Date());
            const monthStart = `${currentCalYear}-${String(currentCalMonth + 1).padStart(2,'0')}-01`;
            const monthEnd = `${currentCalYear}-${String(currentCalMonth + 1).padStart(2,'0')}-${String(daysInMonth).padStart(2,'0')}`;
            const monthItems = getAllCalendarItems(monthStart, monthEnd);
            for (let week = 0; week < 6; week++) {
                html += '<tr>';
                for (let w = 0; w < 7; w++) {
                    const cellIndex = week * 7 + w;
                    const isBlank = week === 0 && w < firstDay;
                    let content = '';
                    let dateStr = '';
                    if (!isBlank && day <= daysInMonth) {
                        const d = day;
                        dateStr = `${currentCalYear}-${String(currentCalMonth+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                        content = `<div class="cal-day-number">${d}</div>`;
                        // event indicator
                        const events = monthItems.filter(ev => ev.date === dateStr);
                        if (events.length) {
                            content += renderCalendarDots(events);
                        }
                        day++;
                    }
                    const classes = [];
                    if (dateStr && dateStr === todayStr) classes.push('cal-today');
                    if (dateStr && dateStr === currentSelectedDate) classes.push('cal-selected');
                    const classAttr = classes.length ? ` class="${classes.join(' ')}"` : '';
                    html += `<td data-date="${dateStr}"${classAttr} onclick="showEventsForDate('${dateStr}')">${content}</td>`;
                }
                html += '</tr>';
            }
            container.innerHTML = html;
        }

        function showEventsForDate(dateStr, refreshCalendar = true) {
            const listEl = document.getElementById('calendarEventList');
            const label = document.getElementById('calendarDayLabel');
            const wrapper = document.getElementById('calendarDayEvents');
            const selectedHint = document.getElementById('calendarSelectedHint');
            if (!listEl || !label || !wrapper) return;
            currentSelectedDate = dateStr;
            if (refreshCalendar) {
                renderCalendar();
            }
            if (!dateStr) {
                label.textContent = CAL_NO_DAY_SELECTED;
                listEl.innerHTML = '<p style="color:var(--color-text-muted)">' + CAL_SELECT_A_DAY + '</p>';
                if (selectedHint) selectedHint.textContent = CAL_SELECTED_PREFIX + CAL_NO_DAY;
                wrapper.style.display = 'block';
                return;
            }
            const d = new Date(dateStr + 'T00:00:00');
            const selectedText = d.toLocaleDateString(CAL_LOCALE, { weekday: 'long', day: '2-digit', month:'2-digit', year:'numeric' });
            label.textContent = selectedText;
            if (selectedHint) selectedHint.textContent = CAL_SELECTED_PREFIX + selectedText;
            const events = getAllCalendarItems(dateStr, dateStr).filter(ev => ev.date === dateStr);
            if (!events.length) {
                listEl.innerHTML = '<p style="color:var(--color-text-muted)">' + CAL_NO_EVENTS + '</p>';
            } else {
                listEl.innerHTML = events.map(ev => {
                    const icon = ev.type === 'exam' ? '📝' : ev.type === 'todo' ? '✅' : ev.type === 'holiday' ? '🎉' : '📌';
                    let deleteBtn = '';
                    if (ev.type === 'extra') {
                        if (ev.id) {
                            deleteBtn = `<button class="btn-icon" onclick="deleteCalendarEvent('${ev.id}', '${ev.date}', '${ev.recurrence || 'none'}')" title="${ev.recurrence && ev.recurrence !== 'none' ? CAL_DELETE_OR_SERIES : HW_DELETE_TITLE}">🗑️</button>`;
                        }
                    }
                    const recurringLabel = getCalendarRecurrenceLabel(ev.recurrence);
                    const timeLabel = getCalendarEventTimeLabel(ev);
                    const timeText = timeLabel ? ` · ${timeLabel}` : '';
                    const recurringText = recurringLabel ? ` · ${recurringLabel}` : '';
                    return `<div class="calendar-event-item ${ev.type}"><div class="calendar-event-content"><span class="calendar-event-dot" style="--event-dot-color:${getCalendarItemColor(ev)}"></span><span>${icon}</span><span class="calendar-event-text"><strong>${escapeHtml(ev.title)}</strong>${timeText}${recurringText}${ev.description ? ' – ' + escapeHtml(ev.description) : ''}</span></div>${deleteBtn}</div>`;
                }).join('');
            }
            wrapper.style.display = 'block';
        }

        function prevMonth() {
            currentCalMonth--;
            if (currentCalMonth < 0) {
                currentCalMonth = 11;
                currentCalYear--;
            }

            renderCalendar();
            showEventsForDate(currentSelectedDate, false);
        }
        function nextMonth() {
            currentCalMonth++;
            if (currentCalMonth > 11) {
                currentCalMonth = 0;
                currentCalYear++;
            }

            renderCalendar();
            showEventsForDate(currentSelectedDate, false);
        }

        async function deleteCalendarEvent(eventId, occurrenceDate = '', recurrence = 'none') {
            if (!eventId) return;

            if (recurrence && recurrence !== 'none') {
                pendingCalendarDelete = { eventId, occurrenceDate, recurrence };
                openCalendarDeleteModal();
                return;
            }

            await performCalendarDelete(eventId, 'series', occurrenceDate);
        }

        function openCalendarDeleteModal() {
            const modal = document.getElementById('calendarDeleteModal');
            const textEl = document.getElementById('calendarDeleteModalText');
            if (!modal || !pendingCalendarDelete) return;

            const recurringLabel = getCalendarRecurrenceLabel(pendingCalendarDelete.recurrence);
            if (textEl) {
                textEl.textContent = recurringLabel
                    ? i18nFormat(CAL_REC_DELETE_CONFIRM, { label: recurringLabel })
                    : <?php echo json_encode(t('calendar.delete_recurring_text')); ?>;
            }

            modal.classList.add('open');
        }

        function closeCalendarDeleteModal() {
            const modal = document.getElementById('calendarDeleteModal');
            if (!modal) return;
            modal.classList.remove('open');
            pendingCalendarDelete = null;
        }

        async function confirmCalendarDelete(deleteScope) {
            if (!pendingCalendarDelete) return;

            const { eventId, occurrenceDate } = pendingCalendarDelete;
            closeCalendarDeleteModal();
            await performCalendarDelete(eventId, deleteScope, occurrenceDate);
        }

        async function performCalendarDelete(eventId, deleteScope = 'series', occurrenceDate = '') {
            if (!eventId) return;

            const params = new URLSearchParams({
                event_id: eventId,
                delete_scope: deleteScope || 'series'
            });
            if (occurrenceDate) {
                params.set('occurrence_date', occurrenceDate);
            }

            try {
                const res = await fetch(`calendar/calendar_delete.php?${params.toString()}`, {
                    method: 'POST'
                });
                if (!res.ok) throw new Error();

                await loadCalendarExtras();
            } catch (err) {
                console.error('Kalendereintrag konnte nicht gelöscht werden', err);
            }
        }

        // ===== ÜBERSICHT VORSCHAUEN =====
        const IS_ADMIN = <?php echo !empty($is_admin) ? 'true' : 'false'; ?>;
        let adminStatsCache = null;
        let adminMessagesCache = [];
        let adminMessageManagementCache = { messages: [], users: [] };
        let adminUsersCache = [];
        let adminUsersSearchTerm = '';

        function setAdminText(id, value) {
            const el = document.getElementById(id);
            if (!el) return;
            el.textContent = value;
        }

        function formatBytes(bytes) {
            const b = Number(bytes || 0);
            if (b < 1024) return `${b} B`;
            if (b < 1024 * 1024) return `${(b / 1024).toFixed(1)} KB`;
            if (b < 1024 * 1024 * 1024) return `${(b / (1024 * 1024)).toFixed(1)} MB`;
            return `${(b / (1024 * 1024 * 1024)).toFixed(1)} GB`;
        }

        function buildSimpleList(items, formatter, emptyText) {
            if (!Array.isArray(items) || !items.length) return `<p style="color:var(--color-text-muted);">${emptyText}</p>`;
            return items.map(formatter).join('');
        }

        function setAdminMessageStatus(message, type = 'info') {
            const el = document.getElementById('adminMessageStatus');
            if (!el) return;
            el.textContent = message || '';

            if (type === 'success') {
                el.style.color = 'var(--color-success)';
            } else if (type === 'error') {
                el.style.color = 'var(--color-danger)';
            } else {
                el.style.color = 'var(--color-text-muted)';
            }
        }

        function setAdminUsersStatus(message, type = 'info') {
            const el = document.getElementById('adminUsersStatus');
            if (!el) return;
            el.textContent = message || '';

            if (type === 'success') {
                el.style.color = 'var(--color-success)';
            } else if (type === 'error') {
                el.style.color = 'var(--color-danger)';
            } else {
                el.style.color = 'var(--color-text-muted)';
            }
        }

        function formatAdminMessageDate(value) {
            if (!value) return '-';
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return String(value);
            return date.toLocaleString('de-DE', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function getAdminMessageRecipientLabel(message) {
            if (!message || message.is_broadcast || !message.recipient_user_id) return 'Alle Nutzer';
            return message.recipient_username || 'Einzelner Nutzer';
        }

        function buildAdminMessageMarkup(message, options = {}) {
            const showDelete = Boolean(options.showDelete);
            const showRecipient = Boolean(options.showRecipient);
            const recipientLabel = getAdminMessageRecipientLabel(message);
            const metaParts = [
                `von ${escapeHtml(message.sender_username || 'Admin')}`,
                escapeHtml(formatAdminMessageDate(message.created_at))
            ];

            if (showRecipient) {
                metaParts.push(`an ${escapeHtml(recipientLabel)}`);
            }

            return `
                <div class="message-item">
                    <div class="message-header">
                        <div>
                            <div class="message-title">${escapeHtml(message.title || '')}</div>
                            <div class="message-meta">${metaParts.join(' · ')}</div>
                        </div>
                        <div class="message-actions">
                            <span class="message-badge">${escapeHtml(recipientLabel)}</span>
                            ${showDelete ? `<button class="btn-secondary" style="padding:0.45rem 0.8rem;color:var(--color-danger);border-color:var(--color-danger);" onclick="deleteAdminMessage('${escapeHtml(message.id)}')">Löschen</button>` : ''}
                        </div>
                    </div>
                    <div class="message-text">${escapeHtml(message.body || '')}</div>
                </div>
            `;
        }

        function renderAdminMessagesList(messages) {
            const container = document.getElementById('adminMessagesList');
            if (!container) return;

            if (!Array.isArray(messages) || !messages.length) {
                container.innerHTML = '<p class="message-empty">Keine Nachrichten vorhanden</p>';
                renderOverviewMessages();
                return;
            }

            container.innerHTML = messages.map(message => buildAdminMessageMarkup(message)).join('');
            renderOverviewMessages();
        }

        function renderAdminSentMessages(messages) {
            const container = document.getElementById('adminSentMessages');
            if (!container) return;

            if (!Array.isArray(messages) || !messages.length) {
                container.innerHTML = '<p class="message-empty">Noch keine Nachrichten gesendet</p>';
                return;
            }

            container.innerHTML = messages.map(message => buildAdminMessageMarkup(message, {
                showDelete: true,
                showRecipient: true
            })).join('');
        }

        function populateAdminMessageRecipients(users) {
            const select = document.getElementById('adminMessageRecipient');
            if (!select) return;

            const currentValue = select.value;
            const options = ['<option value="">Alle Nutzer</option>'];

            (Array.isArray(users) ? users : []).forEach(user => {
                const roleSuffix = user.role && String(user.role).toLowerCase() === 'admin' ? ' (Admin)' : '';
                options.push(`<option value="${escapeHtml(user.id)}">${escapeHtml(user.username || 'Unbekannt')}${roleSuffix}</option>`);
            });

            select.innerHTML = options.join('');
            select.value = currentValue;
            if (select.value !== currentValue) {
                select.value = '';
            }
        }

        function renderAdminUsers(users) {
            const container = document.getElementById('adminUsersList');
            if (!container) return;

            const safeUsers = Array.isArray(users) ? users : [];
            if (!safeUsers.length) {
                const emptyText = adminUsersSearchTerm
                    ? 'Keine passenden Nutzer gefunden'
                    : 'Keine Nutzer gefunden';
                container.innerHTML = `<p class="message-empty">${escapeHtml(emptyText)}</p>`;
                return;
            }

            const adminCount = safeUsers.filter(user => String(user.role || '').toLowerCase() === 'admin').length;

            container.innerHTML = safeUsers.map(user => {
                const username = escapeHtml(user.username || 'Unbekannt');
                const email = escapeHtml(user.email || '-');
                const createdAt = escapeHtml(formatAdminMessageDate(user.created_at));
                const role = String(user.role || 'user').toLowerCase() === 'admin' ? 'admin' : 'user';
                const isAdminRole = role === 'admin';
                const actionRole = isAdminRole ? 'user' : 'admin';
                const actionLabel = isAdminRole ? 'Admin entziehen' : 'Zum Admin machen';
                const roleBadgeStyle = isAdminRole
                    ? 'background:rgba(16,185,129,0.18);color:var(--color-success);'
                    : 'background:rgba(148,163,184,0.2);color:var(--color-text-secondary);';
                const disableDemoteLastAdmin = isAdminRole && adminCount <= 1;
                const disabledAttr = disableDemoteLastAdmin ? 'disabled' : '';
                const disabledStyle = disableDemoteLastAdmin ? 'opacity:0.55;cursor:not-allowed;' : '';
                const hint = disableDemoteLastAdmin
                    ? '<div class="admin-user-hint">Mindestens ein Admin muss bestehen bleiben.</div>'
                    : '';

                return `
                    <div class="message-item">
                        <div class="admin-user-row">
                            <div class="admin-user-main">
                                <div class="message-title admin-user-title">${username}</div>
                                <div class="message-meta admin-user-meta">${email} · Erstellt am ${createdAt}</div>
                                ${hint}
                            </div>
                            <div class="admin-user-actions">
                                <span class="message-badge" style="${roleBadgeStyle}">${isAdminRole ? 'Admin' : 'Nutzer'}</span>
                                <button class="btn-secondary" style="padding:0.45rem 0.8rem;${disabledStyle}" ${disabledAttr} onclick="changeAdminRole('${escapeHtml(user.id)}','${actionRole}')">${actionLabel}</button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function getFilteredAdminUsers() {
            const term = String(adminUsersSearchTerm || '').trim().toLowerCase();
            if (!term) return Array.isArray(adminUsersCache) ? adminUsersCache : [];

            return (Array.isArray(adminUsersCache) ? adminUsersCache : []).filter(user => {
                const role = String(user.role || 'user').toLowerCase() === 'admin' ? 'admin' : 'nutzer';
                const haystack = [
                    String(user.username || ''),
                    String(user.email || ''),
                    role
                ].join(' ').toLowerCase();
                return haystack.includes(term);
            });
        }

        function filterAdminUsers() {
            const input = document.getElementById('adminUsersSearch');
            adminUsersSearchTerm = input ? String(input.value || '').trim() : '';
            renderAdminUsers(getFilteredAdminUsers());
        }

        async function loadAdminMessages() {
            const container = document.getElementById('adminMessagesList');
            if (container) {
                container.innerHTML = '<p class="message-empty">Lädt…</p>';
            }

            try {
                const res = await fetch('admin/messages_load.php');
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.error || data.detail || 'Admin-Nachrichten konnten nicht geladen werden');
                }

                adminMessagesCache = Array.isArray(data) ? data : [];
                renderAdminMessagesList(adminMessagesCache);
            } catch (err) {
                console.error('Admin-Nachrichten konnten nicht geladen werden', err);
                if (container) {
                    container.innerHTML = `<p class="message-empty">${escapeHtml(err.message || 'Admin-Nachrichten konnten nicht geladen werden')}</p>`;
                }
                renderOverviewMessages();
            }
        }

        async function loadAdminMessageManagement() {
            if (!IS_ADMIN) return;

            const container = document.getElementById('adminSentMessages');
            if (container) {
                container.innerHTML = '<p class="message-empty">Lädt…</p>';
            }

            try {
                const res = await fetch('admin/messages_manage.php');
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.error || data.detail || 'Admin-Nachrichtenverwaltung konnte nicht geladen werden');
                }

                adminMessageManagementCache = {
                    messages: Array.isArray(data.messages) ? data.messages : [],
                    users: Array.isArray(data.users) ? data.users : []
                };

                populateAdminMessageRecipients(adminMessageManagementCache.users);
                renderAdminSentMessages(adminMessageManagementCache.messages);
            } catch (err) {
                console.error('Admin-Nachrichtenverwaltung konnte nicht geladen werden', err);
                if (container) {
                    container.innerHTML = `<p class="message-empty">${escapeHtml(err.message || 'Admin-Nachrichtenverwaltung konnte nicht geladen werden')}</p>`;
                }
                setAdminMessageStatus(err.message || 'Admin-Nachrichtenverwaltung konnte nicht geladen werden', 'error');
            }
        }

        async function loadAdminUsers() {
            if (!IS_ADMIN) return;

            const container = document.getElementById('adminUsersList');
            if (container) {
                container.innerHTML = '<p class="message-empty">Lädt…</p>';
            }

            try {
                const res = await fetch('admin/users_manage.php');
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.error || data.detail || 'Benutzerverwaltung konnte nicht geladen werden');
                }

                adminUsersCache = Array.isArray(data.users) ? data.users : [];
                renderAdminUsers(getFilteredAdminUsers());
                setAdminUsersStatus('');
            } catch (err) {
                console.error('Benutzerverwaltung konnte nicht geladen werden', err);
                if (container) {
                    container.innerHTML = `<p class="message-empty">${escapeHtml(err.message || 'Benutzerverwaltung konnte nicht geladen werden')}</p>`;
                }
                setAdminUsersStatus(err.message || 'Benutzerverwaltung konnte nicht geladen werden', 'error');
            }
        }

        async function changeAdminRole(targetUserId, role) {
            if (!IS_ADMIN || !targetUserId) return;
            const normalizedRole = String(role || '').toLowerCase() === 'admin' ? 'admin' : 'user';

            setAdminUsersStatus('Rolle wird aktualisiert…');

            try {
                const res = await fetch('admin/user_role_update.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        user_id: targetUserId,
                        role: normalizedRole
                    })
                });
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.error || data.detail || 'Rolle konnte nicht aktualisiert werden');
                }

                setAdminUsersStatus('Rolle erfolgreich aktualisiert.', 'success');
                await Promise.all([loadAdminUsers(), loadAdminMessageManagement()]);
            } catch (err) {
                console.error('Rolle konnte nicht aktualisiert werden', err);
                setAdminUsersStatus(err.message || 'Rolle konnte nicht aktualisiert werden', 'error');
            }
        }

        function renderOverviewAdmin() {
            if (!IS_ADMIN || !adminStatsCache) return;

            const overview = adminStatsCache.overview || {};
            const learning = adminStatsCache.learning || {};

            setAdminText('overviewAdminMau', String(overview.mau ?? '-'));
            setAdminText('overviewAdminUsers', String(overview.total_users ?? '-'));
            setAdminText('overviewAdminTodos', `${Number(learning.todo_completion_rate || 0).toFixed(1)}%`);
            setAdminText('overviewAdminFailures', String(overview.failed_logins_7d ?? '-'));
        }

        async function loadAdminPanel() {
            if (!IS_ADMIN) return;

            try {
                const res = await fetch('admin/admin_stats.php');
                const data = await res.json();
                if (!res.ok) {
                    const message = data.error || data.detail || 'Fehler beim Laden der Admin-Statistiken';
                    setAdminText('adminContentSummary', message);
                    return;
                }

                adminStatsCache = data;

                const overview = data.overview || {};
                const learning = data.learning || {};
                const content = data.content || {};
                const topLists = data.top_lists || {};
                const trends = data.trends || {};

                setAdminText('adminTotalUsers', String(overview.total_users ?? 0));
                setAdminText('adminNewUsers30d', String(overview.new_users_30d ?? 0));
                setAdminText('adminDauWauMau', `${overview.dau ?? 0} / ${overview.wau ?? 0} / ${overview.mau ?? 0}`);
                setAdminText('adminTodoRate', `${Number(learning.todo_completion_rate || 0).toFixed(1)}%`);
                setAdminText('adminAverageGrade', Number(learning.average_grade || 0).toFixed(2));
                setAdminText('adminFailedLogins7d', String(overview.failed_logins_7d ?? 0));

                setAdminText(
                    'adminContentSummary',
                    `Dateien: ${content.total_files ?? 0} (30 Tage: ${content.upload_count_30d ?? 0}, Speicher: ${formatBytes(content.total_upload_size_bytes)}) | Decks: ${learning.total_flashcard_decks ?? 0} | Karten: ${learning.total_flashcards ?? 0}`
                );

                const topUsersHtml = buildSimpleList(
                    topLists.top_active_users,
                    (entry) => `<div>• ${escapeHtml(entry.username)}: ${entry.logins_30d} Logins</div>`,
                    'Keine Daten'
                );
                const openTodosHtml = buildSimpleList(
                    topLists.users_many_open_todos,
                    (entry) => `<div>• ${escapeHtml(entry.username)}: ${entry.open_todos} offene To-Dos</div>`,
                    'Keine offenen To-Dos gefunden'
                );

                const registrations = (trends.registrations_last_7_days || []).reduce((sum, d) => sum + Number(d.count || 0), 0);
                const logins = (trends.logins_last_7_days || []).reduce((sum, d) => sum + Number(d.count || 0), 0);
                setAdminText('adminTrends', `Registrierungen: ${registrations} | Logins: ${logins}`);

                const topUsersEl = document.getElementById('adminTopUsers');
                if (topUsersEl) topUsersEl.innerHTML = topUsersHtml;

                const openTodosEl = document.getElementById('adminOpenTodos');
                if (openTodosEl) openTodosEl.innerHTML = openTodosHtml;

                if (data.generated_at) {
                    const dt = new Date(data.generated_at);
                    setAdminText('adminGeneratedAt', `Zuletzt aktualisiert: ${dt.toLocaleString('de-DE')}`);
                }

                renderOverviewAdmin();
            } catch {
                setAdminText('adminContentSummary', 'Admin-Statistiken konnten nicht geladen werden');
            }
        }

        async function sendAdminMessage() {
            if (!IS_ADMIN) return;

            const titleEl = document.getElementById('adminMessageTitle');
            const recipientEl = document.getElementById('adminMessageRecipient');
            const bodyEl = document.getElementById('adminMessageBody');

            const title = titleEl ? titleEl.value.trim() : '';
            const body = bodyEl ? bodyEl.value.trim() : '';
            const recipient_user_id = recipientEl ? recipientEl.value : '';

            if (!title) {
                setAdminMessageStatus('Bitte einen Titel eingeben.', 'error');
                return;
            }

            if (!body) {
                setAdminMessageStatus('Bitte eine Nachricht eingeben.', 'error');
                return;
            }

            setAdminMessageStatus('Nachricht wird gesendet…');

            try {
                const res = await fetch('admin/message_send.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        title,
                        body,
                        recipient_user_id: recipient_user_id || null
                    })
                });
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.error || data.detail || 'Admin-Nachricht konnte nicht gesendet werden');
                }

                if (titleEl) titleEl.value = '';
                if (bodyEl) bodyEl.value = '';
                if (recipientEl) recipientEl.value = '';

                setAdminMessageStatus('Nachricht erfolgreich gesendet.', 'success');
                await Promise.all([loadAdminMessages(), loadAdminMessageManagement()]);
            } catch (err) {
                console.error('Admin-Nachricht konnte nicht gesendet werden', err);
                setAdminMessageStatus(err.message || 'Admin-Nachricht konnte nicht gesendet werden', 'error');
            }
        }

        async function deleteAdminMessage(messageId) {
            if (!IS_ADMIN || !messageId) return;
            if (!window.confirm('Nachricht wirklich löschen?')) return;

            try {
                const res = await fetch(`admin/message_delete.php?message_id=${encodeURIComponent(messageId)}`);
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.error || data.detail || 'Nachricht konnte nicht gelöscht werden');
                }

                setAdminMessageStatus('Nachricht gelöscht.', 'success');
                await Promise.all([loadAdminMessages(), loadAdminMessageManagement()]);
            } catch (err) {
                console.error('Nachricht konnte nicht gelöscht werden', err);
                setAdminMessageStatus(err.message || 'Nachricht konnte nicht gelöscht werden', 'error');
            }
        }

        function renderOverviewTimetable() {
            const container = document.getElementById('overviewTimetable');
            if (!container) return;
            const todayKey  = getCurrentDayKey();
            const todayData = timetableData[todayKey] || {};
            const entries   = Object.entries(todayData)
                .filter(([, cell]) => cell && cell.subject)
                .sort(([a], [b]) => parseInt(a) - parseInt(b));

            if (!entries.length) {
                container.innerHTML = '<p style="color:var(--color-text-muted);text-align:center;padding:0.5rem;">Heute keine Stunden eingetragen</p>';
                return;
            }

            // helper: determine current period by comparing now to entered times
            // we consider the period current if now is between its start and the next period's start
            function getCurrentPeriod() {
                const now = new Date();
                const hhmm = now.toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit', hour12: false });
                // build sorted list of available periods
                const periods = entries.map(([p]) => parseInt(p)).sort((a,b)=>a-b);
                for (let i = 0; i < periods.length; i++) {
                    const p = periods[i];
                    const start = timetableTimes[p] || '';
                    if (!start) continue;
                    const nextStart = (i+1 < periods.length) ? (timetableTimes[periods[i+1]] || '') : null;
                    if (hhmm >= start && (nextStart === null || hhmm < nextStart)) {
                        return p;
                    }
                }
                return null;
            }

            const curr = getCurrentPeriod();
            let shown = [];
            if (curr !== null) {
                // add current
                const cell = (todayData[curr] || {});
                if (cell && cell.subject) shown.push([curr.toString(), cell]);
                // find next available period in sorted order
                const avail = entries.map(([p])=>parseInt(p)).sort((a,b)=>a-b);
                const idx = avail.indexOf(curr);
                if (idx !== -1 && idx+1 < avail.length) {
                    const np = avail[idx+1];
                    const nextCell = (todayData[np] || {});
                    if (nextCell && nextCell.subject) shown.push([np.toString(), nextCell]);
                }
            }
            if (shown.length === 0) {
                // fallback: just show first two entries of day
                shown = entries.slice(0, 2);
            }

            container.innerHTML = shown.map(([period, cell]) => `
                <div class="timetable-day">
                    <span class="day-name">${escapeHtml(timetableTimes[period] || period + '.')}</span>
                    <div class="day-classes">
                        <span class="class-badge">${escapeHtml(cell.subject)}</span>
                        ${cell.room ? `<span style="font-size:0.75rem;color:var(--color-text-muted);margin-left:0.3rem;">${escapeHtml(cell.room)}</span>` : ''}
                    </div>
                </div>
            `).join('');
        }

        function renderOverviewTodos() {
            const container = document.getElementById('overviewTodos');
            if (!container) return;
            if (todosData === null) {
                container.innerHTML = '<p style="color:var(--color-text-muted);text-align:center;padding:0.5rem;">Lädt…</p>';
                return;
            }
            const open = todosData.filter(t => !t.done).slice(0, 3);
            if (!open.length) {
                container.innerHTML = '<p style="color:var(--color-text-muted);text-align:center;padding:0.5rem;">Keine offenen Aufgaben 🎉</p>';
                return;
            }
            container.innerHTML = open.map(todo => `
                <div class="todo-item">
                    <div class="todo-checkbox" onclick="toggleTodoById('${escapeHtml(todo.id)}')"></div>
                    <div class="todo-text">${escapeHtml(todo.title)}</div>
                    <div class="todo-priority priority-${todo.priority}" title="${getPriorityLabel(todo.priority)}"></div>
                </div>
            `).join('');
        }

        function renderOverviewExams() {
            const container = document.getElementById('overviewExams');
            if (!container) return;
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const upcoming = exams
                .filter(e => new Date(e.date + 'T00:00:00') >= today)
                .sort((a, b) => new Date(a.date) - new Date(b.date))
                .slice(0, 3);
            if (!upcoming.length) {
                container.innerHTML = '<p style="color:var(--color-text-muted);text-align:center;padding:0.5rem;">Keine bevorstehenden Klassenarbeiten</p>';
                return;
            }
            container.innerHTML = upcoming.map(exam => {
                const d       = new Date(exam.date + 'T00:00:00');
                const dateStr = d.toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' });
                return `<div class="grade-item">
                    <div>
                        <div class="grade-subject">${escapeHtml(exam.subject)}</div>
                        <div style="font-size:0.8rem;color:var(--color-text-muted);">${dateStr}${exam.topic ? ' · ' + escapeHtml(exam.topic) : ''}</div>
                    </div>
                    <span style="font-size:0.9rem;color:var(--color-warning);">⏳</span>
                </div>`;
            }).join('');
        }

        function renderOverviewHomeworks() {
            const container = document.getElementById('overviewHomeworks');
            if (!container) return;
            const items = [];
            TT_DAYS.forEach(day => {
                const dayData = homework[day] || {};
                Object.entries(dayData).forEach(([period, list]) => {
                    (list || []).forEach(hw => {
                        items.push({ day, period: parseInt(period, 10) || 0, title: hw.title });
                    });
                });
            });
            // sort by day order
            items.sort((a,b) => {
                const byDay = TT_DAY_INDEX[a.day] - TT_DAY_INDEX[b.day];
                if (byDay !== 0) return byDay;
                return a.period - b.period;
            });
            const upcoming = items.slice(0,3);
            if (!upcoming.length) {
                container.innerHTML = '<p style="color:var(--color-text-muted);text-align:center;padding:0.5rem;">' + HW_NONE_ENTERED_MSG + '</p>';
                return;
            }
            container.innerHTML = upcoming.map(item => {
                const dayName = TT_DAY_NAMES[item.day];
                return `<div class="grade-item">
                    <div>
                        <div>${escapeHtml(item.title)}</div>
                        <div style="font-size:0.8rem;color:var(--color-text-muted);">${escapeHtml(dayName)} · ${i18nFormat(TT_PERIOD_LABEL, {period: item.period})}</div>
                    </div>
                </div>`;
            }).join('');
        }

        function renderOverviewCalendar() {
            const container = document.getElementById('overviewCalendar');
            if (!container) return;
            const today = new Date();
            today.setHours(0,0,0,0);
            const todayStr = toDateStr(today);
            const rangeEnd = addDaysToDateStr(todayStr, 180);
            const items = getAllCalendarItems(todayStr, rangeEnd);
            const upcoming = items
                .filter(ev => new Date(ev.date + 'T00:00:00') >= today)
                .sort((a,b) => new Date(a.date) - new Date(b.date))
                .slice(0,3);
            if (!upcoming.length) {
                container.innerHTML = '<p style="color:var(--color-text-muted);text-align:center;padding:0.5rem;">' + CAL_NO_UPCOMING + '</p>';
                return;
            }
            container.innerHTML = upcoming.map(ev => {
                const d = new Date(ev.date + 'T00:00:00');
                const dateStr = d.toLocaleDateString(CAL_LOCALE, { day:'2-digit', month:'2-digit', year:'numeric' });
                let icon = '📌';
                if (ev.type === 'exam') icon = '📝';
                if (ev.type === 'todo') icon = '✅';
                if (ev.type === 'holiday') icon = '🎉';
                const recurringLabel = getCalendarRecurrenceLabel(ev.recurrence);
                const recurringText = recurringLabel ? ` · ${recurringLabel}` : '';
                return `<div class="grade-item">
                    <div>
                        <div>${icon} ${escapeHtml(ev.title)}</div>
                        <div style="font-size:0.8rem;color:var(--color-text-muted);">${dateStr}${recurringText}${ev.description ? ' · ' + escapeHtml(ev.description) : ''}</div>
                    </div>
                </div>`;
            }).join('');
        }

        async function renderOverviewGrades() {
            const container = document.getElementById('overviewGrades');
            if (!container) return;
            container.innerHTML = '<p style="color:var(--color-text-muted);text-align:center;padding:0.5rem;">Lädt…</p>';
            try {
                const res = await fetch('grades/grades_load.php');
                if (!res.ok) throw new Error();
                const grades = await res.json();
                if (!grades.length) {
                    container.innerHTML = '<p style="color:var(--color-text-muted);text-align:center;padding:0.5rem;">Noch keine Noten eingetragen</p>';
                    return;
                }
                let totalWeighted = 0;
                let sumWeighted = 0;
                grades.forEach(g => {
                    const w = (!g.weight || Number(g.weight) <= 0) ? 1 : Number(g.weight);
                    totalWeighted += w;
                    sumWeighted += Number(g.value) * w;
                });
                const avg = totalWeighted > 0 ? (sumWeighted / totalWeighted).toFixed(2) : '0.00';
                const avgPercent = Math.min(100, Math.round((Number(avg) / 15) * 100));

                const recentGrades = grades.slice(-3).reverse();
                container.innerHTML = `
                    <div class="grade-average-card" style="background: linear-gradient(135deg, rgba(27, 240, 229, 0.85), rgba(77, 120, 247, 0.95));">
                        <div class="grade-average-title">Durchschnitt</div>
                        <div class="grade-average-circle" style="background: ${buildCircleColor(avg)};">
                            <div class="grade-average-circle-inner">${avg} P</div>
                        </div>
                    </div>
                ` + recentGrades.map(g => `
                    <div class="grade-item">
                        <div>
                            <span class="grade-subject">${escapeHtml(g.subject)}</span>
                            ${g.description ? `<div style="font-size:0.8rem;color:var(--color-text-muted);">${escapeHtml(g.description)}</div>` : ''}
                        </div>
                        <span class="grade-value ${getGradeClass(g.value)}">${g.value} P</span>
                    </div>
                `).join('');
            } catch {
                container.innerHTML = '<p style="color:var(--color-text-muted);text-align:center;padding:0.5rem;">Noten konnten nicht geladen werden</p>';
            }
        }

        async function renderOverviewFiles() {
            const container = document.getElementById('overviewFiles');
            if (!container) return;
            container.innerHTML = '<p style="color:var(--color-text-muted);text-align:center;padding:0.5rem;">Lädt…</p>';
            try {
                const res = await fetch('files/files_load.php');
                if (!res.ok) throw new Error();
                const files = await res.json();
                if (!files.length) {
                    container.innerHTML = '<p style="color:var(--color-text-muted);text-align:center;padding:0.5rem;">Noch keine Dateien hochgeladen</p>';
                    return;
                }
                const icons = { pdf: '📄', xlsx: '📊', xls: '📊', docx: '📝', doc: '📝', mp4: '🎥', mp3: '🎵', jpg: '🖼️', jpeg: '🖼️', png: '🖼️' };
                container.innerHTML = files.slice(-3).reverse().map(f => {
                    const ext  = f.original_name.split('.').pop().toLowerCase();
                    const icon = icons[ext] || '📄';
                    return `<div class="file-item">
                        <span class="file-icon">${icon}</span>
                        <div class="file-info">
                            <div class="file-name">${escapeHtml(f.original_name)}</div>
                            <div class="file-meta">${escapeHtml(f.subject)}</div>
                        </div>
                        <a class="btn-icon" href="files/download.php?file_id=${f.id}" title="Herunterladen">⬇️</a>
                    </div>`;
                }).join('');
            } catch {
                container.innerHTML = '<p style="color:var(--color-text-muted);text-align:center;padding:0.5rem;">Dateien konnten nicht geladen werden</p>';
            }
        }

        function renderOverviewMessages() {
            const container = document.getElementById('overviewMessages');
            if (!container) return;
            const allMessages = document.querySelectorAll('#adminMessagesList .message-item');
            if (!allMessages.length) {
                container.innerHTML = '<p style="color:var(--color-text-muted);text-align:center;padding:0.5rem;">Keine Nachrichten</p>';
                return;
            }
            container.innerHTML = '';
            Array.from(allMessages).slice(0, 2).forEach(msg => {
                const clone = msg.cloneNode(true);
                // Remove message-text to keep the preview compact
                const text = clone.querySelector('.message-text');
                if (text) text.remove();
                container.appendChild(clone);
            });
        }

        const OVERVIEW_WIDGET_ORDER_KEY = 'overview_widget_order';
        let draggingOverviewWidget = null;
        let overviewDropTarget = null;
        let overviewCustomizeMode = false;
        let overviewDragMouseButton = 0;

        function saveOverviewWidgetOrder() {
            const grid = document.getElementById('overviewWidgetGrid');
            if (!grid) return;

            const order = Array.from(grid.querySelectorAll('.widget[data-widget-id]'))
                .map(widget => widget.dataset.widgetId)
                .filter(Boolean);

            writeScopedJson(OVERVIEW_WIDGET_ORDER_KEY, order);
        }

        function applyOverviewWidgetOrder() {
            const grid = document.getElementById('overviewWidgetGrid');
            if (!grid) return;

            const savedOrder = readScopedJson(OVERVIEW_WIDGET_ORDER_KEY, [], true);
            if (!Array.isArray(savedOrder) || !savedOrder.length) return;

            const widgets = Array.from(grid.querySelectorAll('.widget[data-widget-id]'));
            const widgetMap = new Map(
                widgets.map(widget => [widget.dataset.widgetId, widget])
            );

            savedOrder.forEach(widgetId => {
                const widget = widgetMap.get(widgetId);
                if (!widget) return;
                grid.appendChild(widget);
                widgetMap.delete(widgetId);
            });

            widgetMap.forEach(widget => {
                grid.appendChild(widget);
            });
        }

        function getOverviewDropTarget(grid, clientX, clientY) {
            const hovered = document.elementFromPoint(clientX, clientY);
            if (!hovered) return null;

            const target = hovered.closest('.widget[data-widget-id]');
            if (!target || target === draggingOverviewWidget || target.parentElement !== grid) {
                return null;
            }

            const rect = target.getBoundingClientRect();
            const insertAfter = clientY > rect.top + rect.height / 2;
            return { target, insertAfter };
        }

        function clearOverviewDropIndicator() {
            const grid = document.getElementById('overviewWidgetGrid');
            if (!grid) return;

            grid.querySelectorAll('.widget[data-widget-id].drop-swap-target').forEach(widget => {
                widget.classList.remove('drop-swap-target');
            });
        }

        function renderOverviewDropIndicator(dropTarget) {
            clearOverviewDropIndicator();
            if (!dropTarget || !dropTarget.target) return;

            dropTarget.target.classList.add('drop-swap-target');
        }

        function updateOverviewCustomizeButton() {
            const btn = document.getElementById('overviewCustomizeToggle');
            if (!btn) return;

            btn.textContent = overviewCustomizeMode ? 'Anpassen beenden' : 'Dashbord anpassen';
            btn.classList.toggle('active', overviewCustomizeMode);
        }

        function setOverviewCustomizeMode(enabled) {
            overviewCustomizeMode = !!enabled;

            const overview = document.getElementById('overview');
            if (overview) {
                overview.classList.toggle('customize-mode-active', overviewCustomizeMode);
            }

            const grid = document.getElementById('overviewWidgetGrid');
            if (grid) {
                grid.classList.toggle('widget-customize-mode', overviewCustomizeMode);
                grid.querySelectorAll('.widget[data-widget-id]').forEach(widget => {
                    widget.setAttribute('draggable', overviewCustomizeMode ? 'true' : 'false');
                });
            }

            if (!overviewCustomizeMode) {
                clearOverviewDropIndicator();
                if (draggingOverviewWidget) {
                    draggingOverviewWidget.classList.remove('widget-dragging');
                }
                draggingOverviewWidget = null;
                overviewDropTarget = null;
            }

            updateOverviewCustomizeButton();
        }

        function initOverviewWidgetReordering() {
            const grid = document.getElementById('overviewWidgetGrid');
            if (!grid) return;

            const widgets = Array.from(grid.querySelectorAll('.widget[data-widget-id]'));
            if (widgets.length < 2) return;

            applyOverviewWidgetOrder();

            widgets.forEach(widget => {
                widget.setAttribute('draggable', 'false');

                widget.addEventListener('mousedown', event => {
                    overviewDragMouseButton = event.button;
                });

                widget.addEventListener('dragstart', event => {
                    if (!overviewCustomizeMode || overviewDragMouseButton !== 0) {
                        event.preventDefault();
                        return;
                    }

                    draggingOverviewWidget = widget;
                    widget.classList.add('widget-dragging');

                    if (event.dataTransfer) {
                        event.dataTransfer.effectAllowed = 'move';
                        event.dataTransfer.setData('text/plain', widget.dataset.widgetId || 'widget');
                    }
                });

                widget.addEventListener('dragend', () => {
                    widget.classList.remove('widget-dragging');
                    clearOverviewDropIndicator();
                    draggingOverviewWidget = null;
                    overviewDropTarget = null;
                });
            });

            grid.addEventListener('dragover', event => {
                if (!draggingOverviewWidget) return;
                event.preventDefault();
                overviewDropTarget = getOverviewDropTarget(grid, event.clientX, event.clientY);
                renderOverviewDropIndicator(overviewDropTarget);
            });

            grid.addEventListener('drop', event => {
                if (!draggingOverviewWidget) return;
                event.preventDefault();

                if (overviewDropTarget && overviewDropTarget.target) {
                    const targetWidget = overviewDropTarget.target;
                    
                    // Tausche die beiden Widgets
                    if (targetWidget === draggingOverviewWidget) return;
                    
                    // Erstelle einen temp-Knoten als Platzhalter
                    const tempNode = document.createTextNode('');
                    grid.insertBefore(tempNode, draggingOverviewWidget);
                    grid.insertBefore(draggingOverviewWidget, targetWidget);
                    grid.insertBefore(targetWidget, tempNode);
                    tempNode.parentNode.removeChild(tempNode);
                }

                saveOverviewWidgetOrder();
            });

            const toggleBtn = document.getElementById('overviewCustomizeToggle');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', () => {
                    setOverviewCustomizeMode(!overviewCustomizeMode);
                });
            }

            setOverviewCustomizeMode(false);
        }

        function renderOverview() {
            renderOverviewTimetable();
            renderOverviewTodos();
            renderOverviewHomeworks();
            renderOverviewExams();
            renderOverviewCalendar();
            renderOverviewGrades();
            renderOverviewFlashcards();
            renderOverviewFiles();
            renderOverviewMessages();
            renderOverviewAdmin();
        }
        // ===== CALENDAR EVENT HANDLING =====
        async function addCalendarEvent() {
            const titleEl = document.getElementById('eventTitle');
            const dateEl  = document.getElementById('eventDate');
            const descEl  = document.getElementById('eventDesc');
            if (!titleEl.value.trim() || !dateEl.value) return;

            try {
                const res = await fetch('calendar/calendar_add.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        title: titleEl.value.trim(),
                        date: dateEl.value,
                        description: descEl ? descEl.value.trim() : ''
                    })
                });
                if (!res.ok) throw new Error();

                titleEl.value = '';
                dateEl.value  = '';
                if (descEl) descEl.value = '';

                await loadCalendarExtras();
            } catch (err) {
                console.error('Kalendereintrag konnte nicht gespeichert werden', err);
            }
        }

        function openCalendarQuickAddModal() {
            const modal = document.getElementById('calendarQuickAddModal');
            const dateLabel = document.getElementById('calendarQuickAddDateLabel');
            const titleEl = document.getElementById('quickEventTitle');
            const descEl = document.getElementById('quickEventDesc');
            const recurrenceEl = document.getElementById('quickEventRecurrence');
            const colorEl = document.getElementById('quickEventColor');
            const startTimeEl = document.getElementById('quickEventStartTime');
            const endTimeEl = document.getElementById('quickEventEndTime');
            if (!modal || !dateLabel) return;

            const selectedDate = currentSelectedDate || getVisibleDefaultDate();
            if (selectedDate && selectedDate !== currentSelectedDate) {
                showEventsForDate(selectedDate);
            }

            const d = new Date((currentSelectedDate || selectedDate) + 'T00:00:00');
            dateLabel.textContent = d.toLocaleDateString(CAL_LOCALE, {
                weekday: 'long',
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });

            if (titleEl) titleEl.value = '';
            if (descEl) descEl.value = '';
            if (recurrenceEl) recurrenceEl.value = 'none';
            if (colorEl) colorEl.value = DEFAULT_CALENDAR_EVENT_COLOR;
            if (startTimeEl) startTimeEl.value = '';
            if (endTimeEl) endTimeEl.value = '';

            updateCalendarTitleSuggestions();

            modal.classList.add('open');
            if (titleEl) titleEl.focus();
        }

        function closeCalendarQuickAddModal() {
            const modal = document.getElementById('calendarQuickAddModal');
            if (!modal) return;
            modal.classList.remove('open');
        }

        async function submitCalendarQuickAdd() {
            const titleEl = document.getElementById('quickEventTitle');
            const descEl = document.getElementById('quickEventDesc');
            const recurrenceEl = document.getElementById('quickEventRecurrence');
            const colorEl = document.getElementById('quickEventColor');
            const startTimeEl = document.getElementById('quickEventStartTime');
            const endTimeEl = document.getElementById('quickEventEndTime');
            const selectedDate = currentSelectedDate || getVisibleDefaultDate();
            if (!titleEl || !selectedDate) return;

            const title = titleEl.value.trim();
            const startTime = normalizeTimeValue(startTimeEl?.value);
            const endTime = normalizeTimeValue(endTimeEl?.value);
            if (!title) {
                titleEl.focus();
                return;
            }
            if (!!startTime !== !!endTime) {
                showToast('Bitte beide Zeiten angeben oder beide leer lassen.', 'warning');
                if (startTimeEl) startTimeEl.focus();
                return;
            }
            if (startTime && endTime && endTime <= startTime) {
                showToast('Die Endzeit muss nach der Startzeit liegen.', 'warning');
                if (endTimeEl) endTimeEl.focus();
                return;
            }

            try {
                const res = await fetch('calendar/calendar_add.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        title,
                        date: selectedDate,
                        recurrence: recurrenceEl ? recurrenceEl.value : 'none',
                        description: descEl ? descEl.value.trim() : '',
                        color: normalizeHexColor(colorEl?.value),
                        start_time: startTime,
                        end_time: endTime
                    })
                });
                if (!res.ok) throw new Error();

                closeCalendarQuickAddModal();
                await loadCalendarExtras();
            } catch (err) {
                console.error('Kalendereintrag konnte nicht gespeichert werden', err);
            }
        }

        // ===== INITIALISIERUNG =====
        renderTimetableView();
        renderHomework(true, 'homeworkGrid');
        renderHomework(false, 'homeworkGridTimetable');
        renderExams();
        initCalendar();
        initOverviewWidgetReordering();
        renderOverview();
        loadTimetable();
        loadHomeworkData();
        loadExamsData();
        loadCalendarExtras();
        loadSubjects();
        loadGrades();
        loadTodos();
        loadAdminMessages();
        if (IS_ADMIN) loadAdminPanel();
        if (IS_ADMIN) loadAdminMessageManagement();
        if (IS_ADMIN) loadAdminUsers();

        const initialTabParam = new URLSearchParams(window.location.search).get('tab');
        const initialView = mapTabParamToView(initialTabParam);
        openViewById(initialView);

        const I18N = {
            enterUsername: <?php echo json_encode(t('js.enter_username')); ?>,
            errorGeneric: <?php echo json_encode(t('js.error_generic')); ?>,
            serverUnreachable: <?php echo json_encode(t('js.server_unreachable')); ?>,
            fillAllFields: <?php echo json_encode(t('js.fill_all_fields')); ?>,
            passwordMismatch: <?php echo json_encode(t('js.password_mismatch')); ?>,
            passwordMinLength: <?php echo json_encode(t('js.password_min_length')); ?>,
            enterValidEmail: <?php echo json_encode(t('js.enter_valid_email')); ?>,
            codeSentEnterVerification: <?php echo json_encode(t('js.code_sent_enter_verification')); ?>,
            sendCodeFirst: <?php echo json_encode(t('js.send_code_first')); ?>,
            enterVerificationCode: <?php echo json_encode(t('js.enter_verification_code')); ?>,
            enterPassword: <?php echo json_encode(t('js.enter_password')); ?>,
            confirmDeleteAccount: <?php echo json_encode(t('js.confirm_delete_account')); ?>,
            accountDeletedLogout: <?php echo json_encode(t('js.account_deleted_logout')); ?>,
            loadGradesError: <?php echo json_encode(t('js.load_grades_error')); ?>,
            noGrades: <?php echo json_encode(t('js.no_grades')); ?>,
            average: <?php echo json_encode(t('js.average')); ?>,
            deleteLabel: <?php echo json_encode(t('js.delete')); ?>,
            saveGradesError: <?php echo json_encode(t('js.save_grades_error')); ?>,
            serverBackendHint: <?php echo json_encode(t('js.server_backend_hint')); ?>,
            confirmDeleteGrade: <?php echo json_encode(t('js.confirm_delete_grade')); ?>,
            loadSubjectsError: <?php echo json_encode(t('js.load_subjects_error')); ?>,
            noSubjects: <?php echo json_encode(t('js.no_subjects')); ?>,
            gradeStats: <?php echo json_encode(t('js.grade_stats')); ?>,
            confirmDeleteSubject: <?php echo json_encode(t('js.confirm_delete_subject')); ?>,
            loadDetails: <?php echo json_encode(t('js.load_details')); ?>,
            subjectNotFound: <?php echo json_encode(t('js.subject_not_found')); ?>,
            subjectDetailsFor: <?php echo json_encode(t('js.subject_details_for')); ?>,
            detailsGrades: <?php echo json_encode(t('js.details_grades')); ?>,
            detailsNoGrades: <?php echo json_encode(t('js.details_no_grades')); ?>,
            detailsTodos: <?php echo json_encode(t('js.details_todos')); ?>,
            detailsNoTodos: <?php echo json_encode(t('js.details_no_todos')); ?>,
            detailsExams: <?php echo json_encode(t('js.details_exams')); ?>,
            detailsNoExams: <?php echo json_encode(t('js.details_no_exams')); ?>,
            completed: <?php echo json_encode(t('js.completed')); ?>,
            open: <?php echo json_encode(t('js.open')); ?>,
            weight: <?php echo json_encode(t('js.weight')); ?>,
            standard: <?php echo json_encode(t('js.standard')); ?>,
            notGradedYet: <?php echo json_encode(t('js.not_graded_yet')); ?>,
            loadDetailsError: <?php echo json_encode(t('js.load_details_error')); ?>,
            subjectChoose: <?php echo json_encode(t('js.subject_choose')); ?>,
            // Flashcards
            fcPublic: <?php echo json_encode(t('flashcards.public')); ?>,
            fcPrivate: <?php echo json_encode(t('flashcards.private')); ?>,
            fcLoadingDecks: <?php echo json_encode(t('flashcards.loading_decks')); ?>,
            fcNoDecks: <?php echo json_encode(t('flashcards.no_decks')); ?>,
            fcNoCards: <?php echo json_encode(t('flashcards.no_cards')); ?>,
            fcLoadingPublicDecks: <?php echo json_encode(t('flashcards.loading_public_decks')); ?>,
            fcNoPublicDecks: <?php echo json_encode(t('flashcards.no_public_decks')); ?>,
            fcByUser: <?php echo json_encode(t('flashcards.by_user')); ?>,
            fcNoDescription: <?php echo json_encode(t('flashcards.no_description')); ?>,
            fcCopy: <?php echo json_encode(t('flashcards.copy')); ?>,
            fcCardSingular: <?php echo json_encode(t('flashcards.card_singular')); ?>,
            fcCardsPlural: <?php echo json_encode(t('flashcards.card_plural')); ?>,
            fcDeckSingular: <?php echo json_encode(t('flashcards.deck_singular')); ?>,
            fcDecksPlural: <?php echo json_encode(t('flashcards.deck_plural')); ?>,
            enterDeckName: <?php echo json_encode(t('js.enter_deck_name')); ?>,
            createDeckError: <?php echo json_encode(t('js.create_deck_error')); ?>,
            fcEnterFrontBack: <?php echo json_encode(t('js.enter_front_back')); ?>,
            confirmDeleteCard: <?php echo json_encode(t('js.confirm_delete_card')); ?>,
            confirmDeleteDeck: <?php echo json_encode(t('js.confirm_delete_deck')); ?>,
            deleteError: <?php echo json_encode(t('js.delete_error')); ?>,
            copySuccess: <?php echo json_encode(t('js.copy_success')); ?>,
            copyError: <?php echo json_encode(t('js.copy_error')); ?>
        };

        function i18nFormat(template, values = {}) {
            return String(template).replace(/\{(\w+)\}/g, (_, key) => values[key] ?? '');
        }

        // ===== ACCOUNT SETTINGS MODAL =====

        let emailChangeVerificationId = '';
        let deleteAccountVerificationId = '';

        function openAccountModal() {
            document.getElementById('accountModal').classList.add('open');
        }

        function closeAccountModal() {
            document.getElementById('accountModal').classList.remove('open');
            ['msgUsername','msgPassword','msgEmail','msgDelete'].forEach(id => {
                const el = document.getElementById(id);
                if (el) { el.textContent = ''; el.className = 'modal-msg'; }
            });
            emailChangeVerificationId = '';
            deleteAccountVerificationId = '';
            const emailCode = document.getElementById('emailVerificationCode');
            const deleteCode = document.getElementById('deleteVerificationCode');
            if (emailCode) emailCode.value = '';
            if (deleteCode) deleteCode.value = '';
        }

        document.getElementById('accountModal').addEventListener('click', function(e) {
            if (e.target === this) closeAccountModal();
        });

        function setMsg(id, text, type) {
            const el = document.getElementById(id);
            el.textContent = text;
            el.className = 'modal-msg ' + type;
        }

        async function parseApiResponse(res) {
            const raw = await res.text();
            if (!raw) return {};
            try {
                return JSON.parse(raw);
            } catch {
                return { detail: raw };
            }
        }

        async function changeUsername() {
            const val = document.getElementById('newUsername').value.trim();
            if (!val) return setMsg('msgUsername', I18N.enterUsername, 'error');
            try {
                const res = await fetch(`${BACKEND_URL}/auth/change-username/${CURRENT_USER_ID}`, {
                    method: 'PUT',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ new_username: val })
                });
                const data = await parseApiResponse(res);
                if (res.ok) {
                    setMsg('msgUsername', '✅ ' + data.message, 'success');
                    document.getElementById('newUsername').value = '';
                } else {
                    setMsg('msgUsername', '❌ ' + (data.detail || I18N.errorGeneric), 'error');
                }
            } catch { setMsg('msgUsername', '❌ ' + I18N.serverUnreachable, 'error'); }
        }

        async function changePassword() {
            const oldPw  = document.getElementById('oldPassword').value;
            const newPw  = document.getElementById('newPassword').value;
            const newPw2 = document.getElementById('newPassword2').value;
            if (!oldPw || !newPw) return setMsg('msgPassword', I18N.fillAllFields, 'error');
            if (newPw !== newPw2) return setMsg('msgPassword', '❌ ' + I18N.passwordMismatch, 'error');
            if (newPw.length < 6) return setMsg('msgPassword', '❌ ' + I18N.passwordMinLength, 'error');
            try {
                const res = await fetch(`${BACKEND_URL}/auth/change-password/${CURRENT_USER_ID}`, {
                    method: 'PUT',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ old_password: oldPw, new_password: newPw })
                });
                const data = await parseApiResponse(res);
                if (res.ok) {
                    setMsg('msgPassword', '✅ ' + data.message, 'success');
                    document.getElementById('oldPassword').value = '';
                    document.getElementById('newPassword').value = '';
                    document.getElementById('newPassword2').value = '';
                } else {
                    setMsg('msgPassword', '❌ ' + (data.detail || I18N.errorGeneric), 'error');
                }
            } catch { setMsg('msgPassword', '❌ ' + I18N.serverUnreachable, 'error'); }
        }

        async function requestEmailChangeCode() {
            const val = document.getElementById('newEmail').value.trim();
            if (!val || !val.includes('@')) return setMsg('msgEmail', I18N.enterValidEmail, 'error');
            try {
                const res = await fetch('auth/change_email_request.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ new_email: val })
                });
                const data = await parseApiResponse(res);
                if (res.ok) {
                    emailChangeVerificationId = data.verification_id || '';
                    setMsg('msgEmail', '✅ ' + I18N.codeSentEnterVerification, 'success');
                } else {
                    setMsg('msgEmail', '❌ ' + (data.detail || data.error || I18N.errorGeneric), 'error');
                }
            } catch { setMsg('msgEmail', '❌ ' + I18N.serverUnreachable, 'error'); }
        }

        async function confirmEmailChange() {
            const code = document.getElementById('emailVerificationCode').value.trim();
            if (!emailChangeVerificationId) return setMsg('msgEmail', I18N.sendCodeFirst, 'error');
            if (!code) return setMsg('msgEmail', I18N.enterVerificationCode, 'error');
            try {
                const res = await fetch('auth/change_email_confirm.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ verification_id: emailChangeVerificationId, code: code })
                });
                const data = await parseApiResponse(res);
                if (res.ok) {
                    setMsg('msgEmail', '✅ ' + data.message, 'success');
                    document.getElementById('newEmail').value = '';
                    document.getElementById('emailVerificationCode').value = '';
                    emailChangeVerificationId = '';
                } else {
                    setMsg('msgEmail', '❌ ' + (data.detail || data.error || I18N.errorGeneric), 'error');
                }
            } catch { setMsg('msgEmail', '❌ ' + I18N.serverUnreachable, 'error'); }
        }

        async function requestDeleteAccountCode() {
            const pw = document.getElementById('deletePassword').value;
            if (!pw) return setMsg('msgDelete', I18N.enterPassword, 'error');
            try {
                const res = await fetch(`${BACKEND_URL}/auth/delete-account/request-code/${CURRENT_USER_ID}`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ password: pw })
                });
                const data = await parseApiResponse(res);
                if (res.ok) {
                    deleteAccountVerificationId = data.verification_id || '';
                    setMsg('msgDelete', '✅ ' + I18N.codeSentEnterVerification, 'success');
                } else {
                    setMsg('msgDelete', '❌ ' + (data.detail || I18N.errorGeneric), 'error');
                }
            } catch { setMsg('msgDelete', '❌ ' + I18N.serverUnreachable, 'error'); }
        }

        async function confirmDeleteAccount() {
            const code = document.getElementById('deleteVerificationCode').value.trim();
            if (!deleteAccountVerificationId) return setMsg('msgDelete', I18N.sendCodeFirst, 'error');
            if (!code) return setMsg('msgDelete', I18N.enterVerificationCode, 'error');
            if (!confirm(I18N.confirmDeleteAccount)) return;
            try {
                const res = await fetch(`${BACKEND_URL}/auth/delete-account/confirm/${CURRENT_USER_ID}`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ verification_id: deleteAccountVerificationId, code: code })
                });
                const data = await parseApiResponse(res);
                if (res.ok) {
                    showToast(I18N.accountDeletedLogout, 'success');
                    setTimeout(() => { window.location.href = 'auth/logout.php'; }, 1500);
                } else {
                    setMsg('msgDelete', '❌ ' + (data.detail || I18N.errorGeneric), 'error');
                }
            } catch { setMsg('msgDelete', '❌ ' + I18N.serverUnreachable, 'error'); }
        }

        // ===== NOTEN (0-15 Punkte) API =====
        function getGradeClass(value) {
            if (value >= 12) return '';
            if (value >= 7)  return 'warning';
            return 'danger';
        }

        function buildCircleColor(average) {
            const avg = Number(average);
            if (avg >= 12) return '#2bbd64';      // kräftiges grün
            if (avg >= 9) return '#a2dd42';       // limettengelb
            if (avg >= 6) return '#ffd800';       // gelb
            if (avg >= 3) return '#ff9300';       // orange
            return '#ff4b4b';                     // rot
        }

        async function loadGrades() {
            const list = document.getElementById('gradesList');
            if (!list) return;
            try {
                const res = await fetch('grades/grades_load.php');
                if (!res.ok) {
                    console.error('Fehler beim Laden der Noten, Status', res.status);
                    list.innerHTML = `<div class="empty-state">
                    <div class="empty-state-icon">❌</div>
                    <div class="empty-state-title">${escapeHtml(I18N.loadGradesError)}</div>
                </div>`;
                    return;
                }
                const grades = await res.json();
                gradesData = grades; // Global speichern für Dropdown-Statistiken
                if (!grades.length) {
                    list.innerHTML = `<div class="empty-state">
                    <div class="empty-state-icon">📊</div>
                    <div class="empty-state-title">${escapeHtml(I18N.noGrades)}</div>
                    <div class="empty-state-text">Füge oben deine erste Note hinzu.</div>
                </div>`;
                    return;
                }

                let totalWeighted = 0;
                let sumWeighted = 0;
                grades.forEach(g => {
                    const w = (!g.weight || Number(g.weight) <= 0) ? 1 : Number(g.weight);
                    totalWeighted += w;
                    sumWeighted += Number(g.value) * w;
                });
                const average = totalWeighted > 0 ? (sumWeighted / totalWeighted).toFixed(2) : '0.00';

                const avgPercent = Math.min(100, Math.round((Number(average) / 15) * 100));
                const circleColor = buildCircleColor(average);
                list.innerHTML = `
                    <div class="grade-average-card">
                        <div class="grade-average-title">${escapeHtml(I18N.average)}</div>
                        <div class="grade-average-circle" style="background: ${circleColor};">
                            <div class="grade-average-circle-inner">${average}</div>
                        </div>
                    </div>
                ` + grades.map(g => {
                    const w = (!g.weight || Number(g.weight) <= 0) ? 1 : Number(g.weight);
                    return `
                    <div class="grade-item" id="grade-${g.id}">
                        <div>
                            <span class="grade-subject">${escapeHtml(g.subject)}</span>
                            ${g.description ? `<div style="font-size:0.8rem;color:var(--color-text-muted);">${escapeHtml(g.description)}</div>` : ''}
                        </div>
                        <div style="display:flex;align-items:center;gap:0.5rem;">
                            <span class="grade-value ${getGradeClass(g.value)}">${g.value} P</span>
                            <small style="color:var(--color-text-muted);">x${w}</small>
                            <button class="btn-icon" onclick="removeGrade('${g.id}')" title="${escapeHtml(I18N.deleteLabel)}">🗑️</button>
                        </div>
                    </div>
                `;
                }).join('');
            } catch (err) {
                console.error('Netzwerkfehler beim Laden der Noten', err);
                list.innerHTML = `<p style="color:red;text-align:center;padding:1rem;">${escapeHtml(I18N.serverUnreachable)}</p>`;
            }
        }

        async function addGrade() {
            const subjectInput = document.getElementById('gradeSubject');
            const valueInput   = document.getElementById('gradeValue');
            const weightInput  = document.getElementById('gradeWeight');
            const descInput    = document.getElementById('gradeDescription');
            if (!subjectInput.value.trim() || valueInput.value === '') return;
            const value = parseFloat(valueInput.value);
            const weight = parseFloat(weightInput.value);
            if (isNaN(value) || value < 0 || value > 15) return;
            if (isNaN(weight) || weight <= 0) return;
            try {
                const res = await fetch('grades/grade_add.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        subject: subjectInput.value.trim(),
                        value: value,
                        weight: weight,
                        description: descInput ? descInput.value.trim() : ''
                    })
                });
                if (res.ok) {
                    subjectInput.value = '';
                    valueInput.value   = '';
                    weightInput.value  = '1';
                    if (descInput) descInput.value = '';
                    loadGrades();
                } else {
                    console.error('Fehler beim Speichern der Note, Status', res.status);
                    showToast(I18N.saveGradesError, 'error');
                }
            } catch (err) {
                console.error('Netzwerkfehler beim Speichern der Note', err);
                showToast(I18N.serverBackendHint, 'error');
            }
        }

        async function removeGrade(gradeId) {
            if (!confirm(I18N.confirmDeleteGrade)) return;
            try {
                const res = await fetch('grades/grade_delete.php?grade_id=' + encodeURIComponent(gradeId), {
                    method: 'DELETE'
                });
                if (res.ok) {
                    await loadGrades();
                    renderOverviewGrades();
                }
            } catch { /* Server nicht erreichbar */ }
        }

        function triggerFileDownload(url) {
            const link = document.createElement('a');
            link.href = url;
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function exportTimetableCSV() {
            triggerFileDownload('timetable/timetable_export_csv.php');
        }

        function exportTimetablePDF() {
            triggerFileDownload('timetable/timetable_export_pdf.php');
        }

        function exportGradesCSV() {
            triggerFileDownload('grades/grades_export_csv.php');
        }

        function exportGradesPDF() {
            triggerFileDownload('grades/grades_export_pdf.php');
        }

        // ========================================
        // SUBJECTS (FÄCHER)
        // ========================================

        let subjectsData = null;
        let gradesData = null;

        async function loadSubjects() {
            const list = document.getElementById('subjectsList');
            if (!list) return;
            try {
                const res = await fetch('subjects/subjects_load.php');
                if (!res.ok) {
                    console.error('Fehler beim Laden der Fächer, Status', res.status);
                    list.innerHTML = `<p style="color:red;text-align:center;padding:1rem;">${escapeHtml(I18N.loadSubjectsError)}</p>`;
                    return;
                }
                subjectsData = await res.json();
                populateSubjectDropdowns();

                // Load grades data for statistics if not available
                if (!gradesData) {
                    try {
                        const gradesRes = await fetch('grades/grades_load.php');
                        if (gradesRes.ok) {
                            const grades = await gradesRes.json();
                            gradesData = grades;
                        }
                    } catch (err) {
                        console.warn('Fehler beim Laden der Noten für Statistiken', err);
                    }
                }

                if (!subjectsData.length) {
                    list.innerHTML = `<p style="color:var(--color-text-muted);text-align:center;padding:1rem;">${escapeHtml(I18N.noSubjects)}</p>`;
                    return;
                }

                // Calculate statistics per subject
                const subjectStats = {};
                if (gradesData) {
                    gradesData.forEach(g => {
                        if (!subjectStats[g.subject]) {
                            subjectStats[g.subject] = { total: 0, count: 0, grades: [] };
                        }
                        const weight = (!g.weight || Number(g.weight) <= 0) ? 1 : Number(g.weight);
                        subjectStats[g.subject].total += Number(g.value) * weight;
                        subjectStats[g.subject].count += weight;
                        subjectStats[g.subject].grades.push(g.value);
                    });
                }

                list.innerHTML = subjectsData.map(s => {
                    const stats = subjectStats[s.name];
                    let statsHtml = '';
                    if (stats && stats.count > 0) {
                        const avg = (stats.total / stats.count).toFixed(1);
                        const gradeCount = stats.grades.length;
                        statsHtml = `<div style="font-size:0.8rem;color:var(--color-text-muted);margin-top:0.25rem;">${escapeHtml(i18nFormat(I18N.gradeStats, { avg, count: gradeCount, suffix: gradeCount !== 1 ? 'n' : '' }))}</div>`;
                    }
                    return `
                    <div class="grade-item" id="subject-${s.id}" style="border-left: 4px solid ${escapeHtml(s.color)}; cursor:pointer;" onclick="showSubjectDetails('${s.id}')">
                            <div style="display:flex;align-items:center;gap:0.5rem;">
                                <div style="width:20px;height:20px;background-color:${escapeHtml(s.color)};border:1px solid var(--color-border);border-radius:3px;"></div>
                                <span class="grade-subject">${escapeHtml(s.name)}</span>
                            </div>
                            ${statsHtml}
                        <div style="display:flex;align-items:center;gap:0.5rem;">
                            <button class="btn-icon" onclick="event.stopPropagation(); removeSubject('${s.id}')" title="${escapeHtml(I18N.deleteLabel)}">🗑️</button>
                        </div>
                    </div>
                `;
                }).join('');
            } catch (err) {
                console.error('Netzwerkfehler beim Laden der Fächer', err);
                list.innerHTML = `<p style="color:red;text-align:center;padding:1rem;">${escapeHtml(I18N.serverUnreachable)}</p>`;
            }
        }

        async function addSubject() {
            const nameInput  = document.getElementById('subjectName');
            const colorInput = document.getElementById('subjectColor');
            if (!nameInput.value.trim()) return;
            try {
                const res = await fetch('subjects/subject_add.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        name: nameInput.value.trim(),
                        color: colorInput.value
                    })
                });
                if (res.ok) {
                    nameInput.value = '';
                    colorInput.value = '#1E90FF'; // Reset to first color (Blau)
                    const btn = document.getElementById('subjectColorBtn');
                    btn.style.backgroundColor = '#1E90FF';
                    btn.style.color = getContrastColor('#1E90FF');
                    loadSubjects();
                    // Update subject dropdowns in other tabs
                    populateSubjectDropdowns();
                } else {
                    console.error('Fehler beim Speichern des Fachs, Status', res.status);
                    showToast(I18N.saveGradesError, 'error');
                }
            } catch (err) {
                console.error('Netzwerkfehler beim Speichern des Fachs', err);
                showToast(I18N.serverBackendHint, 'error');
            }
        }

        async function removeSubject(subjectId) {
            if (!confirm(I18N.confirmDeleteSubject)) return;
            try {
                const res = await fetch('subjects/subject_delete.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: subjectId})
                });
                if (res.ok) {
                    const el = document.getElementById(`subject-${subjectId}`);
                    if (el) el.remove();
                    loadSubjects();
                    // Update dropdowns
                    populateSubjectDropdowns();
                }
            } catch (err) {
                console.error('Fehler beim Löschen des Fachs', err);
            }
        }

        async function showSubjectDetails(subjectId) {
            const modal = document.getElementById('detailsModal');
            const title = document.getElementById('detailsTitle');
            const content = document.getElementById('detailsContent');
            
            modal.style.display = 'flex';
            content.innerHTML = `<p style="text-align:center;">${escapeHtml(I18N.loadDetails)}</p>`;
            
            try {
                // Load all data
                const [gradesRes, todosRes, examsRes] = await Promise.all([
                    fetch('grades/grades_load.php'),
                    fetch('todos/todos_load.php'),
                    fetch('exams/exams_load.php')
                ]);
                
                const grades = gradesRes.ok ? await gradesRes.json() : [];
                const todos = todosRes.ok ? await todosRes.json() : [];
                const exams = examsRes.ok ? await examsRes.json() : [];
                
                // Find subject name
                const subject = subjectsData.find(s => s.id === subjectId);
                if (!subject) {
                    content.innerHTML = `<p style="color:red;text-align:center;">${escapeHtml(I18N.subjectNotFound)}</p>`;
                    return;
                }
                
                title.textContent = i18nFormat(I18N.subjectDetailsFor, { subject: subject.name });
                
                // Filter data by subject
                const subjectGrades = grades.filter(g => g.subject === subject.name);
                const subjectTodos = todos.filter(t => t.subject === subject.name);
                const subjectExams = exams.filter(e => e.subject === subject.name);
                
                // Build HTML
                let html = '';
                
                // Grades
                html += `<h4>${escapeHtml(i18nFormat(I18N.detailsGrades, { count: subjectGrades.length }))}</h4>`;
                if (subjectGrades.length) {
                    html += '<ul>';
                    subjectGrades.forEach(g => {
                        html += `<li>${g.value}P (${g.weight ? escapeHtml(I18N.weight) + ': ' + g.weight : escapeHtml(I18N.standard)}) ${g.description ? '- ' + escapeHtml(g.description) : ''}</li>`;
                    });
                    html += '</ul>';
                } else {
                    html += `<p>${escapeHtml(I18N.detailsNoGrades)}</p>`;
                }
                
                // Todos
                html += `<h4>${escapeHtml(i18nFormat(I18N.detailsTodos, { count: subjectTodos.length }))}</h4>`;
                if (subjectTodos.length) {
                    html += '<ul>';
                    subjectTodos.forEach(t => {
                        html += `<li>${escapeHtml(t.text)} ${t.completed ? '(' + escapeHtml(I18N.completed) + ')' : '(' + escapeHtml(I18N.open) + ')'}</li>`;
                    });
                    html += '</ul>';
                } else {
                    html += `<p>${escapeHtml(I18N.detailsNoTodos)}</p>`;
                }
                
                // Exams
                html += `<h4>${escapeHtml(i18nFormat(I18N.detailsExams, { count: subjectExams.length }))}</h4>`;
                if (subjectExams.length) {
                    html += '<ul>';
                    subjectExams.forEach(e => {
                        html += `<li>${escapeHtml(e.topic)} - ${e.date} (${e.grade ? e.grade + 'P' : escapeHtml(I18N.notGradedYet)})</li>`;
                    });
                    html += '</ul>';
                } else {
                    html += `<p>${escapeHtml(I18N.detailsNoExams)}</p>`;
                }
                
                content.innerHTML = html;
                
            } catch (err) {
                console.error('Fehler beim Laden der Fach-Details', err);
                content.innerHTML = `<p style="color:red;text-align:center;">${escapeHtml(I18N.loadDetailsError)}</p>`;
            }
        }

        function openColorModal() {
            document.getElementById('colorModal').style.display = 'flex';
        }

        function closeColorModal() {
            document.getElementById('colorModal').style.display = 'none';
        }

        function selectColor(color) {
            document.getElementById('subjectColor').value = color;
            document.getElementById('subjectColorBtn').style.backgroundColor = color;
            document.getElementById('subjectColorBtn').style.color = getContrastColor(color);
            closeColorModal();
        }

        function getContrastColor(hex) {
            // Simple function to get black or white text based on brightness
            const r = parseInt(hex.slice(1, 3), 16);
            const g = parseInt(hex.slice(3, 5), 16);
            const b = parseInt(hex.slice(5, 7), 16);
            const brightness = (r * 299 + g * 587 + b * 114) / 1000;
            return brightness > 128 ? 'black' : 'white';
        }

        function closeDetailsModal() {
            document.getElementById('detailsModal').style.display = 'none';
        }

        // Add event listener to close modals when clicking outside
        document.addEventListener('DOMContentLoaded', function() {
            const detailsModal = document.getElementById('detailsModal');
            const colorModal = document.getElementById('colorModal');

            if (detailsModal) {
                detailsModal.addEventListener('click', function(event) {
                    if (event.target === detailsModal) {
                        closeDetailsModal();
                    }
                });
            }

            if (colorModal) {
                colorModal.addEventListener('click', function(event) {
                    if (event.target === colorModal) {
                        closeColorModal();
                    }
                });
            }
        });

        // Update all subject dropdowns
        async function populateSubjectDropdowns() {
            // Load subjects data if not available
            if (!subjectsData) {
                try {
                    const res = await fetch('subjects/subjects_load.php');
                    if (res.ok) {
                        subjectsData = await res.json();
                    } else {
                        console.error('Fehler beim Laden der Fächer für Dropdowns');
                        return;
                    }
                } catch (err) {
                    console.error('Fehler beim Laden der Fächer für Dropdowns', err);
                    return;
                }
            }

            // Load grades data if not available (for statistics)
            if (!gradesData) {
                try {
                    const res = await fetch('grades/grades_load.php');
                    if (res.ok) {
                        const grades = await res.json();
                        gradesData = grades; // Store globally
                    } else {
                        console.warn('Noten konnten nicht geladen werden für Statistiken');
                    }
                } catch (err) {
                    console.warn('Fehler beim Laden der Noten für Statistiken', err);
                }
            }

            const dropdowns = document.querySelectorAll('[data-subject-dropdown]');
            dropdowns.forEach(dd => {
                const current = dd.value;
                // Berechne Durchschnitt pro Fach
                const subjectStats = {};
                if (gradesData) {
                    gradesData.forEach(g => {
                        if (!subjectStats[g.subject]) {
                            subjectStats[g.subject] = { total: 0, count: 0, grades: [] };
                        }
                        const weight = (!g.weight || Number(g.weight) <= 0) ? 1 : Number(g.weight);
                        subjectStats[g.subject].total += Number(g.value) * weight;
                        subjectStats[g.subject].count += weight;
                        subjectStats[g.subject].grades.push(g.value);
                    });
                }

                dd.innerHTML = `<option value="">${escapeHtml(I18N.subjectChoose)}</option>` +
                    subjectsData.map(s => {
                        return `<option value="${escapeHtml(s.name)}" style="background-color: ${escapeHtml(s.color)}20; color: inherit;">${escapeHtml(s.name)}</option>`;
                    }).join('');
                if (current) dd.value = current;
            });
        }

        // ===== Global Keyboard Shortcuts =====
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                // Close all open modals
                document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
                // Close modals that use display:flex
                ['detailsModal', 'colorModal', 'examGradeModal', 'calendarDeleteModal'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el && (el.style.display === 'flex' || el.style.display === 'block')) el.style.display = 'none';
                });
                // Close mobile sidebar
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebarOverlay');
                if (sidebar) sidebar.classList.remove('open');
                if (overlay) overlay.classList.remove('open');
            }
        });
    </script>
</body>
</html>

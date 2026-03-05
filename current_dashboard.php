<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

?>




<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LearnHub Dashboard</title>
    <script>
(function () {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
})();

//
</script>

    <style>
        :root {
            --font-family-base: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --font-family-mono: 'Courier New', monospace;
            
            /* Light Mode Colors */
            --color-bg-primary: #f8f9fa;
            --color-bg-secondary: #ffffff;
            --color-bg-surface: #ffffff;
            --color-bg-hover: #f1f3f5;
            --color-text-primary: #1a1a1a;
            --color-text-secondary: #6c757d;
            --color-text-muted: #adb5bd;
            --color-primary: #0d6efd;
            --color-primary-hover: #0b5ed7;
            --color-primary-active: #0a58ca;
            --color-border: #dee2e6;
            --color-border-light: #e9ecef;
            --color-success: #198754;
            --color-warning: #ffc107;
            --color-danger: #dc3545;
            --color-info: #0dcaf0;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.07);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        [data-theme="dark"] {
            --color-bg-primary: #0f172a;
            --color-bg-secondary: #1e293b;
            --color-bg-surface: #1e293b;
            --color-bg-hover: #334155;
            --color-text-primary: #f1f5f9;
            --color-text-secondary: #cbd5e1;
            --color-text-muted: #94a3b8;
            --color-primary: #38bdf8;
            --color-primary-hover: #0ea5e9;
            --color-primary-active: #0284c7;
            --color-border: #334155;
            --color-border-light: #475569;
            --color-success: #10b981;
            --color-warning: #f59e0b;
            --color-danger: #ef4444;
            --color-info: #06b6d4;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.4);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.5);
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
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--color-border);
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--color-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .sidebar-nav {
            flex: 1;
            padding: 1rem 0;
            overflow-y: auto;
        }

        .nav-section {
            margin-bottom: 1.5rem;
        }

        .nav-section-title {
            padding: 0.5rem 1.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--color-text-muted);
            letter-spacing: 0.05em;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: var(--color-text-secondary);
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border-left: 3px solid transparent;
        }

        .nav-item:hover {
            background-color: var(--color-bg-hover);
            color: var(--color-text-primary);
        }

        .nav-item.active {
            background-color: var(--color-bg-hover);
            color: var(--color-primary);
            border-left-color: var(--color-primary);
            font-weight: 500;
        }

        .nav-icon {
            margin-right: 0.75rem;
            font-size: 1.2rem;
        }

        .sidebar-footer {
            padding: 1rem;
            border-top: 1px solid var(--color-border);
        }

        .theme-toggle, .account-btn {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 0.75rem 1rem;
            background: transparent;
            border: 1px solid var(--color-border);
            border-radius: 8px;
            color: var(--color-text-primary);
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .theme-toggle:hover, .account-btn:hover {
            background-color: var(--color-bg-hover);
            border-color: var(--color-primary);
        }

        /* Account Settings Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.55);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.open {
            display: flex;
        }
        .modal-box {
            background: var(--color-bg-surface);
            border: 1px solid var(--color-border);
            border-radius: 14px;
            padding: 2rem;
            width: 100%;
            max-width: 460px;
            box-shadow: var(--shadow-lg);
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-box h2 {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .modal-section {
            border: 1px solid var(--color-border);
            border-radius: 10px;
            padding: 1.25rem;
            margin-bottom: 1rem;
        }
        .modal-section h3 {
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--color-text-primary);
        }
        .modal-section input {
            width: 100%;
            padding: 0.6rem 0.85rem;
            border: 1px solid var(--color-border);
            border-radius: 8px;
            background: var(--color-bg-primary);
            color: var(--color-text-primary);
            font-size: 0.9rem;
            margin-bottom: 0.6rem;
            box-sizing: border-box;
        }
        .modal-section input:focus {
            outline: none;
            border-color: var(--color-primary);
        }
        .modal-btn {
            padding: 0.55rem 1.2rem;
            border: none;
            border-radius: 7px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        .modal-btn-primary {
            background: var(--color-primary);
            color: white;
        }
        .modal-btn-primary:hover { background: var(--color-primary-hover); }
        .modal-btn-danger {
            background: var(--color-danger);
            color: white;
        }
        .modal-btn-danger:hover { opacity: 0.85; }
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
        }

        .content-header {
            margin-bottom: 2rem;
        }

        .content-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .content-header p {
            color: var(--color-text-secondary);
            font-size: 1rem;
        }

        /* Bento Grid Layout */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .widget {
            background-color: var(--color-bg-surface);
            border: 1px solid var(--color-border);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
        }

        .widget:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .widget-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .widget-title {
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .widget-icon {
            font-size: 1.3rem;
        }

        .widget-action {
            background: var(--color-primary, #007bff); /* Nutzt deine Primärfarbe oder ein sattes Blau */
            border: none;
            border-radius: 8px;                    /* Weiche, moderne Kanten */
            color: white;                          /* Hoher Kontrast für Lesbarkeit */
            cursor: pointer;
            padding: 0.6rem 1.2rem;                /* Mehr "Klickfläche" */
            font-weight: 600;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Dezenter Schatten für Tiefe */
            transition: all 0.3s ease;             /* Geschmeidige Übergänge für alle Eigenschaften */
        }

        /* Interaktion: Was passiert beim Drüberfahren? */
        .widget-action:hover {
            background: var(--color-primary-dark, #0056b3); 
            transform: translateY(-2px);           /* Kleiner "Hover-Lift" Effekt */
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
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
        }

        .file-item:hover {
            background-color: var(--color-border);
        }

        .file-icon {
            font-size: 1.5rem;
        }

        .file-info {
            flex: 1;
        }

        .file-name {
            font-weight: 500;
            font-size: 0.9rem;
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

        input[type="text"], input[type="number"], select {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid var(--color-border);
            border-radius: 6px;
            background-color: var(--color-bg-primary);
            color: var(--color-text-primary);
            font-size: 0.9rem;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--color-primary);
        }

        .btn-primary {
            padding: 0.75rem 1.5rem;
            background-color: var(--color-primary);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background-color: var(--color-primary-hover);
        }

        .btn-icon {
            padding: 0.5rem;
            background: transparent;
            border: 1px solid var(--color-border);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-icon:hover {
            background-color: var(--color-bg-hover);
            border-color: var(--color-primary);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -260px;
                z-index: 1000;
                height: 100vh;
            }

            .sidebar.open {
                transform: translateX(260px);
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
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
            background-color: rgba(220, 53, 69, 0.1);
            border-color: rgba(220, 53, 69, 0.4);
        }

        .tt-subject-cell.has-exam.today-col {
            background-color: rgba(220, 53, 69, 0.15);
            border-color: var(--color-danger);
        }

        .tt-exam-badge {
            font-size: 0.62rem;
            background-color: var(--color-danger);
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
            padding: 0.75rem 1.5rem;
            background-color: transparent;
            color: var(--color-text-secondary);
            border: 1px solid var(--color-border);
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-secondary:hover {
            background-color: var(--color-bg-hover);
            color: var(--color-text-primary);
            border-color: var(--color-text-secondary);
        }

        /* Vergangene Klausur */
        .exam-past { opacity: 0.55; }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--color-bg-primary);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--color-border);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--color-text-muted);
        }
    </style>
</head>
<body>
    <div class="app-container">
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
                        <span>Übersicht</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Module</div>
                    <a class="nav-item" data-view="timetable">
                        <span class="nav-icon">📅</span>
                        <span>Stundenplan</span>
                    </a>
                    <a class="nav-item" data-view="grades">
                        <span class="nav-icon">📝</span>
                        <span>Noten</span>
                    </a>
                    <a class="nav-item" data-view="todos">
                        <span class="nav-icon">✅</span>
                        <span>To-Dos</span>
                    </a>
                    <a class="nav-item" data-view="exams">
                        <span class="nav-icon">📝</span>
                        <span>Klassenarbeiten</span>
                    </a>
                    <a class="nav-item" data-view="flashcards">
                        <span class="nav-icon">🎴</span>
                        <span>Karteikarten</span>
                    </a>
                    <a class="nav-item" data-view="files">
                        <span class="nav-icon">📁</span>
                        <span>Dateien</span>
                    </a>
                    <a class="nav-item" data-view="admin-messages">
                        <span class="nav-icon">💬</span>
                        <span>Admin Nachrichten</span>
                    </a>
                    <a class="nav-item" data-view="admin">
                        <span class="nav-icon">⚙️</span>
                        <span>Admin Panel</span>
                    </a>
                </div>
            </nav>

            <div class="sidebar-footer">
                <button class="theme-toggle" id="themeToggle">
                    <span id="themeIcon">🌙</span>
                    <span id="themeText" style="margin-left: 0.5rem;">Dark Mode</span>
                </button>
                <button class="account-btn" onclick="openAccountModal()">
                    <span>👤</span>
                    <span style="margin-left: 0.5rem;">Account</span>
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div id="contentArea">
                <?php include __DIR__ . '/tabs/overview.php'; ?>
                <?php include __DIR__ . '/tabs/timetable.php'; ?>
                <?php include __DIR__ . '/tabs/grades.php'; ?>
                <?php include __DIR__ . '/tabs/todos.php'; ?>
                <?php include __DIR__ . '/tabs/exams.php'; ?>
                <?php include __DIR__ . '/tabs/flashcards.php'; ?>
                <?php include __DIR__ . '/tabs/files.php'; ?>
                <?php include __DIR__ . '/tabs/admin-messages.php'; ?>
                <?php include __DIR__ . '/tabs/admin.php'; ?>
            </div>
        </main>
    </div>

    <!-- ===== ACCOUNT SETTINGS MODAL ===== -->
    <div class="modal-overlay" id="accountModal">
        <div class="modal-box">
            <h2>&#128100; Account-Einstellungen</h2>

            <!-- Benutzername ändern -->
            <div class="modal-section">
                <h3>&#9999;&#65039; Benutzername ändern</h3>
                <input type="text" id="newUsername" placeholder="Neuer Benutzername">
                <div>
                    <button class="modal-btn modal-btn-primary" onclick="changeUsername()">Speichern</button>
                </div>
                <div class="modal-msg" id="msgUsername"></div>
            </div>

            <!-- Passwort ändern -->
            <div class="modal-section">
                <h3>&#128274; Passwort ändern</h3>
                <input type="password" id="oldPassword" placeholder="Altes Passwort">
                <input type="password" id="newPassword" placeholder="Neues Passwort">
                <input type="password" id="newPassword2" placeholder="Neues Passwort wiederholen">
                <div>
                    <button class="modal-btn modal-btn-primary" onclick="changePassword()">Speichern</button>
                </div>
                <div class="modal-msg" id="msgPassword"></div>
            </div>

            <!-- E-Mail ändern -->
            <div class="modal-section">
                <h3>&#128231; E-Mail-Adresse ändern</h3>
                <input type="email" id="newEmail" placeholder="Neue E-Mail-Adresse">
                <div>
                    <button class="modal-btn modal-btn-primary" onclick="changeEmail()">Speichern</button>
                </div>
                <div class="modal-msg" id="msgEmail"></div>
            </div>

            <!-- Account löschen -->
            <div class="modal-section" style="border-color: var(--color-danger);">
                <h3 style="color: var(--color-danger);">&#128465;&#65039; Account löschen</h3>
                <p style="font-size:0.85rem;color:var(--color-text-secondary);margin-bottom:0.75rem;">
                    Diese Aktion ist <strong>unwiderruflich</strong>. Alle deine Daten werden gelöscht.
                </p>
                <input type="password" id="deletePassword" placeholder="Passwort zur Bestätigung">
                <div>
                    <button class="modal-btn modal-btn-danger" onclick="deleteAccount()">Account endgültig löschen</button>
                </div>
                <div class="modal-msg" id="msgDelete"></div>
            </div>

            <div class="modal-footer">
                <button class="modal-btn modal-btn-close" onclick="closeAccountModal()">Schließen</button>
            </div>
        </div>
    </div>

    <script>
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

                if (viewId === 'overview') {
                    renderOverview();
                }
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
            } catch {
                todosData = [];
                renderTodosUI();
                renderOverviewTodos();
            }
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
                list.innerHTML = '<p style="color:var(--color-text-muted);text-align:center;padding:1.5rem;">Lädt…</p>';
                return;
            }

            let filtered = todosData;
            if (todoFilter === 'open') filtered = todosData.filter(t => !t.done);
            if (todoFilter === 'done') filtered = todosData.filter(t =>  t.done);

            if (!filtered.length) {
                const msg = todoFilter === 'done' ? 'Noch nichts erledigt.' : 'Keine Aufgaben &#x2014; super! 🎉';
                list.innerHTML = `<p style="color:var(--color-text-muted);text-align:center;padding:1.5rem;">${msg}</p>`;
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
                    <button class="btn-icon" onclick="deleteTodoById('${escapeHtml(todo.id)}')" title="Löschen">🗑️</button>
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
                }
            } catch { /* Server nicht erreichbar */ }
        }


        // Flashcard Functionality
        let currentCard = 0;
        const flashcards = [
            { question: 'Was ist ein Automat?', answer: 'Ein abstraktes Modell eines Rechners mit endlich vielen Zuständen' },
            { question: 'Was ist eine reguläre Sprache?', answer: 'Eine Sprache, die von einem endlichen Automaten erkannt werden kann' },
            { question: 'Was ist der Unterschied zwischen DFA und NFA?', answer: 'DFA ist deterministisch, NFA nicht-deterministisch' },
            { question: 'Was ist die Chomsky-Hierarchie?', answer: 'Eine Klassifizierung formaler Sprachen in vier Typen' },
            { question: 'Was ist ein Pumping-Lemma?', answer: 'Ein Hilfsmittel zum Beweis, dass eine Sprache nicht regulär ist' }
        ];

        function flipCard(cardId) {
            const card = document.getElementById(cardId);
            if (card) {
                card.classList.toggle('flipped');
            }
        }

        function nextCard() {
            currentCard = (currentCard + 1) % flashcards.length;
            updateFlashcard();
            updateCardCounter();
        }

        function previousCard() {
            currentCard = (currentCard - 1 + flashcards.length) % flashcards.length;
            updateFlashcard();
            updateCardCounter();
        }

        function updateCardCounter() {
            const counter = document.getElementById('cardCounter');
            if (counter) {
                counter.textContent = `Karte ${currentCard + 1} von ${flashcards.length}`;
            }
        }

        function updateFlashcard() {
            const card = flashcards[currentCard];
            const flashcardElement = document.getElementById('flashcard');
            const flashcardDetail = document.getElementById('flashcardDetail');
            
            // Remove flipped state
            if (flashcardElement) flashcardElement.classList.remove('flipped');
            if (flashcardDetail) flashcardDetail.classList.remove('flipped');
            
            // Update overview card
            if (flashcardElement) {
                const front = flashcardElement.querySelector('.flashcard-front');
                const back = flashcardElement.querySelector('.flashcard-back');
                front.innerHTML = `<p><strong>Frage:</strong> ${card.question}</p>`;
                back.innerHTML = `<p>${card.answer}</p>`;
            }
            
            // Update detail view card
            if (flashcardDetail) {
                const frontDetail = flashcardDetail.querySelector('.flashcard-front');
                const backDetail = flashcardDetail.querySelector('.flashcard-back');
                frontDetail.innerHTML = `<p><strong>Frage:</strong> ${card.question}</p>`;
                backDetail.innerHTML = `<p>${card.answer}</p>`;
            }
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
            monday: 'Montag', tuesday: 'Dienstag', wednesday: 'Mittwoch',
            thursday: 'Donnerstag', friday: 'Freitag'
        };
        const TT_DAY_INDEX = { monday: 1, tuesday: 2, wednesday: 3, thursday: 4, friday: 5 };

        const DEFAULT_TIMES = {
            1: '07:45', 2: '08:30', 3: '09:15', 4: '10:15', 5: '11:00',
            6: '11:45', 7: '12:45', 8: '13:30', 9: '14:15', 10: '15:00'
        };

        let timetableData  = JSON.parse(localStorage.getItem('timetable_data'))  || {};
        let timetableTimes = JSON.parse(localStorage.getItem('timetable_times')) || { ...DEFAULT_TIMES };
        let homework       = JSON.parse(localStorage.getItem('homework_data'))   || {};
        let exams          = JSON.parse(localStorage.getItem('exams_data'))      || [];

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
                    if (exam.period) {
                        const key = `${dayKey}-${exam.period}`;
                        if (!highlights[key]) highlights[key] = [];
                        highlights[key].push(exam);
                    }
                }
            });
            return highlights;
        }

        function renderTimetableView() {
            const grid = document.getElementById('timetableGrid');
            if (!grid) return;
            const maxPeriod     = getMaxPeriod();
            const todayKey      = getCurrentDayKey();
            const examHighlights = getExamHighlights();

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
                    const hasExam  = !!examHighlights[examKey];
                    const examList = hasExam ? examHighlights[examKey] : [];

                    let cls = 'tt-subject-cell';
                    if (isToday) cls += ' today-col';
                    if (hasExam) cls += ' has-exam';

                    html += `<div class="${cls}">`;
                    if (subject) {
                        html += `<span class="tt-subject-name">${escapeHtml(subject)}</span>`;
                        if (room) html += `<span class="tt-room">${escapeHtml(room)}</span>`;
                    }
                    examList.forEach(ex => {
                        html += `<span class="tt-exam-badge">📝 ${escapeHtml(ex.subject)}</span>`;
                    });
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
                        <input type="text" id="tt_${day}_${p}_subject" value="${escapeHtml(subject)}" placeholder="Fach">
                        <input type="text" id="tt_${day}_${p}_room"    value="${escapeHtml(room)}"    placeholder="Raum">
                    </div></td>`;
                });
                html += '</tr>';
            }
            html += '</tbody></table>';
            editor.innerHTML = html;
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

        function saveTimetable() {
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

            cancelTimetableEdit();
            renderTimetableView();
        }

        // ===== HAUSAUFGABEN =====
        function renderHomework() {
            const grid = document.getElementById('homeworkGrid');
            if (!grid) return;
            const todayKey = getCurrentDayKey();
            let html = '<div class="tt-homework-grid">';
            TT_DAYS.forEach(day => {
                const isToday = day === todayKey;
                const dayHw   = homework[day] || [];
                html += `<div class="tt-hw-day ${isToday ? 'today' : ''}">
                    <div class="tt-hw-day-title">${TT_DAY_NAMES[day]}</div>`;
                dayHw.forEach((hw, i) => {
                    html += `<div class="tt-hw-item">
                        <button class="tt-hw-delete" onclick="deleteHomework('${day}',${i})" title="Löschen">✕</button>
                        <span>${escapeHtml(hw)}</span>
                    </div>`;
                });
                html += `<div class="tt-hw-input-row">
                    <input class="tt-hw-input" type="text" id="hwInput_${day}" placeholder="Hausaufgabe..."
                           onkeydown="if(event.key==='Enter') addHomework('${day}')">
                    <button class="tt-hw-add-btn" onclick="addHomework('${day}')">+</button>
                </div></div>`;
            });
            html += '</div>';
            grid.innerHTML = html;
        }

        function addHomework(day) {
            const input = document.getElementById(`hwInput_${day}`);
            if (!input || !input.value.trim()) return;
            if (!homework[day]) homework[day] = [];
            homework[day].push(input.value.trim());
            localStorage.setItem('homework_data', JSON.stringify(homework));
            renderHomework();
        }

        function deleteHomework(day, index) {
            if (!homework[day]) return;
            homework[day].splice(index, 1);
            localStorage.setItem('homework_data', JSON.stringify(homework));
            renderHomework();
        }

        // ===== KLASSENARBEITEN =====
        function addExam() {
            const subjectEl = document.getElementById('examSubject');
            const dateEl    = document.getElementById('examDate');
            const topicEl   = document.getElementById('examTopic');
            const periodEl  = document.getElementById('examPeriod');

            if (!subjectEl.value.trim() || !dateEl.value) return;

            exams.push({
                subject: subjectEl.value.trim(),
                date:    dateEl.value,
                topic:   topicEl  ? topicEl.value.trim()            : '',
                period:  periodEl ? (parseInt(periodEl.value) || null) : null
            });
            localStorage.setItem('exams_data', JSON.stringify(exams));

            subjectEl.value = '';
            dateEl.value    = '';
            if (topicEl)  topicEl.value  = '';
            if (periodEl) periodEl.value = '';

            renderExams();
        }

        function deleteExam(index) {
            exams.splice(index, 1);
            localStorage.setItem('exams_data', JSON.stringify(exams));
            renderExams();
        }

        function renderExams() {
            const list = document.getElementById('examsList');
            if (!list) return;
            if (!exams.length) {
                list.innerHTML = '<p style="color:var(--color-text-muted);text-align:center;padding:1rem;">Keine Klassenarbeiten eingetragen</p>';
                return;
            }
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const sorted = [...exams]
                .map((e, i) => ({ ...e, origIndex: i }))
                .sort((a, b) => new Date(a.date) - new Date(b.date));

            list.innerHTML = sorted.map(exam => {
                const d       = new Date(exam.date + 'T00:00:00');
                const dateStr = d.toLocaleDateString('de-DE', { weekday: 'short', day: '2-digit', month: '2-digit', year: 'numeric' });
                const isPast  = d < today;
                const period  = exam.period ? ` · ${exam.period}. Stunde` : '';
                const topic   = exam.topic  ? ` · ${escapeHtml(exam.topic)}` : '';
                const badge   = isPast
                    ? '<span style="font-size:0.8rem;color:var(--color-text-muted);">vergangen</span>'
                    : '<span style="font-size:0.8rem;color:var(--color-warning);">⏳ bald</span>';

                return `<div class="grade-item ${isPast ? 'exam-past' : ''}">
                    <div>
                        <div class="grade-subject">${escapeHtml(exam.subject)}</div>
                        <div style="font-size:0.8rem;color:var(--color-text-muted);">${dateStr}${period}${topic}</div>
                    </div>
                    <div style="display:flex;align-items:center;gap:0.5rem;">
                        ${badge}
                        <button class="btn-icon" onclick="deleteExam(${exam.origIndex})" title="Löschen">🗑️</button>
                    </div>
                </div>`;
            }).join('');
        }

        // ===== ÜBERSICHT VORSCHAUEN =====
        const CURRENT_USER_ID = "<?php echo htmlspecialchars($_SESSION['user_id']); ?>";

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

            container.innerHTML = entries.slice(0, 5).map(([period, cell]) => `
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

        async function renderOverviewGrades() {
            const container = document.getElementById('overviewGrades');
            if (!container) return;
            container.innerHTML = '<p style="color:var(--color-text-muted);text-align:center;padding:0.5rem;">Lädt…</p>';
            try {
                const res = await fetch(`http://localhost:8000/grades/${CURRENT_USER_ID}`);
                if (!res.ok) throw new Error();
                const grades = await res.json();
                if (!grades.length) {
                    container.innerHTML = '<p style="color:var(--color-text-muted);text-align:center;padding:0.5rem;">Noch keine Noten eingetragen</p>';
                    return;
                }
                container.innerHTML = grades.slice(-3).reverse().map(g => `
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
                const res = await fetch(`http://localhost:8000/files/${CURRENT_USER_ID}`);
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

        function renderOverview() {
            renderOverviewTimetable();
            renderOverviewTodos();
            renderOverviewExams();
            renderOverviewGrades();
            renderOverviewFiles();
            renderOverviewMessages();
        }

        // ===== INITIALISIERUNG =====
        renderTimetableView();
        renderHomework();
        renderExams();
        loadGrades();
        loadTodos();
        renderOverview();

        // ===== ACCOUNT SETTINGS MODAL =====

        function openAccountModal() {
            document.getElementById('accountModal').classList.add('open');
        }

        function closeAccountModal() {
            document.getElementById('accountModal').classList.remove('open');
            ['msgUsername','msgPassword','msgEmail','msgDelete'].forEach(id => {
                const el = document.getElementById(id);
                if (el) { el.textContent = ''; el.className = 'modal-msg'; }
            });
        }

        document.getElementById('accountModal').addEventListener('click', function(e) {
            if (e.target === this) closeAccountModal();
        });

        function setMsg(id, text, type) {
            const el = document.getElementById(id);
            el.textContent = text;
            el.className = 'modal-msg ' + type;
        }

        async function changeUsername() {
            const val = document.getElementById('newUsername').value.trim();
            if (!val) return setMsg('msgUsername', 'Bitte einen Benutzernamen eingeben.', 'error');
            try {
                const res = await fetch(`http://localhost:8000/auth/change-username/${CURRENT_USER_ID}`, {
                    method: 'PUT',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ new_username: val })
                });
                const data = await res.json();
                if (res.ok) {
                    setMsg('msgUsername', '✅ ' + data.message, 'success');
                    document.getElementById('newUsername').value = '';
                } else {
                    setMsg('msgUsername', '❌ ' + (data.detail || 'Fehler'), 'error');
                }
            } catch { setMsg('msgUsername', '❌ Server nicht erreichbar.', 'error'); }
        }

        async function changePassword() {
            const oldPw  = document.getElementById('oldPassword').value;
            const newPw  = document.getElementById('newPassword').value;
            const newPw2 = document.getElementById('newPassword2').value;
            if (!oldPw || !newPw) return setMsg('msgPassword', 'Bitte alle Felder ausfüllen.', 'error');
            if (newPw !== newPw2) return setMsg('msgPassword', '❌ Passwörter stimmen nicht überein.', 'error');
            if (newPw.length < 6) return setMsg('msgPassword', '❌ Passwort muss mindestens 6 Zeichen haben.', 'error');
            try {
                const res = await fetch(`http://localhost:8000/auth/change-password/${CURRENT_USER_ID}`, {
                    method: 'PUT',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ old_password: oldPw, new_password: newPw })
                });
                const data = await res.json();
                if (res.ok) {
                    setMsg('msgPassword', '✅ ' + data.message, 'success');
                    document.getElementById('oldPassword').value = '';
                    document.getElementById('newPassword').value = '';
                    document.getElementById('newPassword2').value = '';
                } else {
                    setMsg('msgPassword', '❌ ' + (data.detail || 'Fehler'), 'error');
                }
            } catch { setMsg('msgPassword', '❌ Server nicht erreichbar.', 'error'); }
        }

        async function changeEmail() {
            const val = document.getElementById('newEmail').value.trim();
            if (!val || !val.includes('@')) return setMsg('msgEmail', 'Bitte eine gültige E-Mail eingeben.', 'error');
            try {
                const res = await fetch(`http://localhost:8000/auth/change-email/${CURRENT_USER_ID}`, {
                    method: 'PUT',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ new_email: val })
                });
                const data = await res.json();
                if (res.ok) {
                    setMsg('msgEmail', '✅ ' + data.message, 'success');
                    document.getElementById('newEmail').value = '';
                } else {
                    setMsg('msgEmail', '❌ ' + (data.detail || 'Fehler'), 'error');
                }
            } catch { setMsg('msgEmail', '❌ Server nicht erreichbar.', 'error'); }
        }

        async function deleteAccount() {
            const pw = document.getElementById('deletePassword').value;
            if (!pw) return setMsg('msgDelete', 'Bitte dein Passwort eingeben.', 'error');
            if (!confirm('Bist du sicher? Diese Aktion kann NICHT rückgängig gemacht werden!')) return;
            try {
                const res = await fetch(`http://localhost:8000/auth/delete-account/${CURRENT_USER_ID}`, {
                    method: 'DELETE',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ password: pw })
                });
                const data = await res.json();
                if (res.ok) {
                    alert('Account gelöscht. Du wirst ausgeloggt.');
                    window.location.href = 'auth/logout.php';
                } else {
                    setMsg('msgDelete', '❌ ' + (data.detail || 'Fehler'), 'error');
                }
            } catch { setMsg('msgDelete', '❌ Server nicht erreichbar.', 'error'); }
        }

        // ===== NOTEN (0-15 Punkte) API =====
        function getGradeClass(value) {
            if (value >= 12) return '';
            if (value >= 7)  return 'warning';
            return 'danger';
        }

        async function loadGrades() {
            const list = document.getElementById('gradesList');
            if (!list) return;
            try {
                const res = await fetch(`http://localhost:8000/grades/${CURRENT_USER_ID}`);
                if (!res.ok) return;
                const grades = await res.json();
                if (!grades.length) {
                    list.innerHTML = '<p style="color:var(--color-text-muted);text-align:center;padding:1rem;">Noch keine Noten eingetragen</p>';
                    return;
                }
                list.innerHTML = grades.map(g => `
                    <div class="grade-item" id="grade-${g.id}">
                        <div>
                            <span class="grade-subject">${escapeHtml(g.subject)}</span>
                            ${g.description ? `<div style="font-size:0.8rem;color:var(--color-text-muted);">${escapeHtml(g.description)}</div>` : ''}
                        </div>
                        <div style="display:flex;align-items:center;gap:0.5rem;">
                            <span class="grade-value ${getGradeClass(g.value)}">${g.value} P</span>
                            <button class="btn-icon" onclick="removeGrade('${g.id}')" title="Löschen">🗑️</button>
                        </div>
                    </div>
                `).join('');
            } catch { /* Server nicht erreichbar */ }
        }

        async function addGrade() {
            const subjectInput = document.getElementById('gradeSubject');
            const valueInput   = document.getElementById('gradeValue');
            const descInput    = document.getElementById('gradeDescription');
            if (!subjectInput.value.trim() || valueInput.value === '') return;
            const value = parseFloat(valueInput.value);
            if (isNaN(value) || value < 0 || value > 15) return;
            try {
                const res = await fetch(`http://localhost:8000/grades/${CURRENT_USER_ID}`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        subject: subjectInput.value.trim(),
                        value: value,
                        description: descInput ? descInput.value.trim() : ''
                    })
                });
                if (res.ok) {
                    subjectInput.value = '';
                    valueInput.value   = '';
                    if (descInput) descInput.value = '';
                    loadGrades();
                }
            } catch { /* Server nicht erreichbar */ }
        }

        async function removeGrade(gradeId) {
            if (!confirm('Note wirklich löschen?')) return;
            try {
                const res = await fetch(`http://localhost:8000/grades/${CURRENT_USER_ID}/${gradeId}`, {
                    method: 'DELETE'
                });
                if (res.ok) {
                    const el = document.getElementById(`grade-${gradeId}`);
                    if (el) el.remove();
                }
            } catch { /* Server nicht erreichbar */ }
        }
    </script>
</body>
</html>

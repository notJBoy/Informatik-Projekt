# backend.md

## Kurzueberblick
Das Backend basiert auf FastAPI in `backend.py` und nutzt SQLite (`learnhub.db`) als Datenbank.
Das PHP-Frontend ruft entweder direkt FastAPI-Endpunkte auf oder nutzt lokale PHP-Proxy-Endpunkte, die serverseitig an FastAPI weiterleiten.

## Aufbau
- API-Server: FastAPI-App in `backend.py`
- Datenhaltung: SQLite mit Tabellen fuer User, To-dos, Hausaufgaben, Exams, Noten, Kalender, Dateien, Flashcards
- Uploads: Dateispeicher unter `uploads/`
- Konfiguration: `.env` wird beim Start geladen (`.venv/.env` und `.env`)

## Auth & Account-Flow
- Login/Registrierung laufen ueber `/auth/...` Endpunkte.
- Verifizierungscodes (z. B. E-Mail-Aenderung, Account-Loeschung) werden in `email_verifications` gespeichert.
- Codes haben eine gueltige Laufzeit und werden serverseitig geprueft und verbraucht.

## Mailversand
- Verifizierungsmails werden in `send_verification_email()` versendet.
- Aktuell SMTP-basiert (WEB.DE-Konfiguration via ENV):
  - `LEARNHUB_SMTP_HOST`
  - `LEARNHUB_SMTP_PORT`
  - `LEARNHUB_SMTP_USE_SSL`
  - `LEARNHUB_SMTP_USE_STARTTLS`
  - `LEARNHUB_SMTP_EMAIL`
  - `LEARNHUB_SMTP_PASSWORD`
  - `LEARNHUB_SMTP_SENDER_NAME`

## Frontend-Integration
- Fuer stabile Requests in Remote-Umgebungen wurden kritische Flows zusaetzlich ueber same-origin PHP-Proxys umgesetzt:
  - E-Mail-Aenderung: `auth/change_email_request.php`, `auth/change_email_confirm.php`
  - Account-Loeschung: `auth/delete_account_request_code.php`, `auth/delete_account_confirm.php`

## Start lokal
1. Python-Abhaengigkeiten installieren (`pip install -r requirements.txt`)
2. Backend starten (`python backend.py`)
3. PHP-Frontend starten (`php -S 0.0.0.0:8080`)

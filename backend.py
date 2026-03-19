# =========================================
# LearnHub Backend
# Autor: Backend-Team
# Technologie: FastAPI + SQLite
# =========================================
from fastapi import UploadFile, File, Form
from fastapi.responses import FileResponse
from fastapi import FastAPI, HTTPException, Depends
from pydantic import BaseModel
from typing import List, Optional
from datetime import datetime, timedelta
import sqlite3
import uuid
import hashlib
import os
import re
import smtplib
import secrets
import ssl
import json
from email.message import EmailMessage

UPLOAD_DIR = "uploads"
os.makedirs(UPLOAD_DIR, exist_ok=True)


def load_local_env_files():
    env_paths = [".venv/.env", ".env"]
    for env_path in env_paths:
        if not os.path.exists(env_path):
            continue
        with open(env_path, "r", encoding="utf-8") as f:
            for raw_line in f:
                line = raw_line.strip()
                if not line or line.startswith("#") or "=" not in line:
                    continue
                key, value = line.split("=", 1)
                key = key.strip()
                value = value.strip().strip('"').strip("'")
                if key and key not in os.environ:
                    os.environ[key] = value


load_local_env_files()


# =========================================
# APP INITIALISIERUNG
# =========================================

app = FastAPI(
    title="LearnHub API",
    description="Backend für die LearnHub Lernplattform",
    version="1.0.0"
)

# CORS erlauben, damit das PHP‑Frontend (oder andere Hosts) die API ansprechen kann
# während der Entwicklung ist "*" ok, später enger einschränken.
from fastapi.middleware.cors import CORSMiddleware

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],              # Alternativ: ["http://localhost:8080"]
    allow_methods=["*"],
    allow_headers=["*"],
)

DB_NAME = "learnhub.db"
SMTP_HOST = "smtp.gmail.com"
SMTP_PORT = 465
VERIFICATION_TTL_MINUTES = 10


# =========================================
# DATABASE SETUP
# =========================================

def get_db():
    conn = sqlite3.connect(DB_NAME)
    conn.row_factory = sqlite3.Row
    return conn


def init_db():
    db = get_db()
    cursor = db.cursor()

    # FILES
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS files (
        id TEXT PRIMARY KEY,
        user_id TEXT,
        filename TEXT,
        original_name TEXT,
        subject TEXT,
        uploaded_at TEXT
    )
    """)

    
    # USERS
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS users (
        id TEXT PRIMARY KEY,
        username TEXT UNIQUE,
        email TEXT,
        password TEXT,
        role TEXT,
        created_at TEXT
    )
    """)

    # TODOS
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS todos (
        id TEXT PRIMARY KEY,
        user_id TEXT,
        title TEXT,
        subject TEXT,
        due_date TEXT,
        priority TEXT,
        done INTEGER
    )
    """)

    # HOMEWORK
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS homework_entries (
        id TEXT PRIMARY KEY,
        user_id TEXT,
        day TEXT,
        period INTEGER,
        title TEXT,
        created_at TEXT
    )
    """)

    # EXAMS
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS exams (
        id TEXT PRIMARY KEY,
        user_id TEXT,
        subject TEXT,
        date TEXT,
        topic TEXT,
        period INTEGER,
        grade REAL,
        created_at TEXT
    )
    """)

    # CALENDAR EXTRAS
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS calendar_extras (
        id TEXT PRIMARY KEY,
        user_id TEXT,
        title TEXT,
        date TEXT,
        description TEXT,
        created_at TEXT
    )
    """)

    # GRADES
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS grades (
        id TEXT PRIMARY KEY,
        user_id TEXT,
        subject TEXT,
        value REAL,
        description TEXT,
        date TEXT,
        weight REAL DEFAULT 1
    )
    """)

    # Add weight column if it doesn't exist yet (migration path for existing DBs)
    try:
        cursor.execute("ALTER TABLE grades ADD COLUMN weight REAL DEFAULT 1")
    except Exception:
        pass  # column already present

    # Add grade column to exams if it doesn't exist yet (migration path for existing DBs)
    try:
        cursor.execute("ALTER TABLE exams ADD COLUMN grade REAL")
    except Exception:
        pass  # column already present

    # TIMETABLE
    # original structure contained only day/time/subject; we extend with period and room
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS timetable (
        id TEXT PRIMARY KEY,
        user_id TEXT,
        day TEXT,
        period INTEGER,
        time TEXT,
        subject TEXT,
        room TEXT
    )
    """)
    # ensure new columns exist in older databases
    try:
        cursor.execute("ALTER TABLE timetable ADD COLUMN period INTEGER")
    except Exception:
        pass  # column already present
    try:
        cursor.execute("ALTER TABLE timetable ADD COLUMN room TEXT")
    except Exception:
        pass  # column already present

    # Repair rows that were written with a wrong column order.
    # Old buggy writes stored values as:
    # - time   <- period
    # - subject<- time
    # - period <- subject
    cursor.execute("SELECT id, time, subject, period FROM timetable")
    timetable_rows = cursor.fetchall()

    def _parse_period(value):
        if value is None:
            return None
        try:
            p = int(str(value).strip())
            return p if 1 <= p <= 10 else None
        except Exception:
            return None

    def _looks_like_time(value):
        if value is None:
            return False
        return bool(re.match(r"^\d{1,2}:\d{2}$", str(value).strip()))

    for row in timetable_rows:
        existing_period = _parse_period(row["period"])
        time_as_period = _parse_period(row["time"])

        if existing_period is not None:
            continue
        if time_as_period is None:
            continue
        if not _looks_like_time(row["subject"]):
            continue

        fixed_subject = "" if row["period"] is None else str(row["period"]).strip()
        fixed_time = str(row["subject"]).strip()

        cursor.execute(
            """
            UPDATE timetable
            SET time=?, subject=?, period=?
            WHERE id=?
            """,
            (fixed_time, fixed_subject, time_as_period, row["id"])
        )

    # separate table for storing period times; this allows the user to configure slot times even if
    # no classes are set in that period
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS timetable_times (
        user_id TEXT,
        period INTEGER,
        time TEXT,
        PRIMARY KEY(user_id, period)
    )
    """)

    # FLASHCARD DECKS
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS flashcard_decks (
        id TEXT PRIMARY KEY,
        user_id TEXT,
        name TEXT,
        subject TEXT,
        description TEXT,
        public INTEGER,
        created_at TEXT
    )
    """)

    # FLASHCARDS
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS flashcards (
        id TEXT PRIMARY KEY,
        user_id TEXT,
        subject TEXT,
        front TEXT,
        back TEXT,
        public INTEGER
    )
    """)

    # Add deck_id column if it doesn't exist yet
    try:
        cursor.execute("ALTER TABLE flashcards ADD COLUMN deck_id TEXT")
    except Exception:
        pass  # Column already exists

    # E-MAIL VERIFICATION CODES
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS email_verifications (
        id TEXT PRIMARY KEY,
        user_id TEXT,
        email TEXT,
        purpose TEXT,
        code_hash TEXT,
        payload TEXT,
        expires_at TEXT,
        created_at TEXT
    )
    """)

    # LOGIN ATTEMPTS (for security/admin analytics)
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS login_attempts (
        id TEXT PRIMARY KEY,
        user_id TEXT,
        username TEXT,
        success INTEGER,
        created_at TEXT
    )
    """)

    # USER ACTIVITY (lightweight event log for admin analytics)
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS user_activity (
        id TEXT PRIMARY KEY,
        user_id TEXT,
        event_type TEXT,
        created_at TEXT
    )
    """)

    # ADMIN MESSAGES
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS admin_messages (
        id TEXT PRIMARY KEY,
        sender_user_id TEXT,
        recipient_user_id TEXT,
        title TEXT,
        body TEXT,
        created_at TEXT
    )
    """)

    # SUBJECTS (Fächer)
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS subjects (
        id TEXT PRIMARY KEY,
        user_id TEXT,
        name TEXT,
        color TEXT,
        created_at TEXT
    )
    """)

    # Ensure there is at least one admin in existing databases.
    cursor.execute("SELECT COUNT(*) AS total FROM users WHERE lower(role)='admin'")
    admin_count = cursor.fetchone()["total"]
    if admin_count == 0:
        cursor.execute("SELECT id FROM users ORDER BY created_at ASC LIMIT 1")
        first_user = cursor.fetchone()
        if first_user:
            cursor.execute("UPDATE users SET role='admin' WHERE id=?", (first_user["id"],))

    db.commit()
    db.close()


init_db()

# =========================================
# HILFSFUNKTIONEN
# =========================================

def hash_password(password: str) -> str:
    return hashlib.sha256(password.encode()).hexdigest()


def generate_id() -> str:
    return str(uuid.uuid4())


def is_valid_email(email: str) -> bool:
    if not email or "@" not in email:
        return False
    local, _, domain = email.partition("@")
    return bool(local and domain and "." in domain)


def hash_verification_code(code: str) -> str:
    return hashlib.sha256(code.encode()).hexdigest()


def log_login_attempt(db, username: str, success: bool, user_id: Optional[str] = None):
    cursor = db.cursor()
    cursor.execute(
        """
        INSERT INTO login_attempts VALUES (?, ?, ?, ?, ?)
        """,
        (
            generate_id(),
            user_id,
            username,
            1 if success else 0,
            datetime.utcnow().isoformat()
        )
    )
    db.commit()


def log_user_activity(db, user_id: str, event_type: str):
    cursor = db.cursor()
    cursor.execute(
        """
        INSERT INTO user_activity VALUES (?, ?, ?, ?)
        """,
        (
            generate_id(),
            user_id,
            event_type,
            datetime.utcnow().isoformat()
        )
    )
    db.commit()


def require_admin_user(cursor, user_id: str):
    cursor.execute("SELECT id, role FROM users WHERE id=?", (user_id,))
    user_row = cursor.fetchone()
    if not user_row:
        raise HTTPException(status_code=404, detail="User nicht gefunden")
    if (user_row["role"] or "").lower() != "admin":
        raise HTTPException(status_code=403, detail="Nur Admins haben Zugriff")


def send_verification_email(receiver_email: str, code: str, purpose: str):
    sender_email = os.getenv("LEARNHUB_SMTP_EMAIL")
    sender_password = os.getenv("LEARNHUB_SMTP_PASSWORD")

    if not sender_email or not sender_password:
        raise HTTPException(
            status_code=500,
            detail="E-Mail-Versand ist nicht konfiguriert (LEARNHUB_SMTP_EMAIL/LEARNHUB_SMTP_PASSWORD fehlen)"
        )

    subject_map = {
        "register": "Dein LearnHub Verifizierungscode (Registrierung)",
        "change_email": "Dein LearnHub Verifizierungscode (E-Mail-Aenderung)",
        "delete_account": "Dein LearnHub Verifizierungscode (Account-Loeschung)"
    }
    message = EmailMessage()
    message["Subject"] = subject_map.get(purpose, "LearnHub Verifizierungscode")
    message["From"] = sender_email
    message["To"] = receiver_email
    message.set_content(
        f"Hallo,\n\n"
        f"dein Verifizierungscode lautet: {code}\n"
        f"Der Code ist {VERIFICATION_TTL_MINUTES} Minuten gueltig.\n\n"
        "Wenn du diese Aktion nicht gestartet hast, ignoriere diese E-Mail.\n\n"
        "LearnHub"
    )

    context = ssl.create_default_context()
    with smtplib.SMTP_SSL(SMTP_HOST, SMTP_PORT, context=context) as server:
        server.login(sender_email, sender_password)
        server.send_message(message)


def cleanup_expired_verifications(db):
    cursor = db.cursor()
    cursor.execute(
        "DELETE FROM email_verifications WHERE expires_at < ?",
        (datetime.utcnow().isoformat(),)
    )


def create_verification(db, user_id: Optional[str], email: str, purpose: str, payload: dict):
    cleanup_expired_verifications(db)
    cursor = db.cursor()

    verification_id = generate_id()
    code = f"{secrets.randbelow(1000000):06d}"
    now = datetime.utcnow()
    expires_at = now + timedelta(minutes=VERIFICATION_TTL_MINUTES)

    cursor.execute("""
    INSERT INTO email_verifications VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    """, (
        verification_id,
        user_id,
        email,
        purpose,
        hash_verification_code(code),
        json.dumps(payload),
        expires_at.isoformat(),
        now.isoformat()
    ))

    db.commit()
    send_verification_email(email, code, purpose)
    return verification_id


def consume_verification(db, verification_id: str, code: str, purpose: str, user_id: Optional[str]):
    cleanup_expired_verifications(db)
    cursor = db.cursor()
    cursor.execute(
        "SELECT * FROM email_verifications WHERE id=? AND purpose=?",
        (verification_id, purpose)
    )
    verification = cursor.fetchone()

    if not verification:
        raise HTTPException(status_code=400, detail="Verifizierung ungültig oder abgelaufen")

    if user_id is not None and verification["user_id"] != user_id:
        raise HTTPException(status_code=403, detail="Verifizierung gehört zu einem anderen User")

    if verification["code_hash"] != hash_verification_code(code):
        raise HTTPException(status_code=400, detail="Verifizierungscode ist falsch")

    cursor.execute("DELETE FROM email_verifications WHERE id=?", (verification_id,))
    db.commit()
    return verification


# =========================================
# Pydantic MODELS (Request / Response)
# =========================================

class ChangeUsername(BaseModel):
    new_username: str


class ChangePassword(BaseModel):
    old_password: str
    new_password: str


class ChangeEmail(BaseModel):
    new_email: str


class UserRegister(BaseModel):
    username: str
    email: str
    password: str


class RegisterCodeConfirm(BaseModel):
    verification_id: str
    code: str


class ChangeEmailCodeConfirm(BaseModel):
    verification_id: str
    code: str


class DeleteAccountRequest(BaseModel):
    password: str


class DeleteAccountCodeConfirm(BaseModel):
    verification_id: str
    code: str


class UserLogin(BaseModel):
    username: str
    password: str


class TodoCreate(BaseModel):
    title: str
    subject: str
    due_date: str
    priority: str


class HomeworkCreate(BaseModel):
    day: str
    period: int
    title: str


class ExamCreate(BaseModel):
    subject: str
    date: str
    topic: Optional[str] = ""
    period: Optional[int] = None


class CalendarExtraCreate(BaseModel):
    title: str
    date: str
    description: Optional[str] = ""


class AdminMessageCreate(BaseModel):
    title: str
    body: str
    recipient_user_id: Optional[str] = None


class GradeCreate(BaseModel):
    subject: str
    value: float
    weight: float = 1.0
    description: Optional[str] = ""


class SubjectCreate(BaseModel):
    name: str
    color: str

    
class FlashcardCreate(BaseModel):
    subject: str
    front: str
    back: str
    public: bool = False


class FlashcardDeckCreate(BaseModel):
    name: str
    subject: Optional[str] = ""
    description: Optional[str] = ""
    public: bool = False


class FlashcardDeckUpdate(BaseModel):
    name: Optional[str] = None
    subject: Optional[str] = None
    description: Optional[str] = None
    public: Optional[bool] = None


class FlashcardCardCreate(BaseModel):
    front: str
    back: str


class TimetableCreate(BaseModel):
    day: str       # monday, tuesday, ...
    period: int    # 1..10 (Stunde im Raster)
    time: str      # "08:00 - 09:30" (eingetragene Zeit für diese Stunde)
    subject: str
    room: Optional[str] = ""


class TimetableBulk(BaseModel):
    entries: List[TimetableCreate] = []
    times: Optional[dict] = {}


# =========================================
# AUTH ROUTES
# =========================================

@app.post("/auth/register")
def register(user: UserRegister):
    db = get_db()
    cursor = db.cursor()

    if not is_valid_email(user.email):
        raise HTTPException(status_code=400, detail="Ungültige E-Mail-Adresse")

    cursor.execute("SELECT id FROM users WHERE username=?", (user.username,))
    if cursor.fetchone():
        raise HTTPException(status_code=400, detail="Username existiert bereits")

    verification_id = create_verification(
        db=db,
        user_id=None,
        email=user.email,
        purpose="register",
        payload={
            "username": user.username,
            "email": user.email,
            "password_hash": hash_password(user.password)
        }
    )

    return {
        "message": "Verifizierungscode wurde versendet",
        "verification_id": verification_id,
        "expires_in_minutes": VERIFICATION_TTL_MINUTES
    }


@app.post("/auth/register/confirm")
def register_confirm(data: RegisterCodeConfirm):
    db = get_db()
    cursor = db.cursor()

    verification = consume_verification(
        db=db,
        verification_id=data.verification_id,
        code=data.code,
        purpose="register",
        user_id=None
    )
    payload = json.loads(verification["payload"])

    cursor.execute("SELECT id FROM users WHERE username=?", (payload["username"],))
    if cursor.fetchone():
        raise HTTPException(status_code=400, detail="Username existiert bereits")

    cursor.execute("SELECT COUNT(*) AS total FROM users")
    users_total = cursor.fetchone()["total"]
    new_role = "admin" if users_total == 0 else "user"

    try:
        cursor.execute("""
        INSERT INTO users VALUES (?, ?, ?, ?, ?, ?)
        """, (
            generate_id(),
            payload["username"],
            payload["email"],
            payload["password_hash"],
            new_role,
            datetime.utcnow().isoformat()
        ))
        db.commit()
    except sqlite3.IntegrityError:
        raise HTTPException(status_code=400, detail="Username existiert bereits")

    return {"message": "Registrierung erfolgreich"}

@app.put("/auth/change-username/{user_id}")
def change_username(user_id: str, data: ChangeUsername):
    db = get_db()
    cursor = db.cursor()

    # Prüfen ob Username bereits existiert
    cursor.execute(
        "SELECT id FROM users WHERE username=?",
        (data.new_username,)
    )
    if cursor.fetchone():
        raise HTTPException(status_code=400, detail="Username bereits vergeben")

    cursor.execute("""
        UPDATE users
        SET username=?
        WHERE id=?
    """, (data.new_username, user_id))

    if cursor.rowcount == 0:
        raise HTTPException(status_code=404, detail="User nicht gefunden")

    db.commit()
    return {"message": "Username erfolgreich geändert"}


@app.put("/auth/change-password/{user_id}")
def change_password(user_id: str, data: ChangePassword):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
        SELECT password FROM users WHERE id=?
    """, (user_id,))
    user = cursor.fetchone()

    if not user:
        raise HTTPException(status_code=404, detail="User nicht gefunden")

    if user["password"] != hash_password(data.old_password):
        raise HTTPException(status_code=401, detail="Altes Passwort ist falsch")

    cursor.execute("""
        UPDATE users
        SET password=?
        WHERE id=?
    """, (hash_password(data.new_password), user_id))

    db.commit()
    return {"message": "Passwort erfolgreich geändert"}


@app.put("/auth/change-email/{user_id}")
def change_email(user_id: str, data: ChangeEmail):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("SELECT id FROM users WHERE id=?", (user_id,))
    if not cursor.fetchone():
        raise HTTPException(status_code=404, detail="User nicht gefunden")

    if not is_valid_email(data.new_email):
        raise HTTPException(status_code=400, detail="Ungültige E-Mail-Adresse")

    verification_id = create_verification(
        db=db,
        user_id=user_id,
        email=data.new_email,
        purpose="change_email",
        payload={"new_email": data.new_email}
    )

    return {
        "message": "Verifizierungscode wurde an die neue E-Mail gesendet",
        "verification_id": verification_id,
        "expires_in_minutes": VERIFICATION_TTL_MINUTES
    }


@app.put("/auth/change-email/confirm/{user_id}")
def change_email_confirm(user_id: str, data: ChangeEmailCodeConfirm):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("SELECT id FROM users WHERE id=?", (user_id,))
    if not cursor.fetchone():
        raise HTTPException(status_code=404, detail="User nicht gefunden")

    verification = consume_verification(
        db=db,
        verification_id=data.verification_id,
        code=data.code,
        purpose="change_email",
        user_id=user_id
    )
    payload = json.loads(verification["payload"])

    cursor.execute("""
        UPDATE users
        SET email=?
        WHERE id=?
    """, (payload["new_email"], user_id))

    db.commit()
    return {"message": "E-Mail erfolgreich geändert"}


# =========================================
# Timetable routes
# =========================================

@app.get("/timetable/{user_id}")
def get_timetable(user_id: str):
    db = get_db()
    cursor = db.cursor()

    # include period and room so frontend can rebuild grid correctly
    cursor.execute("""
    SELECT id, day, period, time, subject, room
    FROM timetable
    WHERE user_id=?
            AND period IS NOT NULL
    ORDER BY day, period
    """, (user_id,))

    return cursor.fetchall()


# =========================================
# File upload routes
# =========================================




@app.post("/timetable/{user_id}")
def add_timetable_entry(user_id: str, entry: TimetableCreate):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    INSERT INTO timetable (id, user_id, day, time, subject, period, room)
    VALUES (?, ?, ?, ?, ?, ?, ?)
    """, (
        generate_id(),
        user_id,
        entry.day,
        entry.time,
        entry.subject,
        entry.period,
        entry.room
    ))

    db.commit()
    return {"message": "Stunde hinzugefügt"}




@app.put("/timetable/{user_id}/{entry_id}")
def update_timetable_entry(
    user_id: str,
    entry_id: str,
    entry: TimetableCreate
):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    UPDATE timetable
    SET day=?, time=?, subject=?, period=?, room=?
    WHERE id=? AND user_id=?
    """, (
        entry.day,
        entry.time,
        entry.subject,
        entry.period,
        entry.room,
        entry_id,
        user_id
    ))

    if cursor.rowcount == 0:
        raise HTTPException(status_code=404, detail="Eintrag nicht gefunden")

    db.commit()
    return {"message": "Stunde aktualisiert"}


@app.delete("/timetable/{user_id}/{entry_id}")
def delete_timetable_entry(user_id: str, entry_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    DELETE FROM timetable
    WHERE id=? AND user_id=?
    """, (entry_id, user_id))

    if cursor.rowcount == 0:
        raise HTTPException(status_code=404, detail="Eintrag nicht gefunden")

    db.commit()
    return {"message": "Stunde gelöscht"}


# bulk replace all timetable entries for a user (used when the entire plan is saved)
@app.get("/timetable_times/{user_id}")
def get_timetable_times(user_id: str):
    db = get_db()
    cursor = db.cursor()
    cursor.execute("SELECT period, time FROM timetable_times WHERE user_id=?", (user_id,))
    rows = cursor.fetchall()
    return {str(r["period"]): r["time"] for r in rows}


@app.post("/timetable/{user_id}/bulk")
def bulk_update_timetable(user_id: str, bulk: TimetableBulk):
    db = get_db()
    cursor = db.cursor()

    # ---- timetable entries ----
    cursor.execute("DELETE FROM timetable WHERE user_id=?", (user_id,))
    for entry in bulk.entries:
        cursor.execute("""
        INSERT INTO timetable (id, user_id, day, time, subject, period, room)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        """, (
            generate_id(),
            user_id,
            entry.day,
            entry.time,
            entry.subject,
            entry.period,
            entry.room
        ))

    # ---- period times ----
    cursor.execute("DELETE FROM timetable_times WHERE user_id=?", (user_id,))
    if bulk.times:
        for period_str, t in bulk.times.items():
            try:
                period = int(period_str)
            except Exception:
                continue
            cursor.execute("INSERT INTO timetable_times VALUES (?, ?, ?)", (user_id, period, t))

    db.commit()
    return {"message": "Stundenplan aktualisiert"}


# =========================================
# FILE UPLOAD ROUTES
# =========================================

@app.delete("/files/{user_id}/{file_id}")
def delete_file(user_id: str, file_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    SELECT filename FROM files WHERE id=? AND user_id=?
    """, (file_id, user_id))

    file = cursor.fetchone()
    if not file:
        raise HTTPException(status_code=404, detail="Datei nicht gefunden")

    file_path = os.path.join(UPLOAD_DIR, user_id, file["filename"])

    if os.path.exists(file_path):
        os.remove(file_path)

    cursor.execute("DELETE FROM files WHERE id=?", (file_id,))
    db.commit()

    return {"message": "Datei gelöscht"}

@app.get("/files/download/{user_id}/{file_id}")
def download_file(user_id: str, file_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    SELECT filename, original_name
    FROM files WHERE id=? AND user_id=?
    """, (file_id, user_id))

    file = cursor.fetchone()
    if not file:
        raise HTTPException(status_code=404, detail="Datei nicht gefunden")

    file_path = os.path.join(UPLOAD_DIR, user_id, file["filename"])

    if not os.path.exists(file_path):
        raise HTTPException(status_code=404, detail="Datei physisch nicht vorhanden")

    # FileResponse mit originalem Dateinamen inkl. Endung
    return FileResponse(
        path=file_path,
        filename=file["original_name"],  # <-- hier kommt der Name inkl. Endung
        media_type="application/octet-stream",
        headers={"Content-Disposition": f'attachment; filename="{file["original_name"]}"'}
    )



@app.get("/files/{user_id}")
def get_user_files(user_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    SELECT id, original_name, subject, uploaded_at
    FROM files WHERE user_id=?
    """, (user_id,))

    return cursor.fetchall()

ALLOWED_EXTENSIONS = {"pdf", "png", "jpg", "jpeg", "docx", "txt"}
MAX_FILE_SIZE = 5 * 1024 * 1024  # 5MB

@app.post("/files/upload/{user_id}")
async def upload_file(
    user_id: str,
    subject: str = Form(...),
    file: UploadFile = File(...)
):
    db = get_db()
    cursor = db.cursor()

    # ---------------------------
    # 1️⃣ Dateityp prüfen
    # ---------------------------
    file_ext = file.filename.split(".")[-1].lower()
    if file_ext not in ALLOWED_EXTENSIONS:
        raise HTTPException(status_code=400, detail="Dateityp nicht erlaubt")

    # ---------------------------
    # 2️⃣ Dateigröße prüfen
    # ---------------------------
    content = await file.read()
    if len(content) > MAX_FILE_SIZE:
        raise HTTPException(status_code=400, detail="Datei zu groß (max 5MB)")

    # ---------------------------
    # 3️⃣ Speicherpfad
    # ---------------------------
    file_id = generate_id()
    user_dir = os.path.join(UPLOAD_DIR, user_id)
    os.makedirs(user_dir, exist_ok=True)

    stored_filename = f"{file_id}.{file_ext}"
    file_path = os.path.join(user_dir, stored_filename)

    # ---------------------------
    # 4️⃣ Datei speichern
    # ---------------------------
    with open(file_path, "wb") as f:
        f.write(content)

    # ---------------------------
    # 5️⃣ DB speichern
    # ---------------------------
    cursor.execute("""
    INSERT INTO files VALUES (?, ?, ?, ?, ?, ?)
    """, (
        file_id,
        user_id,
        stored_filename,
        file.filename,
        subject,
        datetime.utcnow().isoformat()
    ))

    db.commit()

    return {"message": "Datei erfolgreich hochgeladen"}


    # Metadaten speichern
    cursor.execute("""
    INSERT INTO files VALUES (?, ?, ?, ?, ?, ?)
    """, (
        file_id,
        user_id,
        stored_filename,
        file.filename,
        subject,
        datetime.utcnow().isoformat()
    ))

    db.commit()

    return {"message": "Datei erfolgreich hochgeladen"}

@app.post("/auth/login")
def login(user: UserLogin):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    SELECT * FROM users WHERE username = ?
    """, (user.username,))
    user_db = cursor.fetchone()

    if user_db is None or user_db['password'] != hash_password(user.password):
        log_login_attempt(db, user.username, False)
        raise HTTPException(status_code=401, detail="Ungültiger Benutzername oder Passwort")

    log_login_attempt(db, user.username, True, user_db["id"])
    log_user_activity(db, user_db["id"], "login")

    return {
        "message": "Login erfolgreich",
        "user_id": user_db["id"],
        "username": user_db["username"],
        "role": user_db["role"]
    }


@app.post("/auth/delete-account/request-code/{user_id}")
def request_delete_account_code(user_id: str, data: DeleteAccountRequest):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("SELECT email, password FROM users WHERE id=?", (user_id,))
    user = cursor.fetchone()

    if not user:
        raise HTTPException(status_code=404, detail="User nicht gefunden")

    if user["password"] != hash_password(data.password):
        raise HTTPException(status_code=401, detail="Passwort ist falsch")

    verification_id = create_verification(
        db=db,
        user_id=user_id,
        email=user["email"],
        purpose="delete_account",
        payload={"approved": True}
    )

    return {
        "message": "Verifizierungscode wurde an deine E-Mail gesendet",
        "verification_id": verification_id,
        "expires_in_minutes": VERIFICATION_TTL_MINUTES
    }


@app.post("/auth/delete-account/confirm/{user_id}")
def confirm_delete_account(user_id: str, data: DeleteAccountCodeConfirm):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("SELECT id FROM users WHERE id=?", (user_id,))
    user = cursor.fetchone()

    if not user:
        raise HTTPException(status_code=404, detail="User nicht gefunden")

    consume_verification(
        db=db,
        verification_id=data.verification_id,
        code=data.code,
        purpose="delete_account",
        user_id=user_id
    )

    cursor.execute("DELETE FROM todos WHERE user_id=?", (user_id,))
    cursor.execute("DELETE FROM homework_entries WHERE user_id=?", (user_id,))
    cursor.execute("DELETE FROM exams WHERE user_id=?", (user_id,))
    cursor.execute("DELETE FROM calendar_extras WHERE user_id=?", (user_id,))
    cursor.execute("DELETE FROM grades WHERE user_id=?", (user_id,))
    cursor.execute("DELETE FROM timetable WHERE user_id=?", (user_id,))
    cursor.execute("DELETE FROM timetable_times WHERE user_id=?", (user_id,))
    cursor.execute("DELETE FROM flashcards WHERE user_id=?", (user_id,))
    cursor.execute("DELETE FROM files WHERE user_id=?", (user_id,))
    cursor.execute("DELETE FROM flashcard_decks WHERE user_id=?", (user_id,))
    cursor.execute("DELETE FROM email_verifications WHERE user_id=?", (user_id,))
    cursor.execute("DELETE FROM admin_messages WHERE sender_user_id=? OR recipient_user_id=?", (user_id, user_id))

    cursor.execute("DELETE FROM users WHERE id=?", (user_id,))
    db.commit()

    user_dir = os.path.join(UPLOAD_DIR, user_id)
    if os.path.exists(user_dir):
        for filename in os.listdir(user_dir):
            file_path = os.path.join(user_dir, filename)
            if os.path.isfile(file_path):
                os.remove(file_path)
        os.rmdir(user_dir)

    return {"message": "Account erfolgreich gelöscht"}


# =========================================
# ADMIN MESSAGE ROUTES
# =========================================

@app.get("/messages/{user_id}")
def get_admin_messages(user_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute(
        """
        SELECT
            m.id,
            m.title,
            m.body,
            m.created_at,
            m.sender_user_id,
            m.recipient_user_id,
            COALESCE(sender.username, 'Admin') AS sender_username,
            recipient.username AS recipient_username
        FROM admin_messages m
        LEFT JOIN users sender ON sender.id = m.sender_user_id
        LEFT JOIN users recipient ON recipient.id = m.recipient_user_id
        WHERE m.recipient_user_id IS NULL OR m.recipient_user_id = ?
        ORDER BY m.created_at DESC
        """,
        (user_id,)
    )

    rows = cursor.fetchall()
    db.close()
    return [
        {
            **dict(row),
            "is_broadcast": row["recipient_user_id"] is None
        }
        for row in rows
    ]


@app.get("/admin/messages/{requester_id}")
def get_admin_message_management(requester_id: str):
    db = get_db()
    cursor = db.cursor()

    require_admin_user(cursor, requester_id)

    cursor.execute(
        """
        SELECT
            m.id,
            m.title,
            m.body,
            m.created_at,
            m.sender_user_id,
            m.recipient_user_id,
            COALESCE(sender.username, 'Admin') AS sender_username,
            recipient.username AS recipient_username
        FROM admin_messages m
        LEFT JOIN users sender ON sender.id = m.sender_user_id
        LEFT JOIN users recipient ON recipient.id = m.recipient_user_id
        ORDER BY m.created_at DESC
        """
    )
    messages = [
        {
            **dict(row),
            "is_broadcast": row["recipient_user_id"] is None
        }
        for row in cursor.fetchall()
    ]

    cursor.execute(
        """
        SELECT id, username, role
        FROM users
        ORDER BY lower(username) ASC
        """
    )
    users = [dict(row) for row in cursor.fetchall()]

    db.close()
    return {
        "messages": messages,
        "users": users
    }


@app.post("/admin/messages/{requester_id}")
def create_admin_message(requester_id: str, payload: AdminMessageCreate):
    db = get_db()
    cursor = db.cursor()

    require_admin_user(cursor, requester_id)

    title = (payload.title or "").strip()
    body = (payload.body or "").strip()
    recipient_user_id = (payload.recipient_user_id or "").strip() or None

    if not title:
        raise HTTPException(status_code=400, detail="Titel fehlt")
    if not body:
        raise HTTPException(status_code=400, detail="Nachricht fehlt")

    recipient_username = None
    if recipient_user_id is not None:
        cursor.execute("SELECT username FROM users WHERE id=?", (recipient_user_id,))
        recipient_row = cursor.fetchone()
        if not recipient_row:
            raise HTTPException(status_code=404, detail="Empfänger nicht gefunden")
        recipient_username = recipient_row["username"]

    message_id = generate_id()
    created_at = datetime.utcnow().isoformat()

    cursor.execute(
        """
        INSERT INTO admin_messages (id, sender_user_id, recipient_user_id, title, body, created_at)
        VALUES (?, ?, ?, ?, ?, ?)
        """,
        (message_id, requester_id, recipient_user_id, title, body, created_at)
    )

    db.commit()
    db.close()
    return {
        "message": "Admin-Nachricht gespeichert",
        "id": message_id,
        "created_at": created_at,
        "recipient_username": recipient_username,
        "is_broadcast": recipient_user_id is None
    }


@app.delete("/admin/messages/{requester_id}/{message_id}")
def delete_admin_message(requester_id: str, message_id: str):
    db = get_db()
    cursor = db.cursor()

    require_admin_user(cursor, requester_id)

    cursor.execute("DELETE FROM admin_messages WHERE id=?", (message_id,))
    if cursor.rowcount == 0:
        raise HTTPException(status_code=404, detail="Nachricht nicht gefunden")

    db.commit()
    db.close()
    return {"message": "Nachricht gelöscht"}


# =========================================
# TODO ROUTES
# =========================================

@app.post("/todos/{user_id}")
def create_todo(user_id: str, todo: TodoCreate):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    INSERT INTO todos VALUES (?, ?, ?, ?, ?, ?, ?)
    """, (
        generate_id(),
        user_id,
        todo.title,
        todo.subject,
        todo.due_date,
        todo.priority,
        0
    ))

    db.commit()
    return {"message": "To-Do erstellt"}


@app.get("/todos/{user_id}")
def get_todos(user_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("SELECT * FROM todos WHERE user_id=?", (user_id,))
    return cursor.fetchall()

@app.delete("/todos/{user_id}/{todo_id}")
def delete_todo(user_id: str, todo_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    DELETE FROM todos
    WHERE id=? AND user_id=?
    """, (todo_id, user_id))

    if cursor.rowcount == 0:
        raise HTTPException(status_code=404, detail="To-Do nicht gefunden")

    db.commit()
    return {"message": "To-Do gelöscht"}


@app.patch("/todos/{user_id}/{todo_id}/toggle")
def toggle_todo(user_id: str, todo_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("SELECT done FROM todos WHERE id=? AND user_id=?", (todo_id, user_id))
    todo = cursor.fetchone()
    if not todo:
        raise HTTPException(status_code=404, detail="To-Do nicht gefunden")

    new_done = 0 if todo["done"] else 1
    cursor.execute(
        "UPDATE todos SET done=? WHERE id=? AND user_id=?",
        (new_done, todo_id, user_id)
    )
    db.commit()
    return {"message": "Status geändert", "done": bool(new_done)}


# =========================================
# HOMEWORK ROUTES
# =========================================

@app.post("/homework/{user_id}")
def create_homework(user_id: str, homework: HomeworkCreate):
    db = get_db()
    cursor = db.cursor()
    homework_id = generate_id()

    cursor.execute(
        """
        INSERT INTO homework_entries (id, user_id, day, period, title, created_at)
        VALUES (?, ?, ?, ?, ?, ?)
        """,
        (
            homework_id,
            user_id,
            homework.day,
            homework.period,
            homework.title,
            datetime.utcnow().isoformat()
        )
    )

    db.commit()
    return {"message": "Hausaufgabe gespeichert", "id": homework_id}


@app.get("/homework/{user_id}")
def get_homework(user_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute(
        """
        SELECT * FROM homework_entries
        WHERE user_id=?
        ORDER BY CASE day
            WHEN 'monday' THEN 1
            WHEN 'tuesday' THEN 2
            WHEN 'wednesday' THEN 3
            WHEN 'thursday' THEN 4
            WHEN 'friday' THEN 5
            ELSE 99
        END, period, created_at
        """,
        (user_id,)
    )
    return cursor.fetchall()


@app.delete("/homework/{user_id}/{homework_id}")
def delete_homework(user_id: str, homework_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute(
        """
        DELETE FROM homework_entries
        WHERE id=? AND user_id=?
        """,
        (homework_id, user_id)
    )

    if cursor.rowcount == 0:
        raise HTTPException(status_code=404, detail="Hausaufgabe nicht gefunden")

    db.commit()
    return {"message": "Hausaufgabe gelöscht"}


# =========================================
# EXAM ROUTES
# =========================================

@app.post("/exams/{user_id}")
def create_exam(user_id: str, exam: ExamCreate):
    db = get_db()
    cursor = db.cursor()
    exam_id = generate_id()

    cursor.execute(
        """
        INSERT INTO exams (id, user_id, subject, date, topic, period, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        """,
        (
            exam_id,
            user_id,
            exam.subject,
            exam.date,
            exam.topic or "",
            exam.period,
            datetime.utcnow().isoformat()
        )
    )

    db.commit()
    return {"message": "Klassenarbeit gespeichert", "id": exam_id}


@app.get("/exams/{user_id}")
def get_exams(user_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute(
        """
        SELECT * FROM exams
        WHERE user_id=?
        ORDER BY date, COALESCE(period, 999), created_at
        """,
        (user_id,)
    )
    return cursor.fetchall()


@app.delete("/exams/{user_id}/{exam_id}")
def delete_exam(user_id: str, exam_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute(
        """
        DELETE FROM exams
        WHERE id=? AND user_id=?
        """,
        (exam_id, user_id)
    )

    if cursor.rowcount == 0:
        raise HTTPException(status_code=404, detail="Klassenarbeit nicht gefunden")

    db.commit()
    return {"message": "Klassenarbeit gelöscht"}


@app.put("/exams/{user_id}/{exam_id}/grade")
def set_exam_grade(user_id: str, exam_id: str, grade_data: dict):
    db = get_db()
    cursor = db.cursor()

    grade_value = grade_data.get("grade")
    if grade_value is None or not isinstance(grade_value, (int, float)):
        raise HTTPException(status_code=400, detail="Ungültige Noteneingabe")

    cursor.execute(
        """
        UPDATE exams
        SET grade=?
        WHERE id=? AND user_id=?
        """,
        (grade_value, exam_id, user_id)
    )

    if cursor.rowcount == 0:
        raise HTTPException(status_code=404, detail="Klassenarbeit nicht gefunden")

    db.commit()
    return {"message": "Note für Klassenarbeit gespeichert"}


# =========================================
# CALENDAR EXTRA ROUTES
# =========================================

@app.post("/calendar-extras/{user_id}")
def create_calendar_extra(user_id: str, event: CalendarExtraCreate):
    db = get_db()
    cursor = db.cursor()
    event_id = generate_id()

    cursor.execute(
        """
        INSERT INTO calendar_extras (id, user_id, title, date, description, created_at)
        VALUES (?, ?, ?, ?, ?, ?)
        """,
        (
            event_id,
            user_id,
            event.title,
            event.date,
            event.description or "",
            datetime.utcnow().isoformat()
        )
    )

    db.commit()
    return {"message": "Termin gespeichert", "id": event_id}


@app.get("/calendar-extras/{user_id}")
def get_calendar_extras(user_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute(
        """
        SELECT * FROM calendar_extras
        WHERE user_id=?
        ORDER BY date, created_at
        """,
        (user_id,)
    )
    return cursor.fetchall()


@app.delete("/calendar-extras/{user_id}/{event_id}")
def delete_calendar_extra(user_id: str, event_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute(
        """
        DELETE FROM calendar_extras
        WHERE id=? AND user_id=?
        """,
        (event_id, user_id)
    )

    if cursor.rowcount == 0:
        raise HTTPException(status_code=404, detail="Termin nicht gefunden")

    db.commit()
    return {"message": "Termin gelöscht"}


# =========================================
# GRADES ROUTES
# =========================================

@app.post("/grades/{user_id}")
def add_grade(user_id: str, grade: GradeCreate):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    INSERT INTO grades VALUES (?, ?, ?, ?, ?, ?, ?)
    """, (
        generate_id(),
        user_id,
        grade.subject,
        grade.value,
        grade.description,
        datetime.utcnow().isoformat(),
        grade.weight if grade.weight and grade.weight > 0 else 1.0
    ))

    db.commit()
    return {"message": "Note gespeichert"}


@app.get("/grades/{user_id}")
def get_grades(user_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("SELECT * FROM grades WHERE user_id=?", (user_id,))
    return cursor.fetchall()


@app.delete("/grades/{user_id}/{grade_id}")
def delete_grade(user_id: str, grade_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    DELETE FROM grades
    WHERE id=? AND user_id=?
    """, (grade_id, user_id))

    if cursor.rowcount == 0:
        raise HTTPException(status_code=404, detail="Note nicht gefunden")

    db.commit()
    return {"message": "Note gelöscht"}


# =========================================
# SUBJECTS ROUTES (Fächer)
# =========================================

@app.post("/subjects/{user_id}")
def add_subject(user_id: str, subject: SubjectCreate):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    INSERT INTO subjects VALUES (?, ?, ?, ?, ?)
    """, (
        generate_id(),
        user_id,
        subject.name,
        subject.color,
        datetime.utcnow().isoformat()
    ))

    db.commit()
    return {"message": "Fach gespeichert"}


@app.get("/subjects/{user_id}")
def get_subjects(user_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("SELECT * FROM subjects WHERE user_id=?", (user_id,))
    return cursor.fetchall()


@app.delete("/subjects/{user_id}/{subject_id}")
def delete_subject(user_id: str, subject_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    DELETE FROM subjects
    WHERE id=? AND user_id=?
    """, (subject_id, user_id))

    if cursor.rowcount == 0:
        raise HTTPException(status_code=404, detail="Fach nicht gefunden")

    db.commit()
    return {"message": "Fach gelöscht"}


# =========================================
# FLASHCARD DECK ROUTES
# =========================================

@app.get("/flashcard-decks/explore")
def explore_public_decks():
    db = get_db()
    cursor = db.cursor()
    cursor.execute("""
    SELECT d.id, d.user_id, d.name, d.subject, d.description, d.created_at,
           u.username,
           (SELECT COUNT(*) FROM flashcards f WHERE f.deck_id = d.id) AS card_count
    FROM flashcard_decks d
    JOIN users u ON d.user_id = u.id
    WHERE d.public = 1
    ORDER BY d.created_at DESC
    """)
    rows = cursor.fetchall()
    db.close()
    return [dict(r) for r in rows]


@app.get("/flashcard-decks/{user_id}")
def get_user_decks(user_id: str):
    db = get_db()
    cursor = db.cursor()
    cursor.execute("""
    SELECT d.id, d.name, d.subject, d.description, d.public, d.created_at,
           (SELECT COUNT(*) FROM flashcards f WHERE f.deck_id = d.id) AS card_count
    FROM flashcard_decks d
    WHERE d.user_id = ?
    ORDER BY d.created_at DESC
    """, (user_id,))
    rows = cursor.fetchall()
    db.close()
    return [dict(r) for r in rows]


@app.post("/flashcard-decks/{user_id}")
def create_deck(user_id: str, deck: FlashcardDeckCreate):
    db = get_db()
    cursor = db.cursor()
    deck_id = generate_id()
    cursor.execute("""
    INSERT INTO flashcard_decks VALUES (?, ?, ?, ?, ?, ?, ?)
    """, (
        deck_id,
        user_id,
        deck.name,
        deck.subject,
        deck.description,
        int(deck.public),
        datetime.utcnow().isoformat()
    ))
    db.commit()
    db.close()
    return {"message": "Stapel erstellt", "id": deck_id}


@app.put("/flashcard-decks/{user_id}/{deck_id}")
def update_deck(user_id: str, deck_id: str, deck: FlashcardDeckUpdate):
    db = get_db()
    cursor = db.cursor()
    cursor.execute("SELECT * FROM flashcard_decks WHERE id=? AND user_id=?", (deck_id, user_id))
    existing = cursor.fetchone()
    if not existing:
        raise HTTPException(status_code=404, detail="Stapel nicht gefunden")
    new_name = deck.name if deck.name is not None else existing["name"]
    new_subject = deck.subject if deck.subject is not None else existing["subject"]
    new_desc = deck.description if deck.description is not None else existing["description"]
    new_public = int(deck.public) if deck.public is not None else existing["public"]
    cursor.execute("""
    UPDATE flashcard_decks SET name=?, subject=?, description=?, public=?
    WHERE id=? AND user_id=?
    """, (new_name, new_subject, new_desc, new_public, deck_id, user_id))
    db.commit()
    db.close()
    return {"message": "Stapel aktualisiert"}


@app.delete("/flashcard-decks/{user_id}/{deck_id}")
def delete_deck(user_id: str, deck_id: str):
    db = get_db()
    cursor = db.cursor()
    cursor.execute("DELETE FROM flashcards WHERE deck_id=? AND user_id=?", (deck_id, user_id))
    cursor.execute("DELETE FROM flashcard_decks WHERE id=? AND user_id=?", (deck_id, user_id))
    if cursor.rowcount == 0:
        raise HTTPException(status_code=404, detail="Stapel nicht gefunden")
    db.commit()
    db.close()
    return {"message": "Stapel gelöscht"}


@app.post("/flashcard-decks/{user_id}/copy/{source_deck_id}")
def copy_deck(user_id: str, source_deck_id: str):
    db = get_db()
    cursor = db.cursor()
    cursor.execute("SELECT * FROM flashcard_decks WHERE id=? AND public=1", (source_deck_id,))
    source = cursor.fetchone()
    if not source:
        raise HTTPException(status_code=404, detail="Stapel nicht gefunden oder nicht öffentlich")
    new_deck_id = generate_id()
    cursor.execute("""
    INSERT INTO flashcard_decks VALUES (?, ?, ?, ?, ?, 0, ?)
    """, (
        new_deck_id,
        user_id,
        source["name"] + " (Kopie)",
        source["subject"],
        source["description"],
        datetime.utcnow().isoformat()
    ))
    cursor.execute("SELECT * FROM flashcards WHERE deck_id=?", (source_deck_id,))
    cards = cursor.fetchall()
    for card in cards:
        cursor.execute("""
        INSERT INTO flashcards VALUES (?, ?, ?, ?, ?, 0, ?)
        """, (generate_id(), user_id, card["subject"], card["front"], card["back"], new_deck_id))
    db.commit()
    db.close()
    return {"message": "Stapel übernommen", "id": new_deck_id}


# =========================================
# FLASHCARD CARD ROUTES
# =========================================

@app.get("/flashcard-cards/deck/{deck_id}")
def get_deck_cards(deck_id: str):
    db = get_db()
    cursor = db.cursor()
    cursor.execute("""
    SELECT id, front, back FROM flashcards WHERE deck_id=?
    """, (deck_id,))
    rows = cursor.fetchall()
    db.close()
    return [dict(r) for r in rows]


@app.post("/flashcard-cards/{user_id}/deck/{deck_id}")
def add_card_to_deck(user_id: str, deck_id: str, card: FlashcardCardCreate):
    db = get_db()
    cursor = db.cursor()
    cursor.execute("SELECT id FROM flashcard_decks WHERE id=? AND user_id=?", (deck_id, user_id))
    if not cursor.fetchone():
        raise HTTPException(status_code=404, detail="Stapel nicht gefunden")
    cursor.execute("""
    INSERT INTO flashcards VALUES (?, ?, ?, ?, ?, 0, ?)
    """, (generate_id(), user_id, "", card.front, card.back, deck_id))
    db.commit()
    db.close()
    return {"message": "Karte hinzugefügt"}


@app.delete("/flashcard-cards/{user_id}/{card_id}")
def delete_card(user_id: str, card_id: str):
    db = get_db()
    cursor = db.cursor()
    cursor.execute("DELETE FROM flashcards WHERE id=? AND user_id=?", (card_id, user_id))
    if cursor.rowcount == 0:
        raise HTTPException(status_code=404, detail="Karte nicht gefunden")
    db.commit()
    db.close()
    return {"message": "Karte gelöscht"}


# =========================================
# FLASHCARDS ROUTES (legacy)
# =========================================

@app.post("/flashcards/{user_id}")
def create_flashcard(user_id: str, card: FlashcardCreate):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    INSERT INTO flashcards VALUES (?, ?, ?, ?, ?, ?, NULL)
    """, (
        generate_id(),
        user_id,
        card.subject,
        card.front,
        card.back,
        int(card.public)
    ))

    db.commit()
    db.close()
    return {"message": "Karteikarte erstellt"}


@app.get("/flashcards/{user_id}")
def get_flashcards(user_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    SELECT * FROM flashcards WHERE user_id=? OR public=1
    """, (user_id,))
    rows = cursor.fetchall()
    db.close()
    return [dict(r) for r in rows]


# =========================================
# ADMIN ROUTES
# =========================================

@app.get("/admin/stats/{requester_id}")
def admin_stats(requester_id: str):
    db = get_db()
    cursor = db.cursor()

    require_admin_user(cursor, requester_id)

    now = datetime.utcnow()
    cutoff_1d = (now - timedelta(days=1)).isoformat()
    cutoff_7d = (now - timedelta(days=7)).isoformat()
    cutoff_30d = (now - timedelta(days=30)).isoformat()

    cursor.execute("SELECT COUNT(*) AS total FROM users")
    total_users = cursor.fetchone()["total"]

    cursor.execute("SELECT COUNT(*) AS total FROM users WHERE created_at >= ?", (cutoff_30d,))
    new_users_30d = cursor.fetchone()["total"]

    cursor.execute(
        "SELECT COUNT(DISTINCT user_id) AS total FROM user_activity WHERE event_type='login' AND created_at >= ?",
        (cutoff_1d,)
    )
    dau = cursor.fetchone()["total"]

    cursor.execute(
        "SELECT COUNT(DISTINCT user_id) AS total FROM user_activity WHERE event_type='login' AND created_at >= ?",
        (cutoff_7d,)
    )
    wau = cursor.fetchone()["total"]

    cursor.execute(
        "SELECT COUNT(DISTINCT user_id) AS total FROM user_activity WHERE event_type='login' AND created_at >= ?",
        (cutoff_30d,)
    )
    mau = cursor.fetchone()["total"]

    cursor.execute("SELECT COUNT(*) AS total FROM login_attempts WHERE success=0 AND created_at >= ?", (cutoff_7d,))
    failed_logins_7d = cursor.fetchone()["total"]

    cursor.execute("SELECT COUNT(*) AS total FROM todos")
    total_todos = cursor.fetchone()["total"]

    cursor.execute("SELECT COUNT(*) AS total FROM todos WHERE done=1")
    completed_todos = cursor.fetchone()["total"]

    todo_completion_rate = (completed_todos / total_todos * 100) if total_todos else 0

    cursor.execute("SELECT COUNT(*) AS total FROM grades")
    total_grades = cursor.fetchone()["total"]

    cursor.execute("SELECT AVG(value) AS avg_grade FROM grades")
    avg_row = cursor.fetchone()["avg_grade"]
    average_grade = round(float(avg_row), 2) if avg_row is not None else 0

    cursor.execute("SELECT COUNT(*) AS total FROM flashcard_decks")
    total_flashcard_decks = cursor.fetchone()["total"]

    cursor.execute("SELECT COUNT(*) AS total FROM flashcards")
    total_flashcards = cursor.fetchone()["total"]

    cursor.execute("SELECT COUNT(*) AS total FROM files")
    total_files = cursor.fetchone()["total"]

    cursor.execute("SELECT COUNT(*) AS total FROM files WHERE uploaded_at >= ?", (cutoff_30d,))
    upload_count_30d = cursor.fetchone()["total"]

    total_upload_size_bytes = 0
    cursor.execute("SELECT user_id, filename FROM files")
    for row in cursor.fetchall():
        file_path = os.path.join(UPLOAD_DIR, row["user_id"], row["filename"])
        if os.path.exists(file_path) and os.path.isfile(file_path):
            total_upload_size_bytes += os.path.getsize(file_path)

    cursor.execute(
        """
        SELECT u.username, COUNT(*) AS logins_30d
        FROM user_activity a
        JOIN users u ON u.id = a.user_id
        WHERE a.event_type='login' AND a.created_at >= ?
        GROUP BY a.user_id
        ORDER BY logins_30d DESC, u.username ASC
        LIMIT 5
        """,
        (cutoff_30d,)
    )
    top_active_users = [dict(row) for row in cursor.fetchall()]

    cursor.execute(
        """
        SELECT u.username, COUNT(*) AS open_todos
        FROM todos t
        JOIN users u ON u.id = t.user_id
        WHERE t.done = 0
        GROUP BY t.user_id
        ORDER BY open_todos DESC, u.username ASC
        LIMIT 5
        """
    )
    users_many_open_todos = [dict(row) for row in cursor.fetchall()]

    cursor.execute(
        """
        SELECT substr(created_at, 1, 10) AS date, COUNT(*) AS count
        FROM users
        WHERE created_at >= ?
        GROUP BY substr(created_at, 1, 10)
        ORDER BY date ASC
        """,
        (cutoff_7d,)
    )
    registrations_last_7_days = [dict(row) for row in cursor.fetchall()]

    cursor.execute(
        """
        SELECT substr(created_at, 1, 10) AS date, COUNT(*) AS count
        FROM user_activity
        WHERE event_type='login' AND created_at >= ?
        GROUP BY substr(created_at, 1, 10)
        ORDER BY date ASC
        """,
        (cutoff_7d,)
    )
    logins_last_7_days = [dict(row) for row in cursor.fetchall()]

    return {
        "overview": {
            "total_users": total_users,
            "new_users_30d": new_users_30d,
            "dau": dau,
            "wau": wau,
            "mau": mau,
            "failed_logins_7d": failed_logins_7d
        },
        "learning": {
            "total_todos": total_todos,
            "completed_todos": completed_todos,
            "todo_completion_rate": round(todo_completion_rate, 1),
            "total_grades": total_grades,
            "average_grade": average_grade,
            "total_flashcard_decks": total_flashcard_decks,
            "total_flashcards": total_flashcards
        },
        "content": {
            "total_files": total_files,
            "upload_count_30d": upload_count_30d,
            "total_upload_size_bytes": total_upload_size_bytes
        },
        "top_lists": {
            "top_active_users": top_active_users,
            "users_many_open_todos": users_many_open_todos
        },
        "trends": {
            "registrations_last_7_days": registrations_last_7_days,
            "logins_last_7_days": logins_last_7_days
        },
        "generated_at": now.isoformat()
    }

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(
        "backend:app",
        host="127.0.0.1",
        port=8000,
        reload=True
    )

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
from datetime import datetime
import sqlite3
import uuid
import hashlib
import os
from fastapi import UploadFile, File
from fastapi.responses import FileResponse

UPLOAD_DIR = "uploads"
os.makedirs(UPLOAD_DIR, exist_ok=True)


# =========================================
# APP INITIALISIERUNG
# =========================================

app = FastAPI(
    title="LearnHub API",
    description="Backend für die LearnHub Lernplattform",
    version="2.0.0"
)

DB_NAME = "learnhub.db"


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

    # GRADES
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS grades (
        id TEXT PRIMARY KEY,
        user_id TEXT,
        subject TEXT,
        value REAL,
        description TEXT,
        date TEXT,
        grade_type TEXT DEFAULT 'written'
    )
    """)

    # TIMETABLE
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS timetable (
        id TEXT PRIMARY KEY,
        user_id TEXT,
        day TEXT,
        period_number INTEGER DEFAULT 1,
        start_time TEXT,
        end_time TEXT,
        subject TEXT
    )
    """)

    # FLASHCARD DECKS
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS flashcard_decks (
        id TEXT PRIMARY KEY,
        user_id TEXT,
        name TEXT,
        color TEXT DEFAULT '#0d6efd',
        created_at TEXT
    )
    """)

    # FLASHCARDS
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS flashcards (
        id TEXT PRIMARY KEY,
        user_id TEXT,
        deck_id TEXT,
        subject TEXT,
        front TEXT,
        back TEXT,
        public INTEGER,
        known INTEGER DEFAULT 0
    )
    """)

    # HOMEWORK
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS homework (
        id TEXT PRIMARY KEY,
        user_id TEXT,
        subject TEXT,
        day TEXT,
        text TEXT,
        due_date TEXT,
        done INTEGER DEFAULT 0,
        created_at TEXT
    )
    """)

    # USER SETTINGS
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS user_settings (
        user_id TEXT PRIMARY KEY,
        grade_system TEXT DEFAULT 'points'
    )
    """)

    # SUBJECT SETTINGS (oral/written weighting)
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS subject_settings (
        id TEXT PRIMARY KEY,
        user_id TEXT,
        subject TEXT,
        oral_weight REAL DEFAULT 0.3,
        written_weight REAL DEFAULT 0.7
    )
    """)

    # PERIOD SETTINGS
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS period_settings (
        id TEXT PRIMARY KEY,
        user_id TEXT,
        period_number INTEGER,
        start_time TEXT,
        end_time TEXT
    )
    """)

    # Migration: add grade_type to existing grades if missing
    try:
        cursor.execute("ALTER TABLE grades ADD COLUMN grade_type TEXT DEFAULT 'written'")
    except:
        pass

    # Migration: add period fields to timetable if missing
    try:
        cursor.execute("ALTER TABLE timetable ADD COLUMN period_number INTEGER DEFAULT 1")
    except:
        pass
    try:
        cursor.execute("ALTER TABLE timetable ADD COLUMN start_time TEXT")
    except:
        pass
    try:
        cursor.execute("ALTER TABLE timetable ADD COLUMN end_time TEXT")
    except:
        pass

    # Migration: add deck_id and known to flashcards if missing
    try:
        cursor.execute("ALTER TABLE flashcards ADD COLUMN deck_id TEXT")
    except:
        pass
    try:
        cursor.execute("ALTER TABLE flashcards ADD COLUMN known INTEGER DEFAULT 0")
    except:
        pass

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


# =========================================
# Pydantic MODELS (Request / Response)
# =========================================

class ChangeUsername(BaseModel):
    new_username: str


class ChangePassword(BaseModel):
    old_password: str
    new_password: str


class UserRegister(BaseModel):
    username: str
    email: str
    password: str


class UserLogin(BaseModel):
    username: str
    password: str


class TodoCreate(BaseModel):
    title: str
    subject: str
    due_date: str
    priority: str


class TodoToggle(BaseModel):
    done: bool


class GradeCreate(BaseModel):
    subject: str
    value: float
    description: Optional[str] = ""
    grade_type: Optional[str] = "written"


class DeleteAccountRequest(BaseModel):
    password: str


class FlashcardCreate(BaseModel):
    subject: str
    front: str
    back: str
    public: bool = False
    deck_id: Optional[str] = None


class FlashcardKnown(BaseModel):
    known: bool


class FlashcardDeckCreate(BaseModel):
    name: str
    color: Optional[str] = "#0d6efd"


class TimetableCreate(BaseModel):
    day: str
    period_number: Optional[int] = 1
    start_time: Optional[str] = ""
    end_time: Optional[str] = ""
    subject: str


class HomeworkCreate(BaseModel):
    subject: str
    day: str
    text: str
    due_date: Optional[str] = ""


class HomeworkToggle(BaseModel):
    done: bool


class UserSettingsUpdate(BaseModel):
    grade_system: str


class SubjectSettingsUpdate(BaseModel):
    subject: str
    oral_weight: float
    written_weight: float


class PeriodSettingsUpdate(BaseModel):
    period_number: int
    start_time: str
    end_time: str


class AdminRoleChange(BaseModel):
    role: str


# =========================================
# AUTH ROUTES
# =========================================

@app.post("/auth/register")
def register(user: UserRegister):
    db = get_db()
    cursor = db.cursor()

    try:
        user_id = generate_id()
        cursor.execute("""
        INSERT INTO users VALUES (?, ?, ?, ?, ?, ?)
        """, (
            user_id,
            user.username,
            user.email,
            hash_password(user.password),
            "user",
            datetime.utcnow().isoformat()
        ))
        # Create default settings for user
        cursor.execute("""
        INSERT OR IGNORE INTO user_settings VALUES (?, 'points')
        """, (user_id,))
        db.commit()
    except sqlite3.IntegrityError:
        raise HTTPException(status_code=400, detail="Username existiert bereits")

    return {"message": "Registrierung erfolgreich"}


@app.put("/auth/change-username/{user_id}")
def change_username(user_id: str, data: ChangeUsername):
    db = get_db()
    cursor = db.cursor()

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


@app.post("/auth/login")
def login(user: UserLogin):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    SELECT * FROM users WHERE username = ?
    """, (user.username,))
    user_db = cursor.fetchone()

    if user_db is None or user_db['password'] != hash_password(user.password):
        raise HTTPException(status_code=401, detail="Ungültiger Benutzername oder Passwort")

    # Ensure user settings exist
    cursor.execute("""
    INSERT OR IGNORE INTO user_settings VALUES (?, 'points')
    """, (user_db["id"],))
    db.commit()

    return {
        "message": "Login erfolgreich",
        "user_id": user_db["id"],
        "username": user_db["username"],
        "role": user_db["role"]
    }


@app.delete("/auth/delete-account/{user_id}")
def delete_account(user_id: str, data: DeleteAccountRequest):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("SELECT password FROM users WHERE id=?", (user_id,))
    user = cursor.fetchone()

    if not user:
        raise HTTPException(status_code=404, detail="User nicht gefunden")

    if user["password"] != hash_password(data.password):
        raise HTTPException(status_code=401, detail="Passwort ist falsch")

    cursor.execute("DELETE FROM todos WHERE user_id=?", (user_id,))
    cursor.execute("DELETE FROM grades WHERE user_id=?", (user_id,))
    cursor.execute("DELETE FROM timetable WHERE user_id=?", (user_id,))
    cursor.execute("DELETE FROM flashcards WHERE user_id=?", (user_id,))
    cursor.execute("DELETE FROM flashcard_decks WHERE user_id=?", (user_id,))
    cursor.execute("DELETE FROM files WHERE user_id=?", (user_id,))
    cursor.execute("DELETE FROM homework WHERE user_id=?", (user_id,))
    cursor.execute("DELETE FROM user_settings WHERE user_id=?", (user_id,))
    cursor.execute("DELETE FROM subject_settings WHERE user_id=?", (user_id,))
    cursor.execute("DELETE FROM period_settings WHERE user_id=?", (user_id,))
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
# USER SETTINGS ROUTES
# =========================================

@app.get("/settings/{user_id}")
def get_settings(user_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("SELECT * FROM user_settings WHERE user_id=?", (user_id,))
    settings = cursor.fetchone()

    if not settings:
        cursor.execute("INSERT INTO user_settings VALUES (?, 'points')", (user_id,))
        db.commit()
        return {"user_id": user_id, "grade_system": "points"}

    return dict(settings)


@app.put("/settings/{user_id}")
def update_settings(user_id: str, data: UserSettingsUpdate):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    INSERT OR REPLACE INTO user_settings VALUES (?, ?)
    """, (user_id, data.grade_system))
    db.commit()
    return {"message": "Einstellungen gespeichert"}


@app.get("/settings/{user_id}/subjects")
def get_subject_settings(user_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("SELECT * FROM subject_settings WHERE user_id=?", (user_id,))
    return [dict(row) for row in cursor.fetchall()]


@app.put("/settings/{user_id}/subjects")
def update_subject_settings(user_id: str, data: SubjectSettingsUpdate):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    SELECT id FROM subject_settings WHERE user_id=? AND subject=?
    """, (user_id, data.subject))
    existing = cursor.fetchone()

    if existing:
        cursor.execute("""
        UPDATE subject_settings SET oral_weight=?, written_weight=? WHERE id=?
        """, (data.oral_weight, data.written_weight, existing["id"]))
    else:
        cursor.execute("""
        INSERT INTO subject_settings VALUES (?, ?, ?, ?, ?)
        """, (generate_id(), user_id, data.subject, data.oral_weight, data.written_weight))

    db.commit()
    return {"message": "Fach-Einstellungen gespeichert"}


# =========================================
# PERIOD SETTINGS ROUTES
# =========================================

@app.get("/periods/{user_id}")
def get_periods(user_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    SELECT * FROM period_settings WHERE user_id=? ORDER BY period_number
    """, (user_id,))
    return [dict(row) for row in cursor.fetchall()]


@app.put("/periods/{user_id}")
def upsert_period(user_id: str, data: PeriodSettingsUpdate):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    SELECT id FROM period_settings WHERE user_id=? AND period_number=?
    """, (user_id, data.period_number))
    existing = cursor.fetchone()

    if existing:
        cursor.execute("""
        UPDATE period_settings SET start_time=?, end_time=? WHERE id=?
        """, (data.start_time, data.end_time, existing["id"]))
    else:
        cursor.execute("""
        INSERT INTO period_settings VALUES (?, ?, ?, ?, ?)
        """, (generate_id(), user_id, data.period_number, data.start_time, data.end_time))

    db.commit()
    return {"message": "Stundenzeit gespeichert"}


@app.delete("/periods/{user_id}/{period_number}")
def delete_period(user_id: str, period_number: int):
    db = get_db()
    cursor = db.cursor()
    cursor.execute("""
    DELETE FROM period_settings WHERE user_id=? AND period_number=?
    """, (user_id, period_number))
    db.commit()
    return {"message": "Stundenzeit gelöscht"}


# =========================================
# TIMETABLE ROUTES
# =========================================

@app.get("/timetable/{user_id}")
def get_timetable(user_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    SELECT id, day, period_number, start_time, end_time, subject
    FROM timetable
    WHERE user_id=?
    ORDER BY day, period_number, start_time
    """, (user_id,))

    return [dict(row) for row in cursor.fetchall()]


@app.post("/timetable/{user_id}")
def add_timetable_entry(user_id: str, entry: TimetableCreate):
    db = get_db()
    cursor = db.cursor()

    entry_id = generate_id()
    cursor.execute("""
    INSERT INTO timetable VALUES (?, ?, ?, ?, ?, ?, ?)
    """, (
        entry_id,
        user_id,
        entry.day,
        entry.period_number,
        entry.start_time,
        entry.end_time,
        entry.subject
    ))

    db.commit()
    return {"message": "Stunde hinzugefügt", "id": entry_id}


@app.put("/timetable/{user_id}/{entry_id}")
def update_timetable_entry(user_id: str, entry_id: str, entry: TimetableCreate):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    UPDATE timetable
    SET day=?, period_number=?, start_time=?, end_time=?, subject=?
    WHERE id=? AND user_id=?
    """, (
        entry.day,
        entry.period_number,
        entry.start_time,
        entry.end_time,
        entry.subject,
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


# =========================================
# HOMEWORK ROUTES
# =========================================

@app.get("/homework/{user_id}")
def get_homework(user_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    SELECT * FROM homework WHERE user_id=? ORDER BY due_date, subject
    """, (user_id,))
    return [dict(row) for row in cursor.fetchall()]


@app.post("/homework/{user_id}")
def add_homework(user_id: str, hw: HomeworkCreate):
    db = get_db()
    cursor = db.cursor()

    hw_id = generate_id()
    cursor.execute("""
    INSERT INTO homework VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    """, (
        hw_id,
        user_id,
        hw.subject,
        hw.day,
        hw.text,
        hw.due_date,
        0,
        datetime.utcnow().isoformat()
    ))

    db.commit()
    return {"message": "Hausaufgabe hinzugefügt", "id": hw_id}


@app.put("/homework/{user_id}/{hw_id}/toggle")
def toggle_homework(user_id: str, hw_id: str, data: HomeworkToggle):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    UPDATE homework SET done=? WHERE id=? AND user_id=?
    """, (int(data.done), hw_id, user_id))

    if cursor.rowcount == 0:
        raise HTTPException(status_code=404, detail="Hausaufgabe nicht gefunden")

    db.commit()
    return {"message": "Status aktualisiert"}


@app.delete("/homework/{user_id}/{hw_id}")
def delete_homework(user_id: str, hw_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    DELETE FROM homework WHERE id=? AND user_id=?
    """, (hw_id, user_id))

    if cursor.rowcount == 0:
        raise HTTPException(status_code=404, detail="Hausaufgabe nicht gefunden")

    db.commit()
    return {"message": "Hausaufgabe gelöscht"}


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

    return FileResponse(
        path=file_path,
        filename=file["original_name"],
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

    return [dict(row) for row in cursor.fetchall()]


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

    file_ext = file.filename.split(".")[-1].lower()
    if file_ext not in ALLOWED_EXTENSIONS:
        raise HTTPException(status_code=400, detail="Dateityp nicht erlaubt")

    content = await file.read()
    if len(content) > MAX_FILE_SIZE:
        raise HTTPException(status_code=400, detail="Datei zu groß (max 5MB)")

    file_id = generate_id()
    user_dir = os.path.join(UPLOAD_DIR, user_id)
    os.makedirs(user_dir, exist_ok=True)

    stored_filename = f"{file_id}.{file_ext}"
    file_path = os.path.join(user_dir, stored_filename)

    with open(file_path, "wb") as f:
        f.write(content)

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


# =========================================
# TODO ROUTES
# =========================================

@app.post("/todos/{user_id}")
def create_todo(user_id: str, todo: TodoCreate):
    db = get_db()
    cursor = db.cursor()

    todo_id = generate_id()
    cursor.execute("""
    INSERT INTO todos VALUES (?, ?, ?, ?, ?, ?, ?)
    """, (
        todo_id,
        user_id,
        todo.title,
        todo.subject,
        todo.due_date,
        todo.priority,
        0
    ))

    db.commit()
    return {"message": "To-Do erstellt", "id": todo_id}


@app.get("/todos/{user_id}")
def get_todos(user_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("SELECT * FROM todos WHERE user_id=? ORDER BY priority DESC, due_date", (user_id,))
    return [dict(row) for row in cursor.fetchall()]


@app.put("/todos/{user_id}/{todo_id}/toggle")
def toggle_todo(user_id: str, todo_id: str, data: TodoToggle):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    UPDATE todos SET done=? WHERE id=? AND user_id=?
    """, (int(data.done), todo_id, user_id))

    if cursor.rowcount == 0:
        raise HTTPException(status_code=404, detail="To-Do nicht gefunden")

    db.commit()
    return {"message": "Status aktualisiert"}


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


# =========================================
# GRADES ROUTES
# =========================================

@app.post("/grades/{user_id}")
def add_grade(user_id: str, grade: GradeCreate):
    db = get_db()
    cursor = db.cursor()

    grade_id = generate_id()
    cursor.execute("""
    INSERT INTO grades VALUES (?, ?, ?, ?, ?, ?, ?)
    """, (
        grade_id,
        user_id,
        grade.subject,
        grade.value,
        grade.description,
        datetime.utcnow().isoformat(),
        grade.grade_type
    ))

    db.commit()
    return {"message": "Note gespeichert", "id": grade_id}


@app.get("/grades/{user_id}")
def get_grades(user_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("SELECT * FROM grades WHERE user_id=? ORDER BY subject, date DESC", (user_id,))
    return [dict(row) for row in cursor.fetchall()]


@app.delete("/grades/{user_id}/{grade_id}")
def delete_grade(user_id: str, grade_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    DELETE FROM grades WHERE id=? AND user_id=?
    """, (grade_id, user_id))

    if cursor.rowcount == 0:
        raise HTTPException(status_code=404, detail="Note nicht gefunden")

    db.commit()
    return {"message": "Note gelöscht"}


# =========================================
# FLASHCARD DECK ROUTES
# =========================================

@app.get("/flashcard-decks/{user_id}")
def get_decks(user_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    SELECT d.*, COUNT(f.id) as card_count
    FROM flashcard_decks d
    LEFT JOIN flashcards f ON f.deck_id = d.id
    WHERE d.user_id=?
    GROUP BY d.id
    ORDER BY d.created_at DESC
    """, (user_id,))
    return [dict(row) for row in cursor.fetchall()]


@app.post("/flashcard-decks/{user_id}")
def create_deck(user_id: str, deck: FlashcardDeckCreate):
    db = get_db()
    cursor = db.cursor()

    deck_id = generate_id()
    cursor.execute("""
    INSERT INTO flashcard_decks VALUES (?, ?, ?, ?, ?)
    """, (
        deck_id,
        user_id,
        deck.name,
        deck.color,
        datetime.utcnow().isoformat()
    ))

    db.commit()
    return {"message": "Stapel erstellt", "id": deck_id}


@app.delete("/flashcard-decks/{user_id}/{deck_id}")
def delete_deck(user_id: str, deck_id: str):
    db = get_db()
    cursor = db.cursor()

    # Delete all cards in the deck
    cursor.execute("DELETE FROM flashcards WHERE deck_id=? AND user_id=?", (deck_id, user_id))
    cursor.execute("DELETE FROM flashcard_decks WHERE id=? AND user_id=?", (deck_id, user_id))

    if cursor.rowcount == 0:
        raise HTTPException(status_code=404, detail="Stapel nicht gefunden")

    db.commit()
    return {"message": "Stapel gelöscht"}


# =========================================
# FLASHCARDS ROUTES
# =========================================

@app.post("/flashcards/{user_id}")
def create_flashcard(user_id: str, card: FlashcardCreate):
    db = get_db()
    cursor = db.cursor()

    card_id = generate_id()
    cursor.execute("""
    INSERT INTO flashcards VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    """, (
        card_id,
        user_id,
        card.deck_id,
        card.subject,
        card.front,
        card.back,
        int(card.public),
        0
    ))

    db.commit()
    return {"message": "Karteikarte erstellt", "id": card_id}


@app.get("/flashcards/{user_id}")
def get_flashcards(user_id: str, deck_id: Optional[str] = None):
    db = get_db()
    cursor = db.cursor()

    if deck_id:
        cursor.execute("""
        SELECT * FROM flashcards WHERE (user_id=? OR public=1) AND deck_id=?
        """, (user_id, deck_id))
    else:
        cursor.execute("""
        SELECT * FROM flashcards WHERE user_id=? OR public=1
        """, (user_id,))

    return [dict(row) for row in cursor.fetchall()]


@app.put("/flashcards/{user_id}/{card_id}/known")
def update_card_known(user_id: str, card_id: str, data: FlashcardKnown):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    UPDATE flashcards SET known=? WHERE id=? AND user_id=?
    """, (int(data.known), card_id, user_id))

    if cursor.rowcount == 0:
        raise HTTPException(status_code=404, detail="Karte nicht gefunden")

    db.commit()
    return {"message": "Lernstatus aktualisiert"}


@app.delete("/flashcards/{user_id}/{card_id}")
def delete_flashcard(user_id: str, card_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    DELETE FROM flashcards WHERE id=? AND user_id=?
    """, (card_id, user_id))

    if cursor.rowcount == 0:
        raise HTTPException(status_code=404, detail="Karte nicht gefunden")

    db.commit()
    return {"message": "Karte gelöscht"}


# =========================================
# ADMIN ROUTES
# =========================================

@app.get("/admin/stats")
def admin_stats():
    db = get_db()
    cursor = db.cursor()

    cursor.execute("SELECT COUNT(*) as c FROM users")
    users = cursor.fetchone()["c"]

    cursor.execute("SELECT COUNT(*) as c FROM flashcards")
    cards = cursor.fetchone()["c"]

    cursor.execute("SELECT COUNT(*) as c FROM todos")
    todos = cursor.fetchone()["c"]

    cursor.execute("SELECT COUNT(*) as c FROM grades")
    grades = cursor.fetchone()["c"]

    cursor.execute("SELECT COUNT(*) as c FROM files")
    files = cursor.fetchone()["c"]

    cursor.execute("SELECT COUNT(*) as c FROM homework")
    homework = cursor.fetchone()["c"]

    return {
        "total_users": users,
        "total_flashcards": cards,
        "total_todos": todos,
        "total_grades": grades,
        "total_files": files,
        "total_homework": homework
    }


@app.get("/admin/users")
def get_all_users():
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC
    """)
    return [dict(row) for row in cursor.fetchall()]


@app.put("/admin/users/{target_user_id}/role")
def change_user_role(target_user_id: str, data: AdminRoleChange):
    db = get_db()
    cursor = db.cursor()

    if data.role not in ["user", "admin"]:
        raise HTTPException(status_code=400, detail="Ungültige Rolle")

    cursor.execute("""
    UPDATE users SET role=? WHERE id=?
    """, (data.role, target_user_id))

    if cursor.rowcount == 0:
        raise HTTPException(status_code=404, detail="User nicht gefunden")

    db.commit()
    return {"message": "Rolle aktualisiert"}


@app.delete("/admin/users/{target_user_id}")
def admin_delete_user(target_user_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("SELECT id FROM users WHERE id=?", (target_user_id,))
    if not cursor.fetchone():
        raise HTTPException(status_code=404, detail="User nicht gefunden")

    cursor.execute("DELETE FROM todos WHERE user_id=?", (target_user_id,))
    cursor.execute("DELETE FROM grades WHERE user_id=?", (target_user_id,))
    cursor.execute("DELETE FROM timetable WHERE user_id=?", (target_user_id,))
    cursor.execute("DELETE FROM flashcards WHERE user_id=?", (target_user_id,))
    cursor.execute("DELETE FROM flashcard_decks WHERE user_id=?", (target_user_id,))
    cursor.execute("DELETE FROM files WHERE user_id=?", (target_user_id,))
    cursor.execute("DELETE FROM homework WHERE user_id=?", (target_user_id,))
    cursor.execute("DELETE FROM user_settings WHERE user_id=?", (target_user_id,))
    cursor.execute("DELETE FROM subject_settings WHERE user_id=?", (target_user_id,))
    cursor.execute("DELETE FROM period_settings WHERE user_id=?", (target_user_id,))
    cursor.execute("DELETE FROM users WHERE id=?", (target_user_id,))
    db.commit()

    user_dir = os.path.join(UPLOAD_DIR, target_user_id)
    if os.path.exists(user_dir):
        for filename in os.listdir(user_dir):
            file_path = os.path.join(user_dir, filename)
            if os.path.isfile(file_path):
                os.remove(file_path)
        os.rmdir(user_dir)

    return {"message": "User gelöscht"}


if __name__ == "__main__":
    import uvicorn
    uvicorn.run(
        "backend:app",
        host="127.0.0.1",
        port=8000,
        reload=True
    )

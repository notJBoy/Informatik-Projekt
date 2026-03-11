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
        date TEXT
    )
    """)

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


class ChangeEmail(BaseModel):
    new_email: str


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


class GradeCreate(BaseModel):
    subject: str
    value: float
    description: Optional[str] = ""

class DeleteAccountRequest(BaseModel):
    password: str

    
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

    try:
        cursor.execute("""
        INSERT INTO users VALUES (?, ?, ?, ?, ?, ?)
        """, (
            generate_id(),
            user.username,
            user.email,
            hash_password(user.password),
            "user",
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

    cursor.execute("""
        UPDATE users
        SET email=?
        WHERE id=?
    """, (data.new_email, user_id))

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
    INSERT INTO timetable VALUES (?, ?, ?, ?, ?, ?, ?)
    """, (
        generate_id(),
        user_id,
        entry.day,
        entry.period,
        entry.time,
        entry.subject,
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
    SET day=?, period=?, time=?, subject=?, room=?
    WHERE id=? AND user_id=?
    """, (
        entry.day,
        entry.period,
        entry.time,
        entry.subject,
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
        INSERT INTO timetable VALUES (?, ?, ?, ?, ?, ?, ?)
        """, (
            generate_id(),
            user_id,
            entry.day,
            entry.period,
            entry.time,
            entry.subject,
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
        raise HTTPException(status_code=401, detail="Ungültiger Benutzername oder Passwort")

    return {
        "message": "Login erfolgreich",
        "user_id": user_db["id"],
        "username": user_db["username"]
    }


@app.delete("/auth/delete-account/{user_id}")
def delete_account(user_id: str, data: DeleteAccountRequest):
    db = get_db()
    cursor = db.cursor()

    # -------------------------
    # 1️⃣ User abrufen
    # -------------------------
    cursor.execute("SELECT password FROM users WHERE id=?", (user_id,))
    user = cursor.fetchone()

    if not user:
        raise HTTPException(status_code=404, detail="User nicht gefunden")

    # -------------------------
    # 2️⃣ Passwort prüfen
    # -------------------------
    if user["password"] != hash_password(data.password):
        raise HTTPException(status_code=401, detail="Passwort ist falsch")

    # -------------------------
    # 3️⃣ Abhängige Daten löschen
    # -------------------------
    cursor.execute("DELETE FROM todos WHERE user_id=?", (user_id,))
    cursor.execute("DELETE FROM grades WHERE user_id=?", (user_id,))
    cursor.execute("DELETE FROM timetable WHERE user_id=?", (user_id,))
    cursor.execute("DELETE FROM timetable_times WHERE user_id=?", (user_id,))
    cursor.execute("DELETE FROM flashcards WHERE user_id=?", (user_id,))
    cursor.execute("DELETE FROM files WHERE user_id=?", (user_id,))

    # -------------------------
    # 4️⃣ User löschen
    # -------------------------
    cursor.execute("DELETE FROM users WHERE id=?", (user_id,))
    db.commit()

    # -------------------------
    # 5️⃣ Upload-Ordner löschen
    # -------------------------
    user_dir = os.path.join(UPLOAD_DIR, user_id)
    if os.path.exists(user_dir):
        for filename in os.listdir(user_dir):
            file_path = os.path.join(user_dir, filename)
            if os.path.isfile(file_path):
                os.remove(file_path)
        os.rmdir(user_dir)

    return {"message": "Account erfolgreich gelöscht"}


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
# GRADES ROUTES
# =========================================

@app.post("/grades/{user_id}")
def add_grade(user_id: str, grade: GradeCreate):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    INSERT INTO grades VALUES (?, ?, ?, ?, ?, ?)
    """, (
        generate_id(),
        user_id,
        grade.subject,
        grade.value,
        grade.description,
        datetime.utcnow().isoformat()
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

@app.get("/admin/stats")
def admin_stats():
    db = get_db()
    cursor = db.cursor()

    cursor.execute("SELECT COUNT(*) FROM users")
    users = cursor.fetchone()[0]

    cursor.execute("SELECT COUNT(*) FROM flashcards")
    cards = cursor.fetchone()[0]

    return {
        "total_users": users,
        "total_flashcards": cards
    }

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(
        "backend:app",
        host="127.0.0.1",
        port=8000,
        reload=True
    )

# =========================================
# LearnHub Backend
# Autor: Backend-Team
# Technologie: FastAPI + SQLite
# =========================================

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
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS timetable (
        id TEXT PRIMARY KEY,
        user_id TEXT,
        day TEXT,
        time TEXT,
        subject TEXT
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


class GradeCreate(BaseModel):
    subject: str
    value: float
    description: Optional[str] = ""


class FlashcardCreate(BaseModel):
    subject: str
    front: str
    back: str
    public: bool = False
    
class TimetableCreate(BaseModel):
    day: str       # monday, tuesday, ...
    time: str      # "08:00 - 09:30"
    subject: str


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


# =========================================
# Timetable routes
# =========================================

@app.get("/timetable/{user_id}")
def get_timetable(user_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    SELECT id, day, time, subject
    FROM timetable
    WHERE user_id=?
    ORDER BY day, time
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
    INSERT INTO timetable VALUES (?, ?, ?, ?, ?)
    """, (
        generate_id(),
        user_id,
        entry.day,
        entry.time,
        entry.subject
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
    SET day=?, time=?, subject=?
    WHERE id=? AND user_id=?
    """, (
        entry.day,
        entry.time,
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

    return FileResponse(
        path=file_path,
        filename=file["original_name"]
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


@app.post("/files/upload/{user_id}")
def upload_file(
    user_id: str,
    subject: str,
    file: UploadFile = File(...)
):
    db = get_db()
    cursor = db.cursor()

    file_id = generate_id()
    user_dir = os.path.join(UPLOAD_DIR, user_id)
    os.makedirs(user_dir, exist_ok=True)

    file_ext = file.filename.split(".")[-1]
    stored_filename = f"{file_id}.{file_ext}"
    file_path = os.path.join(user_dir, stored_filename)

    # Datei speichern
    with open(file_path, "wb") as f:
        f.write(file.file.read())

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
def login(data: UserLogin):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    SELECT * FROM users WHERE username=? AND password=?
    """, (data.username, hash_password(data.password)))

    user = cursor.fetchone()
    if not user:
        raise HTTPException(status_code=401, detail="Login fehlgeschlagen")

    return {
        "user_id": user["id"],
        "username": user["username"],
        "role": user["role"]
    }


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


# =========================================
# FLASHCARDS ROUTES
# =========================================

@app.post("/flashcards/{user_id}")
def create_flashcard(user_id: str, card: FlashcardCreate):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    INSERT INTO flashcards VALUES (?, ?, ?, ?, ?, ?)
    """, (
        generate_id(),
        user_id,
        card.subject,
        card.front,
        card.back,
        int(card.public)
    ))

    db.commit()
    return {"message": "Karteikarte erstellt"}


@app.get("/flashcards/{user_id}")
def get_flashcards(user_id: str):
    db = get_db()
    cursor = db.cursor()

    cursor.execute("""
    SELECT * FROM flashcards WHERE user_id=? OR public=1
    """, (user_id,))
    return cursor.fetchall()


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

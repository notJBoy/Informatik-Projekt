# LearnHub

Eine anwendungsbasierte Lernplattform für Schüler und Studierende mit Karteikarten, Stundenplan, Notenverwaltung und Dateiorganisation. Ermöglicht effizientes Lernen durch personalisierte Dashboards und kollaborative Funktionen.

***

## Inhaltsübersicht
- Ziel des Projekts
- Anforderungen
- Projektstruktur
- Module & Zuständigkeiten
- Installation & Start
- Arbeitsweise & Regeln
- Projektstatus

***

## 1. Ziel des Projekts

**LearnHub** löst das Chaos bei Lernorganisation: Schüler und Studierende haben ihre Karteikarten, Noten, Hausaufgaben, Stundenpläne und Lernmaterialien stets übersichtlich an einem Ort. Die Plattform zeigt auf dem Dashboard sofort den Lernfortschritt, offene To-Dos und kommende Klausuren – perfekt für den Schul-/Uni-Alltag.

Das Endergebnis ist eine **responsive Java-App** mit Login, Admin-Bereich und Premium-Features, die in Gruppen im Informatik-Leistungskurs JS1 umgesetzt wird.

***

## 2. Anforderungen

### MUSS
- Login/Registrierung mit Benutzername + Passwort
- Dashboard mit Stundenplan-Widget, Lernfortschritt und nächsten To-Dos
- Karteikarten: Erstellen/Lernen (CRUD + Lernmodi)
- Noteneingabe (Punkte 0-15 / Noten 1-6) mit Durchschnittsberechnung
- Datei-Upload mit Fachzuordnung
- Admin-Panel: Nutzerübersicht + Rollenverwaltung

### SOLL
- Desktop
- Dunkel-/Hellmodus
- To-Do-Liste mit Fälligkeitsdaten
- Lernfortschritt-Balken + Badges (Streaks)
- Geteilte Karteikarten-Sets (öffentlich/privat)

### KANN
- Premium-Features (erweiterte Statistiken, mehr Speicher)
- Onboarding-Wizard beim ersten Login
- Export-Funktionen (PDF/CSV)
- Prüfungsmodus mit Timer für Karteikarten

***

## 3. Projektstruktur
```
learnhub/
├── src/
│   ├── main/
│   │   ├── java/
│   │   │   └── com/
│   │   │       └── learnhub/
│   │   │           ├── controller/
│   │   │           │   ├── AuthController.java        # Login/Registrierung
│   │   │           │   ├── DashboardController.java   # Dashboard-Widgets
│   │   │           │   ├── FlashcardsController.java  # Karteikarten-Logik
│   │   │           │   ├── GradesController.java      # Notenverwaltung
│   │   │           │   ├── TimetableController.java   # Stundenplan
│   │   │           │   ├── FilesController.java       # Datei-Upload/Verwaltung
│   │   │           │   ├── TodosController.java       # To-Do-Liste
│   │   │           │   ├── AdminController.java       # Admin-Funktionen
│   │   │           │   └── StorageController.java     # localStorage Helper
│   │   │           ├── service/
│   │   │           │   ├── AuthService.java           # Authentifizierungslogik
│   │   │           │   ├── FlashcardsService.java     # Logik für Karteikarten
│   │   │           │   ├── GradesService.java         # Notenlogik
│   │   │           │   ├── TimetableService.java      # Stundenplanlogik
│   │   │           │   └── FilesService.java          # Datei-Upload-Logik
│   │   │           └── model/
│   │   │               ├── User.java                  # Benutzer-Datenmodell
│   │   │               ├── Subject.java               # Fach-Datenmodell
│   │   │               ├── Flashcard.java             # Karteikarten-Datenmodell
│   │   │               └── Grade.java                 # Noten-Datenmodell
│   │   ├── resources/
│   │   │   ├── static/
│   │   │   │   ├── css/
│   │   │   │   │   ├── main.css                      # Global Styles
│   │   │   │   │   ├── dashboard.css                 # Widget-Layout
│   │   │   │   │   └── components.css                # Buttons, Cards, etc.
│   │   │   │   ├── js/
│   │   │   │   │   └── app.js                        # App-Initialisierung + Routing
│   │   │   │   └── images/
│   │   │   ├── templates/
│   │   │   │   └── index.html                        # Einstiegspunkt
│   │   │   └── application.properties                # Spring Boot Konfiguration
│   └── test/
│       ├── java/
│       │   └── com/
│       │       └── learnhub/
│       │           ├── controller/
│       │           ├── service/
│       │           └── model/
├── data/                                              # Demo-Daten (JSON oder DB)
│   ├── users.json
│   └── subjects.json
├── assets/                                            # Icons, Bilder
└── README.md                                          # Diese Datei
```

***

Ah, jetzt verstehe ich! Du möchtest die Struktur und die Zuständigkeiten der Module in Java übernehmen, basierend auf einer Webanwendung, aber die Schnittstellen und Funktionalitäten in einer Java-Backend-Struktur umsetzen.

Hier ist eine angepasste Version für eine Java-Anwendung, die den gleichen Zweck wie die JavaScript-Module verfolgt, aber unter Verwendung von Java-Klassen und -Methoden:

---

### Modul: **Authentifizierung**

**Zweck:** Benutzerverwaltung, Login/Logout, Session-Handling
**Verantwortlich:** [Name1]
**Dateien:**

* `src/main/java/com/learnhub/controller/AuthController.java`
* `src/main/java/com/learnhub/service/AuthService.java`
* `src/main/java/com/learnhub/model/User.java`

**Schnittstellen (öffentliche Funktionen):**

* `login(username, password) → boolean` (Login prüfen/speichern)
* `register(userData) → boolean` (Neuen User anlegen)
* `getCurrentUser() → User|null` (Aktueller Benutzer)
* `logout() → void` (Session löschen)
* `isAdmin(user) → boolean` (Admin-Rechte prüfen)

**Beispiel-Implementierung**:

```java
@Service
public class AuthService {

    private final UserRepository userRepository;
    private final SessionService sessionService;

    public AuthService(UserRepository userRepository, SessionService sessionService) {
        this.userRepository = userRepository;
        this.sessionService = sessionService;
    }

    public boolean login(String username, String password) {
        // Prüft Login-Daten und speichert die Session
    }

    public boolean register(User userData) {
        // Neuen Benutzer anlegen
    }

    public User getCurrentUser() {
        // Gibt den aktuellen Benutzer aus der Session zurück
    }

    public void logout() {
        // Löscht die Session des Benutzers
    }

    public boolean isAdmin(User user) {
        // Prüft, ob der Benutzer Admin-Rechte hat
    }
}
```

---

### Modul: **Dashboard**

**Zweck:** Hauptübersicht mit Widgets (Stundenplan, To-Dos, Fortschritt)
**Verantwortlich:** [Name2]
**Dateien:**

* `src/main/java/com/learnhub/controller/DashboardController.java`
* `src/main/java/com/learnhub/service/DashboardService.java`
* `src/main/resources/templates/dashboard.html`

**Schnittstellen:**

* `loadDashboard() → void` (Alle Widgets laden)
* `updateProgress(subjectId) → void` (Fortschrittsbalken aktualisieren)
* `getNextTodos(count) → List<Todo>` (Nächste Aufgaben abrufen)

**Beispiel-Implementierung**:

```java
@Controller
public class DashboardController {

    private final DashboardService dashboardService;

    public DashboardController(DashboardService dashboardService) {
        this.dashboardService = dashboardService;
    }

    @GetMapping("/dashboard")
    public String loadDashboard(Model model) {
        model.addAttribute("widgets", dashboardService.loadWidgets());
        return "dashboard";
    }

    @PostMapping("/dashboard/updateProgress")
    public void updateProgress(@RequestParam("subjectId") Long subjectId) {
        dashboardService.updateProgress(subjectId);
    }

    @GetMapping("/dashboard/todos")
    public List<Todo> getNextTodos(@RequestParam("count") int count) {
        return dashboardService.getNextTodos(count);
    }
}
```

---

### Modul: **Karteikarten**

**Zweck:** Erstellen, Lernen, Statistiken von Karteikarten-Sets
**Verantwortlich:** [Name3]
**Dateien:**

* `src/main/java/com/learnhub/controller/FlashcardsController.java`
* `src/main/java/com/learnhub/service/FlashcardsService.java`
* `src/main/java/com/learnhub/model/Flashcard.java`

**Schnittstellen:**

* `createCard(front, back, subjectId) → String` (Karten-ID erstellen)
* `startLearning(setId, mode) → void` (Lernsession starten)
* `getStats(setId) → FlashcardStats` (Statistiken abfragen)
* `markPublic(setId, isPublic) → void` (Set öffentlich machen)

**Beispiel-Implementierung**:

```java
@Service
public class FlashcardsService {

    private final FlashcardRepository flashcardRepository;

    public FlashcardsService(FlashcardRepository flashcardRepository) {
        this.flashcardRepository = flashcardRepository;
    }

    public String createCard(String front, String back, Long subjectId) {
        // Karteikarte erstellen und ID zurückgeben
    }

    public void startLearning(Long setId, String mode) {
        // Lernsession starten
    }

    public FlashcardStats getStats(Long setId) {
        // Erfolgsquote und Lernzeit abrufen
    }

    public void markPublic(Long setId, boolean isPublic) {
        // Karteikarten-Set öffentlich oder privat setzen
    }
}
```

---

### Modul: **Noten**

**Zweck:** Noteneingabe, Durchschnittsberechnung, Trends
**Verantwortlich:** [Name1]
**Dateien:**

* `src/main/java/com/learnhub/controller/GradesController.java`
* `src/main/java/com/learnhub/service/GradesService.java`
* `src/main/java/com/learnhub/model/Grade.java`

**Schnittstellen:**

* `addGrade(subjectId, value, type) → void` (Note hinzufügen)
* `getAverage(subjectId) → double` (Durchschnitt berechnen)
* `getAllGrades(subjectId) → List<Grade>` (Alle Noten abrufen)

**Beispiel-Implementierung**:

```java
@Service
public class GradesService {

    private final GradeRepository gradeRepository;

    public GradesService(GradeRepository gradeRepository) {
        this.gradeRepository = gradeRepository;
    }

    public void addGrade(Long subjectId, double value, String type) {
        // Logik zum Hinzufügen einer Note
    }

    public double getAverage(Long subjectId) {
        // Berechnung des Durchschnitts
    }

    public List<Grade> getAllGrades(Long subjectId) {
        // Alle Noten für ein Fach abrufen
    }
}
```

---

### Modul: **Stundenplan**

**Zweck:** Anzeige und Verwaltung des Wochen-/Monatsplans
**Verantwortlich:** [Name2]
**Dateien:**

* `src/main/java/com/learnhub/controller/TimetableController.java`
* `src/main/java/com/learnhub/service/TimetableService.java`
* `src/main/java/com/learnhub/model/TimetableEntry.java`

**Schnittstellen:**

* `setSchedule(day, slot, subject) → void` (Eintrag setzen)
* `getTodaySchedule() → List<TimetableEntry>` (Heutige Kurse abrufen)
* `getWeekSchedule() → List<TimetableEntry>` (Stundenplan der Woche abrufen)

**Beispiel-Implementierung**:

```java
@Service
public class TimetableService {

    private final TimetableRepository timetableRepository;

    public TimetableService(TimetableRepository timetableRepository) {
        this.timetableRepository = timetableRepository;
    }

    public void setSchedule(String day, String slot, String subject) {
        // Stundenplan für einen Tag setzen
    }

    public List<TimetableEntry> getTodaySchedule() {
        // Heutigen Stundenplan abrufen
    }

    public List<TimetableEntry> getWeekSchedule() {
        // Stundenplan für die gesamte Woche abrufen
    }
}
```

---

### Modul: **Dateien**

**Zweck:** Upload, Organisation und Suche von Lernmaterial
**Verantwortlich:** [Name3]
**Dateien:**

* `src/main/java/com/learnhub/controller/FilesController.java`
* `src/main/java/com/learnhub/service/FilesService.java`
* `src/main/java/com/learnhub/model/File.java`

**Schnittstellen:**

* `uploadFile(file, subjectId, tags) → String` (Datei hochladen)
* `getFiles(subjectId) → List<File>` (Dateien für ein Fach abrufen)
* `searchFiles(query) → List<File>` (Dateien suchen)

**Beispiel-Implementierung**:

```java
@Service
public class FilesService {

    private final FileRepository fileRepository;

    public FilesService(FileRepository fileRepository) {
        this.fileRepository = fileRepository;
    }

    public String uploadFile(MultipartFile file, Long subjectId, List<String> tags) {
        // Logik zum Hochladen einer Datei
    }

    public List<File> getFiles(Long subjectId) {
        // Dateien für ein Fach abrufen
    }

    public List<File> searchFiles(String query) {
        // Suche nach Dateien durchführen
    }
}
```

---

### Modul: **Admin**

**Zweck:** Nutzer- und Abo-Verwaltung für Administratoren
**Verantwortlich:** [Name1]
**Dateien:**

* `src/main/java/com/learnhub/controller/AdminController.java`
* `src/main/java/com/learnhub/service/AdminService.java`
* `src/main/java/com/learnhub/model/UserRole.java`

**Schnittstellen:**

* `getAllUsers() → List<User>` (Alle Nutzer abrufen)
* `setRole(userId, role) → void` (Rolle ändern)
* `getUserStats

***

## 5. Installation & Start

1. Repository klonen: `git clone [URL]`
2. Browser öffnen: `index.html` direkt öffnen (kein Server nötig)
3. Demo-Login: `admin/admin` oder `user/user`
4. Daten werden in `localStorage` gespeichert

**Entwicklung:** Live-Server empfohlen (`npx live-server`)

***

## 6. Arbeitsweise & Regeln

**Git-Branching:**
```
main     → produktive Version
develop  → Integration
feature/ → neue Features ([Name]-flashcards)
```

**Commits:** `git commit -m "feat: karteikarten lernmodus hinzugefügt"`
**Stand-ups:** Mo/Mi/Fr 15 Min (Discord/Slack)
**Code Review:** Jeder PR muss von 1 anderem genehmigt werden

**Qualitätsregeln:**
- Semikolons überall
- 2 Spaces Einrückung
- ESLint aktivieren
- Konsistente Namenskonventionen (camelCase)

***

## 7. Projektstatus

| Sprint | Features | Status | Verantwortlich |
|--------|----------|--------|---------------|
| Sprint 1 | Login + Dashboard | ⏳ geplant | Name1+Name2 |
| Sprint 2 | Karteikarten + Noten | ⏳ geplant | Name3+Name1 |
| Sprint 3 | Stundenplan + Dateien | ⏳ geplant | Name2+Name3 |
| Sprint 4 | Admin + Polish | ⏳ geplant | Alle |

**Nächster Meilenstein:** Sprint 1 fertig (Ende Woche 2)

***

**🚀 Bereit zum Start!** Ersetzt die [NameX]-Platzhalter mit euren Namen und legt los. Wer übernimmt Sprint 1?

                <!-- Overview Content -->
                <div id="overview" class="view-content">
                    <div class="content-header">
                        <h1>Willkommen zurück! 👋</h1>
                        <p>Hier ist deine Lernübersicht für heute</p>
                    </div>

                    <div class="dashboard-grid">
                        <!-- Stundenplan Widget -->
                        <div class="widget">
                            <div class="widget-header">
                                <div class="widget-title">
                                    <span class="widget-icon">📅</span>
                                    Heute im Stundenplan
                                </div>
                                <button class="widget-action" data-view="timetable">→</button>
                            </div>
                            <div class="timetable" id="overviewTimetable">
                                <p style="color:var(--color-text-muted);text-align:center;padding:0.5rem;">Lädt…</p>
                            </div>
                        </div>

                        <!-- Noten Widget -->
                        <div class="widget">
                            <div class="widget-header">
                                <div class="widget-title">
                                    <span class="widget-icon">📝</span>
                                    Aktuelle Noten
                                </div>
                                <button class="widget-action" data-view="grades">→</button>
                            </div>
                            <div class="grades-list" id="overviewGrades">
                                <p style="color:var(--color-text-muted);text-align:center;padding:0.5rem;">Lädt…</p>
                            </div>
                        </div>

                        <!-- To-Do Widget -->
                        <div class="widget">
                            <div class="widget-header">
                                <div class="widget-title">
                                    <span class="widget-icon">✅</span>
                                    Offene Aufgaben
                                </div>
                                <button class="widget-action" data-view="todos">→</button>
                            </div>
                            <div class="todo-list" id="overviewTodos">
                                <p style="color:var(--color-text-muted);text-align:center;padding:0.5rem;">Lädt…</p>
                            </div>
                        </div>

                        <!-- Hausaufgaben Widget -->
                        <div class="widget">
                            <div class="widget-header">
                                <div class="widget-title">
                                    <span class="widget-icon">📚</span>
                                    Hausaufgaben
                                </div>
                                <button class="widget-action" data-view="homework">→</button>
                            </div>
                            <div class="todo-list" id="overviewHomeworks">
                                <p style="color:var(--color-text-muted);text-align:center;padding:0.5rem;">Lädt…</p>
                            </div>
                        </div>

                        <!-- Klassenarbeiten Widget -->
                        <div class="widget">
                            <div class="widget-header">
                                <div class="widget-title">
                                    <span class="widget-icon">📝</span>
                                    Klassenarbeiten
                                </div>
                                <button class="widget-action" data-view="exams">→</button>
                            </div>
                            <div class="grades-list" id="overviewExams">
                                <p style="color:var(--color-text-muted);text-align:center;padding:0.5rem;">Lädt…</p>
                            </div>
                        </div>

                        <!-- Kalender Widget -->
                        <div class="widget">
                            <div class="widget-header">
                                <div class="widget-title">
                                    <span class="widget-icon">📆</span>
                                    Kommende Termine
                                </div>
                                <button class="widget-action" data-view="calendar">→</button>
                            </div>
                            <div class="grades-list" id="overviewCalendar">
                                <p style="color:var(--color-text-muted);text-align:center;padding:0.5rem;">Lädt…</p>
                            </div>
                        </div>


                        <!-- Karteikarten Widget -->
                        <div class="widget">
                            <div class="widget-header">
                                <div class="widget-title">
                                    <span class="widget-icon">🎴</span>
                                    Karteikarten lernen
                                </div>
                                <button class="widget-action" data-view="flashcards">→</button>
                            </div>
                            <div class="flashcard" id="flashcard" onclick="flipCard('flashcard')">
                                <div class="flashcard-inner" id="flashcardInner">
                                    <div class="flashcard-front">
                                        <p><strong>Frage:</strong> Was ist ein Automat?</p>
                                    </div>
                                    <div class="flashcard-back">
                                        <p>Ein abstraktes Modell eines Rechners mit endlich vielen Zuständen</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flashcard-nav">
                                <button class="flashcard-btn" onclick="previousCard(); event.stopPropagation();">← Zurück</button>
                                <button class="flashcard-btn" onclick="nextCard(); event.stopPropagation();">Weiter →</button>
                            </div>
                        </div>

                        <!-- Dateien Widget -->
                        <div class="widget">
                            <div class="widget-header">
                                <div class="widget-title">
                                    <span class="widget-icon">📁</span>
                                    Letzte Dateien
                                </div>
                                <button class="widget-action" data-view="files">→</button>
                            </div>
                            <div class="files-list" id="overviewFiles">
                                <p style="color:var(--color-text-muted);text-align:center;padding:0.5rem;">Lädt…</p>
                            </div>
                        </div>

                        <!-- Admin Nachrichten Widget -->
                        <div class="widget">
                            <div class="widget-header">
                                <div class="widget-title">
                                    <span class="widget-icon">💬</span>
                                    <span>Admin Nachrichten</span>
                                </div>
                                <button class="widget-action" data-view="admin-messages">→</button>
                            </div>
                            <div class="widget-body">
                                <div class="messages-list" id="overviewMessages">
                                    <p style="color:var(--color-text-muted);text-align:center;padding:0.5rem;">Lädt…</p>
                                </div>
                            </div>
                        </div>

                        <!-- Admin Panel Widget -->
                        <div class="widget">
                            <div class="widget-header">
                                <div class="widget-title">
                                    <span class="widget-icon">⚙️</span>
                                    Admin Statistiken
                                </div>
                                <button class="widget-action" data-view="admin">→</button>
                            </div>
                            <div class="admin-stats">
                                <div class="stat-card">
                                    <div class="stat-value">156</div>
                                    <div class="stat-label">Aktive User</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-value">24</div>
                                    <div class="stat-label">Kurse</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-value">89%</div>
                                    <div class="stat-label">Abschlussrate</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-value">4.8</div>
                                    <div class="stat-label">Ø Bewertung</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

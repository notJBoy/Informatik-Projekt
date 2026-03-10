                <!-- Stundenplan Detail View -->
                <div id="timetable" class="view-content" style="display: none;">
                    <div class="content-header">
                        <h1>📅 Stundenplan</h1>
                        <p>Deine Wochenübersicht – heutiger Tag ist hervorgehoben</p>
                    </div>

                    <!-- Stundenplan Widget -->
                    <div class="widget">
                        <div class="widget-header">
                            <div class="widget-title">📋 Wochenplan</div>
                            <button id="timetableEditBtn" class="btn-primary" onclick="toggleTimetableEdit()">✏️ Bearbeiten</button>
                        </div>

                        <!-- Ansichtsmodus -->
                        <div id="timetableViewMode">
                            <div id="timetableGrid"></div>
                        </div>

                        <!-- Bearbeitungsmodus -->
                        <div id="timetableEditMode" style="display:none;">
                            <div class="tt-edit-section">
                                <div class="tt-edit-section-title">⏰ Stundenbeginn konfigurieren</div>
                                <div id="periodTimesEditor"></div>
                            </div>
                            <div class="tt-edit-section" style="margin-top:2rem;">
                                <div class="tt-edit-section-title">📝 Fächer & Räume eintragen</div>
                                <div id="timetableEditor" style="overflow-x:auto;"></div>
                            </div>
                            <div style="margin-top:1.5rem; display:flex; gap:0.75rem; flex-wrap:wrap;">
                                <button class="btn-primary" onclick="saveTimetable()">💾 Speichern</button>
                                <button class="btn-secondary" onclick="cancelTimetableEdit()">Abbrechen</button>
                            </div>
                        </div>
                    </div>

                    <!-- Hausaufgaben Zusammenfassung -->
                    <div class="widget" style="margin-top:1.5rem;">
                        <div class="widget-header">
                            <div class="widget-title">📚 Hausaufgaben</div>
                        </div>
                        <div id="homeworkGridTimetable"></div>
                    </div>
                </div>

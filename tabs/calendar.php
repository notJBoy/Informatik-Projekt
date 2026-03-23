                <!-- Calendar Detail View -->
                <div id="calendar" class="view-content" style="display: none;">
                    <div class="content-header">
                        <h1>📆 Kalender</h1>
                        <p>Sieh dir alle Termine, Klassenarbeiten und Aufgaben in einer Monatsübersicht an und füge eigene Ereignisse hinzu.</p>
                    </div>

                    <!-- Monatsübersicht -->
                    <div class="widget">
                        <div class="widget-header">
                            <div class="widget-title">Monatsübersicht</div>
                            <button class="btn-primary" onclick="openCalendarQuickAddModal()">Ereignis hinzufügen</button>
                        </div>
                        <div id="calendarLayout" style="padding:1rem;">
                            <div id="calendarContainer">
                                <div id="calendarControls" style="margin-bottom:0.5rem; display:flex; justify-content:space-between; align-items:center;">
                                    <button class="btn-secondary" onclick="prevMonth()">◀</button>
                                    <span id="calendarMonthLabel" style="font-weight:600"></span>
                                    <button class="btn-secondary" onclick="nextMonth()">▶</button>
                                </div>
                                <div id="calendarSelectedHint">Ausgewählt: -</div>
                                <table id="calendarGrid" style="width:100%; border-collapse:collapse;"></table>
                            </div>
                            <div id="calendarDayEvents" style="padding:1rem;">
                                <h3 id="calendarDayLabel"></h3>
                                <div id="calendarEventList"></div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-overlay" id="calendarQuickAddModal">
                        <div class="modal-box" style="max-width: 500px;">
                            <h2>📌 Ereignis hinzufügen</h2>
                            <div class="modal-section">
                                <h3>Ausgewählter Tag</h3>
                                <p id="calendarQuickAddDateLabel" style="color:var(--color-text-secondary); margin-bottom:0.4rem;">-</p>
                                <div class="calendar-title-input-row">
                                    <span id="calendarTitleColorPreview" class="calendar-title-preview-dot" onclick="document.getElementById('quickEventColor').click()" title="Farbe auswählen"></span>
                                    <input type="text" id="quickEventTitle" placeholder="Titel..." autocomplete="off" oninput="updateCalendarTitleSuggestions(this.value)" onfocus="updateCalendarTitleSuggestions(this.value)">
                                    <input type="color" id="quickEventColor" value="#0d6efd" onchange="updateCalendarTitlePreview(document.getElementById('quickEventTitle')?.value || '')">
                                </div>
                                <div id="calendarTitleSuggestions" class="calendar-title-suggestions"></div>
                                <input type="text" id="quickEventDesc" placeholder="Beschreibung (optional)...">
                                <label class="calendar-repeat-label">Uhrzeit (optional):</label>
                                <div class="calendar-time-row">
                                    <div class="calendar-time-field">
                                        <label class="calendar-repeat-label" for="quickEventStartTime">Von</label>
                                        <input type="time" id="quickEventStartTime">
                                    </div>
                                    <div class="calendar-time-field">
                                        <label class="calendar-repeat-label" for="quickEventEndTime">Bis</label>
                                        <input type="time" id="quickEventEndTime">
                                    </div>
                                </div>
                                <label class="calendar-repeat-label" for="quickEventRecurrence">Wiederholung</label>
                                <select id="quickEventRecurrence" class="calendar-repeat-select">
                                    <option value="none">Keine</option>
                                    <option value="weekly">Wöchentlich</option>
                                    <option value="monthly">Monatlich</option>
                                    <option value="yearly">Jährlich</option>
                                </select>
                            </div>
                            <div class="modal-footer">
                                <button class="modal-btn modal-btn-primary" onclick="submitCalendarQuickAdd()">Speichern</button>
                                <button class="modal-btn modal-btn-close" onclick="closeCalendarQuickAddModal()">Abbrechen</button>
                            </div>
                        </div>
                    </div>

                    <div class="modal-overlay" id="calendarDeleteModal">
                        <div class="modal-box" style="max-width: 460px;">
                            <h2>🗑️ Wiederkehrenden Termin löschen</h2>
                            <div class="modal-section">
                                <p id="calendarDeleteModalText" style="color:var(--color-text-secondary); margin:0;">Wie soll der Termin gelöscht werden?</p>
                            </div>
                            <div class="modal-footer">
                                <button class="modal-btn modal-btn-primary" onclick="confirmCalendarDelete('occurrence')">Nur diesen Termin</button>
                                <button class="modal-btn modal-btn-danger" onclick="confirmCalendarDelete('series')">Ganze Serie</button>
                                <button class="modal-btn modal-btn-close" onclick="closeCalendarDeleteModal()">Abbrechen</button>
                            </div>
                        </div>
                    </div>
                </div>
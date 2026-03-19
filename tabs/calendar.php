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
                                <input type="text" id="quickEventTitle" placeholder="Titel...">
                                <input type="text" id="quickEventDesc" placeholder="Beschreibung (optional)...">
                            </div>
                            <div class="modal-footer">
                                <button class="modal-btn modal-btn-primary" onclick="submitCalendarQuickAdd()">Speichern</button>
                                <button class="modal-btn modal-btn-close" onclick="closeCalendarQuickAddModal()">Abbrechen</button>
                            </div>
                        </div>
                    </div>
                </div>
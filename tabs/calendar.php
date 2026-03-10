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
                        </div>
                        <div id="calendarContainer" style="padding:1rem;">
                            <div id="calendarControls" style="margin-bottom:0.5rem; display:flex; justify-content:space-between; align-items:center;">
                                <button class="btn-secondary" onclick="prevMonth()">◀</button>
                                <span id="calendarMonthLabel" style="font-weight:600"></span>
                                <button class="btn-secondary" onclick="nextMonth()">▶</button>
                            </div>
                            <table id="calendarGrid" style="width:100%; border-collapse:collapse;"></table>
                        </div>

                        <!-- Liste der Ereignisse eines Tages -->
                        <div id="calendarDayEvents" style="padding:1rem; display:none;">
                            <h3 id="calendarDayLabel"></h3>
                            <div id="calendarEventList"></div>
                        </div>
                    </div>

                    <!-- Termin hinzufügen -->
                    <div class="widget" style="margin-top:1.5rem;">
                        <div class="widget-header">
                            <div class="widget-title">Neues Ereignis</div>
                        </div>
                        <div class="input-group" style="flex-wrap: wrap; gap: 0.5rem;">
                            <input type="text" id="eventTitle" placeholder="Titel..." style="flex:2 1 180px;">
                            <input type="date" id="eventDate" style="flex:1 1 140px;">
                            <input type="text" id="eventDesc" placeholder="Beschreibung (optional)" style="flex:2 1 180px;">
                            <button class="btn-primary" onclick="addCalendarEvent()">+ Hinzufügen</button>
                        </div>
                    </div>
                </div>
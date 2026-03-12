                <!-- Noten Detail View -->
                <div id="grades" class="view-content" style="display: none;">
                    <div class="content-header">
                        <h1>📝 Notenübersicht</h1>
                        <p>Alle deine Fächer und Noten </p>
                    </div>
                    <div class="widget">
                        <div class="widget-header">
                            <div class="widget-title">Note hinzufügen</div>
                        </div>
                        <div class="input-group">
                            <input type="text" id="gradeSubject" placeholder="Fach eingeben...">
                            <input type="number" id="gradeValue" placeholder="Punkte (0–15)" min="0" max="15" step="1">
                            <input type="number" id="gradeWeight" placeholder="Gewichtung (z.B. 2)" min="1" step="1" value="1">
                            <input type="text" id="gradeDescription" placeholder="s/m/p">
                            <button class="btn-primary" onclick="addGrade()">Hinzufügen</button>
                        </div>
                        <div class="grades-list" id="gradesList" style="margin-top: 1.5rem;">
                            <p style="color:var(--color-text-muted);text-align:center;padding:1rem;">Wird geladen...</p>
                        </div>
                    </div>
                </div>

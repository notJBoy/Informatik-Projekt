                <!-- Fächer Detail View -->
                <div id="subjects" class="view-content" style="display: none;">
                    <div class="content-header">
                        <h1>📚 Fächer</h1>
                        <p>Verwalte deine Fächer und Farben</p>
                    </div>
                    <div class="widget">
                        <div class="widget-header">
                            <div class="widget-title">Neues Fach hinzufügen</div>
                        </div>
                        <div class="input-group">
                            <input type="text" id="subjectName" placeholder="Fach eingeben...">
                            <input type="color" id="subjectColor" value="#0d6efd">
                            <button class="btn-primary" onclick="addSubject()">Hinzufügen</button>
                        </div>
                        <div class="subjects-list" id="subjectsList" style="margin-top: 1.5rem;">
                            <p style="color:var(--color-text-muted);text-align:center;padding:1rem;">Wird geladen...</p>
                        </div>
                    </div>
                </div>

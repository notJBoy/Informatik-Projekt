                <!-- Admin Detail View -->
                <div id="admin" class="view-content" style="display: none;">
                    <div class="content-header">
                        <h1>⚙️ Admin Panel</h1>
                        <p>Systemverwaltung und Live-Statistiken</p>
                    </div>
                    <div class="dashboard-grid">
                        <div class="widget">
                            <div class="widget-header">
                                <div class="widget-title">Kern-KPIs</div>
                                <button class="btn-primary" onclick="loadAdminPanel()">Aktualisieren</button>
                            </div>
                            <div class="admin-stats">
                                <div class="stat-card">
                                    <div class="stat-value" id="adminTotalUsers">-</div>
                                    <div class="stat-label">Nutzer gesamt</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-value" id="adminNewUsers30d">-</div>
                                    <div class="stat-label">Neue Nutzer (30 Tage)</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-value" id="adminDauWauMau">-</div>
                                    <div class="stat-label">DAU / WAU / MAU</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-value" id="adminTodoRate">-</div>
                                    <div class="stat-label">To-Do Erledigungsquote</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-value" id="adminAverageGrade">-</div>
                                    <div class="stat-label">Ø Notenwert</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-value" id="adminFailedLogins7d">-</div>
                                    <div class="stat-label">Fehlgeschlagene Logins (7 Tage)</div>
                                </div>
                            </div>
                            <p id="adminGeneratedAt" style="margin-top:1rem;color:var(--color-text-muted);font-size:0.85rem;">Zuletzt aktualisiert: -</p>
                        </div>
                        <div class="widget">
                            <div class="widget-header">
                                <div class="widget-title">Lernen, Inhalte und Trends</div>
                            </div>
                            <div class="input-group" style="flex-direction: column; gap: 1rem;">
                                <div>
                                    <strong>Inhalte</strong>
                                    <p id="adminContentSummary" style="margin-top:0.25rem;color:var(--color-text-secondary);">-</p>
                                </div>
                                <div>
                                    <strong>Top aktive Nutzer (30 Tage)</strong>
                                    <div id="adminTopUsers" style="margin-top:0.25rem;color:var(--color-text-secondary);">-</div>
                                </div>
                                <div>
                                    <strong>Nutzer mit vielen offenen To-Dos</strong>
                                    <div id="adminOpenTodos" style="margin-top:0.25rem;color:var(--color-text-secondary);">-</div>
                                </div>
                                <div>
                                    <strong>Registrierungen / Logins (7 Tage)</strong>
                                    <div id="adminTrends" style="margin-top:0.25rem;color:var(--color-text-secondary);">-</div>
                                </div>
                            </div>
                        </div>
                        <div class="widget">
                            <div class="widget-header">
                                <div class="widget-title">Admin-Nachricht schreiben</div>
                                <button class="btn-primary" onclick="sendAdminMessage()">Senden</button>
                            </div>
                            <div class="input-group" style="flex-direction: column; gap: 0.75rem;">
                                <input type="text" id="adminMessageTitle" placeholder="Titel der Nachricht">
                                <select id="adminMessageRecipient">
                                    <option value="">Alle Nutzer</option>
                                </select>
                                <textarea id="adminMessageBody" rows="6" placeholder="Nachricht eingeben"></textarea>
                                <p id="adminMessageStatus" class="form-status"></p>
                            </div>
                        </div>
                        <div class="widget">
                            <div class="widget-header">
                                <div class="widget-title">Gesendete Admin-Nachrichten</div>
                                <button class="btn-primary" onclick="loadAdminMessageManagement()">Aktualisieren</button>
                            </div>
                            <div class="messages-list" id="adminSentMessages">
                                <p class="message-empty">Noch keine Nachrichten gesendet</p>
                            </div>
                        </div>
                    </div>
                </div>

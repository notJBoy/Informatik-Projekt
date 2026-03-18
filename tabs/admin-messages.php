                <!-- Admin Nachrichten Detail View -->
                <div id="admin-messages" class="view-content" style="display: none;">
                    <div class="content-header">
                        <h1>Admin Nachrichten</h1>
                        <p>Hier erhältst du Mitteilungen von deinen Administratoren.</p>
                    </div>

                    <div class="widget">
                        <div class="widget-header">
                            <div class="widget-title">
                                <span class="widget-icon">💬</span>
                                <span>Nachrichtenübersicht</span>
                            </div>
                            <button class="btn-primary" onclick="loadAdminMessages()">Aktualisieren</button>
                        </div>
                        <div class="widget-body">
                            <div class="messages-list" id="adminMessagesList">
                                <p class="message-empty">Lädt…</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Klassenarbeiten Detail View -->
                <div id="exams" class="view-content" style="display: none;">
                    <div class="content-header">
                        <h1>📝 Klassenarbeiten</h1>
                        <p>Behalte deine kommenden Arbeiten im Blick</p>
                    </div>
                    <div class="widget">
                        <div class="widget-header">
                            <div class="widget-title">Neue Klassenarbeit hinzufügen</div>
                        </div>
                        <div class="input-group">
                            <select id="examSubject" data-subject-dropdown>
                                <option value="">-- Fach wählen --</option>
                            </select>
                            <input type="date" id="examDate">
                            <input type="text" id="examTopic" placeholder="Thema...">
                            <select id="examPeriod">
                                <option value="">Stunde (optional)</option>
                                <option value="1">1. Stunde</option>
                                <option value="2">2. Stunde</option>
                                <option value="3">3. Stunde</option>
                                <option value="4">4. Stunde</option>
                                <option value="5">5. Stunde</option>
                                <option value="6">6. Stunde</option>
                                <option value="7">7. Stunde</option>
                                <option value="8">8. Stunde</option>
                                <option value="9">9. Stunde</option>
                                <option value="10">10. Stunde</option>
                            </select>
                            <button class="btn-primary" onclick="addExam()">Hinzufügen</button>
                        </div>
                        <div class="grades-list" id="examsList" style="margin-top: 1.5rem;"></div>
                    </div>
                </div>

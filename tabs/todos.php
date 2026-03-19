                <!-- To-Dos Detail View -->
                <div id="todos" class="view-content" style="display: none;">
                    <div class="content-header">
                        <h1>✅ To-Do Liste</h1>
                        <p>Verwalte deine Aufgaben und Hausaufgaben</p>
                    </div>

                    <!-- Neue Aufgabe -->
                    <div class="widget" style="margin-bottom: 1.5rem;">
                        <div class="widget-header">
                            <div class="widget-title">Neue Aufgabe</div>
                        </div>
                        <div class="input-group" style="flex-wrap: wrap; gap: 0.5rem;">
                            <input type="text" id="todoTitle" placeholder="Aufgabe eingeben..." style="flex: 2 1 180px;">
                            <select id="todoSubject" data-subject-dropdown style="flex: 1 1 130px;">
                                <option value="">-- Fach (optional) --</option>
                            </select>
                            <input type="date" id="todoDueDate" style="flex: 1 1 140px;">
                            <select id="todoPriority" style="flex: 1 1 120px;">
                                <option value="low">🟢 Niedrig</option>
                                <option value="medium" selected>🟡 Mittel</option>
                                <option value="high">🔴 Hoch</option>
                            </select>
                            <button class="btn-primary" onclick="addTodo()">+ Hinzufügen</button>
                        </div>
                    </div>

                    <!-- Aufgabenliste -->
                    <div class="widget">
                        <div class="widget-header">
                            <div class="widget-title">Aufgaben</div>
                            <div style="display:flex; gap: 0.4rem;">
                                <button class="todo-filter-btn active" onclick="setTodoFilter('all', this)">Alle</button>
                                <button class="todo-filter-btn" onclick="setTodoFilter('open', this)">Offen</button>
                                <button class="todo-filter-btn" onclick="setTodoFilter('done', this)">Erledigt</button>
                            </div>
                        </div>
                        <div class="todo-list" id="todosDetailList" style="margin-top: 1rem; max-height: none; overflow: visible;">
                            <p style="color:var(--color-text-muted);text-align:center;padding:1.5rem;">Lädt…</p>
                        </div>
                    </div>
                </div>

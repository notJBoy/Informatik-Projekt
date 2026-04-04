<!-- Dateizweck: Tab-Template "todos" fuer die Dashboard-Ansicht. -->
<!-- Hinweis: Enthält primär HTML-Struktur und UI-Bausteine fuer diesen Bereich. -->
                <!-- To-Dos Detail View -->
                <div id="todos" class="view-content" style="display: none;">
                    <div class="content-header">
                        <h1>✅ <?php echo htmlspecialchars(t('todos.title')); ?></h1>
                        <p><?php echo htmlspecialchars(t('todos.subtitle')); ?></p>
                    </div>

                    <!-- Neue Aufgabe -->
                    <div class="widget" style="margin-bottom: 1.5rem;">
                        <div class="widget-header">
                            <div class="widget-title"><?php echo htmlspecialchars(t('todos.new_task')); ?></div>
                        </div>
                        <div class="input-group" style="flex-wrap: wrap; gap: 0.5rem;">
                            <input type="text" id="todoTitle" placeholder="<?php echo htmlspecialchars(t('todos.task_placeholder')); ?>" style="flex: 2 1 180px;">
                            <select id="todoSubject" data-subject-dropdown style="flex: 1 1 130px;">
                                <option value=""><?php echo htmlspecialchars(t('todos.subject_optional')); ?></option>
                            </select>
                            <input type="date" id="todoDueDate" style="flex: 1 1 140px;">
                            <select id="todoPriority" style="flex: 1 1 120px;">
                                <option value="low"><?php echo htmlspecialchars(t('todos.priority_low')); ?></option>
                                <option value="medium" selected><?php echo htmlspecialchars(t('todos.priority_medium')); ?></option>
                                <option value="high"><?php echo htmlspecialchars(t('todos.priority_high')); ?></option>
                            </select>
                            <button class="btn-primary" onclick="addTodo()">+ <?php echo htmlspecialchars(t('common.add')); ?></button>
                        </div>
                    </div>

                    <!-- Aufgabenliste -->
                    <div class="widget">
                        <div class="widget-header">
                            <div class="widget-title"><?php echo htmlspecialchars(t('todos.tasks')); ?></div>
                            <div style="display:flex; gap: 0.4rem;">
                                <button class="todo-filter-btn active" onclick="setTodoFilter('all', this)"><?php echo htmlspecialchars(t('todos.filter_all')); ?></button>
                                <button class="todo-filter-btn" onclick="setTodoFilter('open', this)"><?php echo htmlspecialchars(t('todos.filter_open')); ?></button>
                                <button class="todo-filter-btn" onclick="setTodoFilter('done', this)"><?php echo htmlspecialchars(t('todos.filter_done')); ?></button>
                            </div>
                        </div>
                        <div class="todo-list" id="todosDetailList" style="margin-top: 1rem; max-height: none; overflow: visible;">
                            <div class="loading-spinner"><div class="spinner"></div><span><?php echo htmlspecialchars(t('common.loading')); ?></span></div>
                        </div>
                    </div>
                </div>

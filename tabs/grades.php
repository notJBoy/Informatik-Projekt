<!-- Dateizweck: Tab-Template "grades" fuer die Dashboard-Ansicht. -->
<!-- Hinweis: Enthält primär HTML-Struktur und UI-Bausteine fuer diesen Bereich. -->
                <!-- Noten Detail View -->
                <div id="grades" class="view-content" style="display: none;">
                    <div class="content-header">
                        <h1>📝 <?php echo htmlspecialchars(t('grades.title')); ?></h1>
                        <p><?php echo htmlspecialchars(t('grades.subtitle')); ?></p>
                    </div>
                    <div class="widget">
                        <div class="widget-header">
                            <div class="widget-title"><?php echo htmlspecialchars(t('grades.add_title')); ?></div>
                            <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                                <button class="btn-secondary" onclick="exportGradesCSV()">⬇️ CSV</button>
                                <button class="btn-secondary" onclick="exportGradesPDF()">🧾 PDF</button>
                            </div>
                        </div>
                        <div class="input-group">
                            <select id="gradeSubject" data-subject-dropdown placeholder="Fach wählen...">
                                <option value=""><?php echo htmlspecialchars(t('grades.subject_choose')); ?></option>
                            </select>
                            <input type="number" id="gradeValue" placeholder="<?php echo htmlspecialchars(t('grades.value_placeholder')); ?>" min="0" max="15" step="1">
                            <input type="number" id="gradeWeight" placeholder="<?php echo htmlspecialchars(t('grades.weight_placeholder')); ?>" min="1" step="1" value="1">
                            <button class="btn-primary" onclick="addGrade()"><?php echo htmlspecialchars(t('common.add')); ?></button>
                        </div>
                        <div class="grades-list" id="gradesList" style="margin-top: 1.5rem;">
                            <div class="loading-spinner"><div class="spinner"></div><span><?php echo htmlspecialchars(t('common.loading_long')); ?></span></div>
                        </div>
                    </div>
                </div>

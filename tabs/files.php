<?php
/**
 * Dateizweck: Endpoint oder Seite "files" im Modul "tabs".
 * Hinweis: Diese Datei ist Teil der LearnHub-Backend/Frontend-Anbindung.
 */
// Dateien vom Backend laden (benötigt $user_id aus der Session, gesetzt in current_dashboard.php)
$backend_url = BACKEND_BASE_URL . "/files/$user_id";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $backend_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$files = json_decode($response, true);
if (!$files) {
    $files = [];
}
?>
                <!-- Dateien Detail View -->
                <div id="files" class="view-content" style="display: none;">
                    <div class="content-header">
                        <h1>📁 <?php echo htmlspecialchars(t('files.title')); ?></h1>
                        <p><?php echo htmlspecialchars(t('files.subtitle')); ?></p>
                    </div>
                    <div class="widget">
                        <div class="widget-header">
                            <div class="widget-title"><?php echo htmlspecialchars(t('files.upload_title')); ?></div>
                        </div>
                        <form action="files/upload.php" method="POST" enctype="multipart/form-data">
                        <div class="input-group">
                            <input type="text" name="subject" placeholder="<?php echo htmlspecialchars(t('files.subject_placeholder')); ?>" required>
                                <input type="file" name="file" required>
                            <button class="btn-primary" type="submit"><?php echo htmlspecialchars(t('common.upload')); ?></button>
                                </div>
                            </form>
                        </div>
                        <div class="files-list" style="margin-top: 1.5rem;">
<?php foreach ($files as $file): ?>
    <div class="file-item">
        <span class="file-icon">📄</span>
        <div class="file-info">
            <div class="file-name">
                <?php echo htmlspecialchars($file['original_name']); ?>
            </div>
            <div class="file-meta">
                <?php echo htmlspecialchars($file['subject']); ?>
            </div>
        </div>

        <div style="display:flex; gap:10px;">
            <a class="btn-icon"
               href="files/download.php?file_id=<?php echo $file['id']; ?>">
               ⬇️
            </a>

            <a class="btn-icon"
               href="files/delete.php?file_id=<?php echo $file['id']; ?>">
               🗑️
            </a>
        </div>
    </div>
<?php endforeach; ?>
</div>

                    </div>
                </div>

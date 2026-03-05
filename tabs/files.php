<?php
// Dateien vom Backend laden (benötigt $user_id aus der Session, gesetzt in current_dashboard.php)
$backend_url = "http://127.0.0.1:8000/files/$user_id";

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
                        <h1>📁 Dateien</h1>
                        <p>Alle deine Lernmaterialien</p>
                    </div>
                    <div class="widget">
                        <div class="widget-header">
                            <div class="widget-title">Datei hochladen</div>
                        </div>
                        <form action="files/upload.php" method="POST" enctype="multipart/form-data">
                        <div class="input-group">
                            <input type="text" name="subject" placeholder="Fach eingeben..." required>
                                <input type="file" name="file" required>
                            <button class="btn-primary" type="submit">Hochladen</button>
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

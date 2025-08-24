<?php
ob_start();
require '../../../config/autoload.php';
require '../../../config/database.php';
require '../../../config/config.php';
require '../auth_check.php';

$db = new Database();
$conn = $db->connect();
$dataManager = new DataManager($conn);

$message = '';
$error = '';
$warnings = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            $format = $_POST['format'] ?? 'json';

            if ($_POST['action'] === 'export') {
                $types = $_POST['types'] ?? [];

                if (empty($types)) {
                    throw new Exception('Please select at least one content type to export.');
                }

                $exportData = $dataManager->export($format, $types);

                $timestamp = date('Y-m-d_H-i-s');
                $filename = "export_{$timestamp}.{$format}";

                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename=' . $filename);
                header('Pragma: no-cache');

                echo $exportData;
                exit;
            } elseif ($_POST['action'] === 'import') {
                if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('Please select a valid file to import.');
                }

                $result = $dataManager->import($_FILES['import_file']['tmp_name'], $format);
                $message = 'Data imported successfully.';

                if (!empty($result['warnings'])) {
                    $warnings = $result['warnings'];
                }
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

include '../../../templates/header-admin.php';
?>

<div class="alpi-admin-content">
    <h1 class="alpi-text-primary alpi-mb-lg">Data Management</h1>

    <?php if ($message): ?>
        <div class="alpi-alert alpi-alert-success alpi-mb-md"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alpi-alert alpi-alert-danger alpi-mb-md"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($warnings)): ?>
        <div class="alpi-alert alpi-alert-warning alpi-mb-md">
            <strong>Warnings:</strong>
            <ul>
                <?php foreach ($warnings as $warning): ?>
                    <li><?= htmlspecialchars($warning) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="alpi-tabs">
        <button class="alpi-tab active" onclick="switchTab(event, 'export')">Export Data</button>
        <button class="alpi-tab" onclick="switchTab(event, 'import')">Import Data</button>
    </div>

    <div class="alpi-tab-content" id="export-tab" style="display: block;">
        <div class="alpi-card alpi-p-lg">
            <h2 class="alpi-text-secondary alpi-mb-md">Export Data</h2>
            <form method="post" class="alpi-form">
                <input type="hidden" name="action" value="export">

                <div class="alpi-form-group">
                    <label class="alpi-form-label">Select Content Types to Export:</label>
                    <div class="alpi-checkbox-group">
                        <label class="alpi-form-checkbox">
                            <input type="checkbox" name="types[]" value="posts" checked>
                            Posts (including blocks)
                        </label>
                        <label class="alpi-form-checkbox">
                            <input type="checkbox" name="types[]" value="pages" checked>
                            Pages (including blocks)
                        </label>
                        <label class="alpi-form-checkbox">
                            <input type="checkbox" name="types[]" value="categories" checked>
                            Categories
                        </label>
                        <label class="alpi-form-checkbox">
                            <input type="checkbox" name="types[]" value="settings" checked>
                            Settings
                        </label>
                    </div>
                </div>

                <div class="alpi-form-group">
                    <label class="alpi-form-label">Export Format:</label>
                    <select name="format" class="alpi-form-input">
                        <option value="json">JSON</option>
                        <option value="xml">XML</option>
                    </select>
                </div>

                <div class="alpi-form-help alpi-mb-md">
                    <h3>About Export</h3>
                    <p>The export tool will:</p>
                    <ul>
                        <li>Create a backup of your selected content</li>
                        <li>Include all related data (e.g., blocks for posts and pages)</li>
                        <li>Generate a file that can be used for restoration or migration</li>
                        <li>Preserve all relationships between content items</li>
                    </ul>
                </div>

                <div class="alpi-text-right">
                    <button type="submit" class="alpi-btn alpi-btn-primary">Export Data</button>
                </div>
            </form>
        </div>
    </div>

    <div class="alpi-tab-content" id="import-tab" style="display: none;">
        <div class="alpi-card alpi-p-lg">
            <h2 class="alpi-text-secondary alpi-mb-md">Import Data</h2>
            <form method="post" class="alpi-form" enctype="multipart/form-data">
                <input type="hidden" name="action" value="import">

                <div class="alpi-form-group">
                    <label class="alpi-form-label">Select Import File:</label>
                    <input type="file" name="import_file" class="alpi-form-input" required>
                </div>

                <div class="alpi-form-group">
                    <label class="alpi-form-label">File Format:</label>
                    <select name="format" class="alpi-form-input">
                        <option value="json">JSON</option>
                        <option value="xml">XML</option>
                    </select>
                </div>

                <div class="alpi-form-help alpi-mb-md">
                    <h3>About Import</h3>
                    <p>The import process will:</p>
                    <ul>
                        <li>Validate the import file format and structure</li>
                        <li>Maintain data integrity with transaction support</li>
                        <li>Map fields dynamically to match your database structure</li>
                        <li>Generate warnings for any non-critical issues</li>
                    </ul>
                    <p class="alpi-text-warning">Note: It's recommended to backup your database before performing an import.</p>
                </div>

                <div class="alpi-text-right">
                    <button type="submit" class="alpi-btn alpi-btn-primary">Import Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .alpi-tabs {
        display: flex;
        border-bottom: 1px solid var(--alpi-border);
        margin-bottom: var(--alpi-spacing-md);
        gap: 1px;
    }

    .alpi-tab {
        padding: var(--alpi-spacing-sm) var(--alpi-spacing-md);
        background: none;
        border: none;
        border-bottom: 2px solid transparent;
        cursor: pointer;
        font-size: 16px;
        color: var(--alpi-text);
        transition: all 0.3s ease;
    }

    .alpi-tab:hover {
        background-color: var(--alpi-light);
    }

    .alpi-tab.active {
        border-bottom-color: var(--alpi-primary);
        color: var(--alpi-primary);
    }

    .alpi-checkbox-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 10px;
    }

    .alpi-form-help {
        color: #666;
        font-size: 14px;
        background: #f8f9fa;
        padding: 15px;
        border-radius: 4px;
    }

    .alpi-form-help h3 {
        margin-bottom: 10px;
        color: #333;
    }

    .alpi-form-help ul {
        margin: 10px 0 10px 20px;
    }

    .alpi-form-help li {
        margin-bottom: 5px;
    }

    .alpi-text-warning {
        color: #856404;
        background-color: #fff3cd;
        padding: 10px;
        border-radius: 4px;
        margin-top: 15px;
    }
</style>

<script>
    function switchTab(event, tabName) {
        document.querySelectorAll('.alpi-tab-content').forEach(content => {
            content.style.display = 'none';
        });

        document.querySelectorAll('.alpi-tab').forEach(tab => {
            tab.classList.remove('active');
        });

        const targetContent = document.getElementById(tabName + '-tab');
        if (targetContent) targetContent.style.display = 'block';

        if (event && event.target) {
            event.target.classList.add('active');
        } else {
            const btn = Array.from(document.querySelectorAll('.alpi-tab')).find(b => b.textContent.trim().toLowerCase().startsWith(tabName));
            if (btn) btn.classList.add('active');
        }
    }
</script>

<?php
include '../../../templates/footer-admin.php';
ob_end_flush();
?>
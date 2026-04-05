<?php
ob_start();
require '../../../config/autoload.php';
require '../../../config/database.php';
require '../../../config/config.php';
require '../auth_check.php';

$db = new Database();
$conn = $db->connect();
$dataManager = new DataManager($conn);

$activeTab = (isset($_GET['tab']) && $_GET['tab'] === 'import') ? 'import' : 'export';
$flashMessage = null;
$warnings = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'export';
    $redirectTab = $action === 'import' ? 'import' : 'export';
    $redirectUrl = BASE_URL . '/public/admin/settings/data-management.php?tab=' . urlencode($redirectTab);

    if (!alpiVerifyCsrfToken($_POST['csrf_token'] ?? '')) {
        alpiRegenerateCsrfToken();
        alpiSetFlashValue('data_management_message', [
            'type' => 'danger',
            'message' => 'Invalid CSRF token. Please refresh and try again.',
        ]);
        header('Location: ' . $redirectUrl);
        exit;
    } else {
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
                    alpiRegenerateCsrfToken();
                    alpiSetFlashValue('data_management_message', [
                        'type' => 'success',
                        'message' => 'Data imported successfully.',
                    ]);

                    if (!empty($result['warnings'])) {
                        alpiSetFlashValue('data_management_warnings', $result['warnings']);
                    }

                    header('Location: ' . $redirectUrl);
                    exit;
                }
            }
        } catch (Exception $e) {
            alpiSetFlashValue('data_management_message', [
                'type' => 'danger',
                'message' => $e->getMessage(),
            ]);
            header('Location: ' . $redirectUrl);
            exit;
        }
    }
}

$flashMessage = alpiConsumeFlashValue('data_management_message');
$warnings = alpiConsumeFlashValue('data_management_warnings', []);

include '../../../templates/header-admin.php';
?>

<div class="alpi-admin-content">
    <h1 class="alpi-text-primary alpi-mb-lg">Data Management</h1>

    <?php if ($flashMessage): ?>
        <div class="alpi-alert <?= $flashMessage['type'] === 'success' ? 'alpi-alert-success' : 'alpi-alert-danger' ?> alpi-mb-md"><?= htmlspecialchars($flashMessage['message'], ENT_QUOTES, 'UTF-8') ?></div>
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
        <button type="button" class="alpi-tab <?= $activeTab === 'export' ? 'active' : '' ?>" onclick="switchTab(event, 'export')">Export Data</button>
        <button type="button" class="alpi-tab <?= $activeTab === 'import' ? 'active' : '' ?>" onclick="switchTab(event, 'import')">Import Data</button>
    </div>

    <div class="alpi-tab-content" id="export-tab" style="display: <?= $activeTab === 'export' ? 'block' : 'none' ?>;">
        <div class="alpi-card alpi-p-lg">
            <h2 class="alpi-text-secondary alpi-mb-md">Export Data</h2>
            <form method="post" class="alpi-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(alpiGetCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
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

                <div class="alpi-info-panel alpi-mb-md">
                    <h3 class="alpi-info-panel-title">About Export</h3>
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

    <div class="alpi-tab-content" id="import-tab" style="display: <?= $activeTab === 'import' ? 'block' : 'none' ?>;">
        <div class="alpi-card alpi-p-lg">
            <h2 class="alpi-text-secondary alpi-mb-md">Import Data</h2>
            <form method="post" class="alpi-form" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(alpiGetCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
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

                <div class="alpi-info-panel alpi-mb-md">
                    <h3 class="alpi-info-panel-title">About Import</h3>
                    <p>The import process will:</p>
                    <ul>
                        <li>Validate the import file format and structure</li>
                        <li>Maintain data integrity with transaction support</li>
                        <li>Map fields dynamically to match your database structure</li>
                        <li>Generate warnings for any non-critical issues</li>
                    </ul>
                    <p class="alpi-info-panel-note">Note: It is recommended to back up your database before performing an import.</p>
                </div>

                <div class="alpi-text-right">
                    <button type="submit" class="alpi-btn alpi-btn-primary">Import Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function switchTab(event, tabName) {
        const exportTab = document.getElementById('export-tab');
        const importTab = document.getElementById('import-tab');
        const tabs = document.querySelectorAll('.alpi-tab');

        exportTab.style.display = tabName === 'export' ? 'block' : 'none';
        importTab.style.display = tabName === 'import' ? 'block' : 'none';

        tabs.forEach((tab) => tab.classList.remove('active'));

        if (event && event.currentTarget) {
            event.currentTarget.classList.add('active');
        }
    }
</script>

<?php
include '../../../templates/footer-admin.php';
ob_end_flush();
?>
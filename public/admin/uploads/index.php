<?php
require '../../../config/autoload.php';
require '../../../config/database.php';
require '../../../config/config.php';
require '../auth_check.php';

$db = new Database();
$conn = $db->connect();
$upload = new Upload($conn);

$errorMessage = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete'])) {
        $fileName = basename($_POST['delete']);
        try {
            $upload->deleteFile($fileName);
            $successMessage = 'File deleted successfully.';
        } catch (Exception $e) {
            $errorMessage = 'Error: ' . htmlspecialchars($e->getMessage());
        }
    }

    if (isset($_FILES['fileToUpload'])) {
        try {
            $upload->uploadFile($_FILES['fileToUpload']);
            $successMessage = 'File uploaded successfully.';

            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch (Exception $e) {
            $errorMessage = 'Error: ' . htmlspecialchars($e->getMessage());
        }
    }
}

$uploads = $upload->listFiles();

include '../../../templates/header-admin.php';
?>

<div class="alpi-admin-content">
    <h1 class="alpi-text-primary alpi-mb-lg">Uploads Management</h1>

    <?php if ($errorMessage) : ?>
        <div class="alpi-alert alpi-alert-danger alpi-mb-md"><?= $errorMessage ?></div>
    <?php endif; ?>

    <?php if ($successMessage) : ?>
        <div class="alpi-alert alpi-alert-success alpi-mb-md"><?= $successMessage ?></div>
    <?php endif; ?>

    <div class="alpi-card alpi-p-lg alpi-mb-lg">
        <h2 class="alpi-text-secondary alpi-mb-md">Upload New File</h2>
        <form action="" method="post" enctype="multipart/form-data" class="alpi-form">
            <div class="alpi-form-group">
                <label for="fileToUpload" class="alpi-form-label">Select file to upload:</label>
                <input type="file" name="fileToUpload" id="fileToUpload" class="alpi-form-input alpi-file-input" required>
            </div>
            <button type="submit" name="submit" class="alpi-btn alpi-btn-primary">Upload File</button>
        </form>
    </div>

    <div class="alpi-uploads-grid">
        <?php foreach ($uploads as $fileInfo) : ?>
            <div class="alpi-uploads-item alpi-card">
                <div class="alpi-uploads-preview">
                    <?php if ($fileInfo['isImage']) : ?>
                        <img src="<?= htmlspecialchars($fileInfo['url'], ENT_QUOTES, 'UTF-8') ?>" alt="Image preview" class="alpi-uploads-thumbnail">
                    <?php else : ?>
                        <div class="alpi-uploads-file-icon">
                            <span class="alpi-uploads-file-ext"><?= strtoupper(pathinfo($fileInfo['path'], PATHINFO_EXTENSION)) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="alpi-uploads-details">
                    <h4 class="alpi-uploads-filename">
                        <a href="<?= htmlspecialchars($fileInfo['url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="alpi-link">
                            <?= htmlspecialchars(basename($fileInfo['path']), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </h4>
                    <p class="alpi-uploads-filetype"><?= htmlspecialchars(mime_content_type($fileInfo['path']), ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="alpi-uploads-filesize"><?= round(filesize($fileInfo['path']) / (1024 * 1024), 2) ?> MB</p>
                    <p class="alpi-uploads-filedate"><?= htmlspecialchars(date("F d, Y", filemtime($fileInfo['path'])), ENT_QUOTES, 'UTF-8') ?></p>
                    <form method="post" class="alpi-uploads-delete-form">
                        <input type="hidden" name="delete" value="<?= htmlspecialchars(basename($fileInfo['path']), ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="alpi-btn alpi-btn-danger alpi-btn-sm">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include '../../../templates/footer-admin.php'; ?>

<style>
    .alpi-uploads-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
    }

    .alpi-uploads-item {
        overflow: hidden;
        transition: transform 0.3s ease;
    }

    .alpi-uploads-item:hover {
        transform: translateY(-5px);
    }

    .alpi-uploads-preview {
        height: 150px;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: var(--alpi-background);
    }

    .alpi-uploads-thumbnail {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .alpi-uploads-file-icon {
        font-size: 48px;
        color: var(--alpi-text);
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        height: 100%;
    }

    .alpi-uploads-file-ext {
        font-size: 24px;
        font-weight: bold;
    }

    .alpi-uploads-details {
        padding: 15px;
    }

    .alpi-uploads-filename {
        margin-bottom: 10px;
    }

    .alpi-uploads-filetype,
    .alpi-uploads-filesize,
    .alpi-uploads-filedate {
        margin-bottom: 5px;
        color: var(--alpi-text);
        font-size: 0.9em;
    }

    .alpi-uploads-delete-form {
        text-align: right;
        margin-top: 10px;
    }

    .alpi-file-input {
        border: 1px solid var(--alpi-border);
        padding: 10px;
        border-radius: var(--alpi-radius-sm);
    }
</style>
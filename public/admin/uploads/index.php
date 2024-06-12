<?php

require '../../../config/database.php';
require '../../../config/config.php';
require '../../../config/autoload.php';
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

?>

<?php include '../../../templates/header-admin.php'; ?>

<div class="admin-uploads-container">
    <h1 class="admin-uploads-title">Uploads</h1>

    <?php if ($errorMessage) : ?>
        <div class="admin-uploads-alert admin-uploads-alert-danger"><?= $errorMessage ?></div>
    <?php endif; ?>

    <?php if ($successMessage) : ?>
        <div class="admin-uploads-alert admin-uploads-alert-success"><?= $successMessage ?></div>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data" class="admin-uploads-form">
        <div class="admin-uploads-form-group">
            <label for="fileToUpload" class="admin-uploads-label">Select file to upload:</label>
            <input type="file" name="fileToUpload" id="fileToUpload" class="admin-uploads-file-input" required>
        </div>
        <button type="submit" name="submit" class="admin-uploads-btn admin-uploads-btn-primary">Upload File</button>
    </form>

    <div class="admin-uploads-grid">
        <?php foreach ($uploads as $fileInfo) : ?>
            <div class="admin-uploads-item">
                <div class="admin-uploads-preview">
                    <?php if ($fileInfo['isImage']) : ?>
                        <img src="<?= htmlspecialchars($fileInfo['url'], ENT_QUOTES, 'UTF-8') ?>" alt="Image preview" class="admin-uploads-thumbnail">
                    <?php else : ?>
                        <div class="admin-uploads-file-icon">
                            <span class="admin-uploads-file-ext"><?= strtoupper(pathinfo($fileInfo['path'], PATHINFO_EXTENSION)) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="admin-uploads-details">
                    <h4 class="admin-uploads-filename"><a href="<?= htmlspecialchars($fileInfo['url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank"><?= htmlspecialchars(basename($fileInfo['path']), ENT_QUOTES, 'UTF-8') ?></a></h4>
                    <p class="admin-uploads-filetype"><?= htmlspecialchars(mime_content_type($fileInfo['path']), ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="admin-uploads-filesize"><?= round(filesize($fileInfo['path']) / (1024 * 1024), 2) ?> MB</p>
                    <p class="admin-uploads-filedate"><?= htmlspecialchars(date("F d, Y", filemtime($fileInfo['path'])), ENT_QUOTES, 'UTF-8') ?></p>
                    <form method="post" class="admin-uploads-delete-form">
                        <input type="hidden" name="delete" value="<?= htmlspecialchars(basename($fileInfo['path']), ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="admin-uploads-delete-btn">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include '../../../templates/footer-admin.php'; ?>

<style>
    .admin-uploads-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .admin-uploads-title {
        font-size: 24px;
        margin-bottom: 20px;
    }

    .admin-uploads-alert {
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 4px;
    }

    .admin-uploads-alert-danger {
        background-color: #f8d7da;
        color: #721c24;
    }

    .admin-uploads-alert-success {
        background-color: #d4edda;
        color: #155724;
    }

    .admin-uploads-form {
        margin-bottom: 20px;
    }

    .admin-uploads-form-group {
        margin-bottom: 10px;
    }

    .admin-uploads-label {
        display: block;
        margin-bottom: 5px;
    }

    .admin-uploads-file-input {
        display: block;
        width: 100%;
        padding: 10px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .admin-uploads-btn {
        display: inline-block;
        padding: 10px 20px;
        font-size: 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .admin-uploads-btn-primary {
        background-color: #007bff;
        color: #fff;
    }

    .admin-uploads-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        grid-gap: 20px;
    }

    .admin-uploads-item {
        background-color: #fff;
        border-radius: 6px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: transform 0.3s ease;
    }

    .admin-uploads-item:hover {
        transform: translateY(-5px);
    }

    .admin-uploads-preview {
        height: 150px;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: #f8f9fa;
    }

    .admin-uploads-thumbnail {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .admin-uploads-file-icon {
        font-size: 48px;
        color: #6c757d;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        height: 100%;
    }

    .admin-uploads-file-ext {
        font-size: 24px;
        font-weight: bold;
    }

    .admin-uploads-details {
        padding: 15px;
        background-color: #f8f9fa;
    }

    .admin-uploads-filename {
        margin-bottom: 10px;
    }

    .admin-uploads-filetype,
    .admin-uploads-filesize,
    .admin-uploads-filedate {
        margin-bottom: 5px;
        color: #6c757d;
    }

    .admin-uploads-delete-form {
        text-align: right;
        margin-top: 10px;
    }

    .admin-uploads-delete-btn {
        background-color: #dc3545;
        color: #fff;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .admin-uploads-delete-btn:hover {
        background-color: #c82333;
    }
</style>
<?php
require '../../../config/autoload.php';
require '../../../config/database.php';
require '../../../config/config.php';
require '../auth_check.php';

$db = new Database();
$conn = $db->connect();
$upload = new Upload($conn);
$redirectUrl = BASE_URL . '/public/admin/uploads/index.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!alpiVerifyCsrfToken($_POST['csrf_token'] ?? '')) {
        alpiRegenerateCsrfToken();
        alpiSetFlashValue('uploads_message', [
            'type' => 'danger',
            'message' => 'Invalid CSRF token. Please refresh and try again.',
        ]);
        header('Location: ' . $redirectUrl);
        exit;
    } else {
        if (isset($_POST['delete'])) {
            $fileName = basename($_POST['delete']);
            try {
                $upload->deleteFile($fileName);
                alpiRegenerateCsrfToken();
                alpiSetFlashValue('uploads_message', [
                    'type' => 'success',
                    'message' => 'File deleted successfully.',
                ]);
            } catch (Exception $e) {
                $details = [];
                if ($e instanceof UploadInUseException) {
                    $details = $e->getUsages();
                }

                alpiSetFlashValue('uploads_message', [
                    'type' => 'danger',
                    'message' => 'Error: ' . $e->getMessage(),
                    'details' => $details,
                ]);
            }

            header('Location: ' . $redirectUrl);
            exit;
        }

        if (isset($_FILES['fileToUpload'])) {
            try {
                $upload->uploadFile($_FILES['fileToUpload']);
                alpiRegenerateCsrfToken();
                alpiSetFlashValue('uploads_message', [
                    'type' => 'success',
                    'message' => 'File uploaded successfully.',
                ]);
            } catch (Exception $e) {
                alpiSetFlashValue('uploads_message', [
                    'type' => 'danger',
                    'message' => 'Error: ' . $e->getMessage(),
                ]);
            }

            header('Location: ' . $redirectUrl);
            exit;
        }
    }
}

$flashMessage = alpiConsumeFlashValue('uploads_message');

$uploads = $upload->listFiles();


if (!function_exists('shorten_filename')) {
    function shorten_filename(string $filename, int $maxLength = 20): string
    {
        if (strlen($filename) > $maxLength) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $basename = pathinfo($filename, PATHINFO_FILENAME);
            $basename = substr($basename, 0, $maxLength - strlen($extension) - 3);
            return $basename . '...' . $extension;
        }
        return $filename;
    }
}

include '../../../templates/header-admin.php';
?>

<div class="alpi-admin-content">
    <h1 class="alpi-text-primary alpi-mb-lg">Uploads Management</h1>

    <?php if ($flashMessage) : ?>
        <div class="alpi-alert <?= $flashMessage['type'] === 'success' ? 'alpi-alert-success' : 'alpi-alert-danger' ?> alpi-mb-md">
            <div><?= htmlspecialchars($flashMessage['message'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php if (!empty($flashMessage['details']) && is_array($flashMessage['details'])) : ?>
                <ul class="alpi-mt-sm">
                    <?php foreach ($flashMessage['details'] as $detail) : ?>
                        <li><?= htmlspecialchars($detail, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="alpi-card alpi-p-lg alpi-mb-lg">
        <h2 class="alpi-text-secondary alpi-mb-md">Upload New File</h2>
        <form action="" method="post" enctype="multipart/form-data" class="alpi-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(alpiGetCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
            <div class="alpi-form-group">
                <label for="fileToUpload" class="alpi-form-label">Select file to upload:</label>
                <input type="file" name="fileToUpload" id="fileToUpload" class="alpi-form-input alpi-file-input" accept="<?= htmlspecialchars($upload->getAcceptAttribute(), ENT_QUOTES, 'UTF-8') ?>" required>
                <p class="alpi-form-help">Supported files include images, upload-based video formats, upload-based audio formats, and favicon icons.</p>
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
                        <a href="<?= htmlspecialchars($fileInfo['url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="alpi-link" title="<?= htmlspecialchars(basename($fileInfo['path']), ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars(shorten_filename(basename($fileInfo['path'])), ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </h4>
                    <p class="alpi-uploads-filetype"><?= htmlspecialchars($fileInfo['type'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="alpi-uploads-filesize"><?= round(filesize($fileInfo['path']) / (1024 * 1024), 2) ?> MB</p>
                    <p class="alpi-uploads-filedate"><?= htmlspecialchars(date("F d, Y", filemtime($fileInfo['path'])), ENT_QUOTES, 'UTF-8') ?></p>
                    <form method="post" class="alpi-uploads-delete-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(alpiGetCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="delete" value="<?= htmlspecialchars(basename($fileInfo['path']), ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="alpi-btn alpi-btn-danger alpi-btn-sm">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include '../../../templates/footer-admin.php'; ?>
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
$uploadCount = count($uploads);


if (!function_exists('alpiShortenUploadFilename')) {
    function alpiShortenUploadFilename(string $filename, int $maxLength = 20): string
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

if (!function_exists('alpiFormatUploadSize')) {
    function alpiFormatUploadSize(int $sizeBytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = max(0, $sizeBytes);
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        $precision = $unitIndex === 0 ? 0 : ($size < 10 ? 1 : 0);

        return number_format($size, $precision) . ' ' . $units[$unitIndex];
    }
}

if (!function_exists('alpiGetUploadBadgeConfig')) {
    function alpiGetUploadBadgeConfig(array $fileInfo): array
    {
        if (!empty($fileInfo['isImage'])) {
            return ['label' => 'Image', 'class' => 'alpi-badge-info'];
        }

        if (!empty($fileInfo['isAudio'])) {
            return ['label' => 'Audio', 'class' => 'alpi-badge-success'];
        }

        if (!empty($fileInfo['isVideo'])) {
            return ['label' => 'Video', 'class' => 'alpi-badge-secondary'];
        }

        if (!empty($fileInfo['isIcon'])) {
            return ['label' => 'Icon', 'class' => 'alpi-badge-warning'];
        }

        return ['label' => 'File', 'class' => 'alpi-badge-secondary'];
    }
}

include '../../../templates/header-admin.php';
?>

<div class="alpi-admin-content">
    <div class="alpi-uploads-toolbar alpi-mb-lg">
        <div>
            <h1 class="alpi-text-primary">Uploads Management</h1>
            <p class="alpi-uploads-summary">
                <?= htmlspecialchars($uploadCount === 1 ? '1 file in the library. Newest uploads appear first.' : $uploadCount . ' files in the library. Newest uploads appear first.', ENT_QUOTES, 'UTF-8') ?>
            </p>
        </div>
        <span class="alpi-badge alpi-badge-info"><?= htmlspecialchars((string) $uploadCount, ENT_QUOTES, 'UTF-8') ?> files</span>
    </div>

    <?php if ($flashMessage) : ?>
        <div class="alpi-alert <?= $flashMessage['type'] === 'success' ? 'alpi-alert-success' : 'alpi-alert-danger' ?> alpi-mb-md">
            <div><?= htmlspecialchars($flashMessage['message'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php if (!empty($flashMessage['details']) && is_array($flashMessage['details'])) : ?>
                <div class="alpi-uploads-alert-details">
                    <p class="alpi-uploads-alert-details-title">This file is still used in:</p>
                    <ul class="alpi-list-clean alpi-alert-list">
                        <?php foreach ($flashMessage['details'] as $detail) : ?>
                            <li><?= htmlspecialchars($detail, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="alpi-card alpi-p-lg alpi-uploads-library-card">
        <div class="alpi-card-header">
            <h2 class="alpi-text-secondary">Upload New File</h2>
            <p class="alpi-uploads-library-note">Supported files include images, upload-based video formats, upload-based audio formats, and favicon icons.</p>
        </div>
        <form action="" method="post" enctype="multipart/form-data" class="alpi-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(alpiGetCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
            <div class="alpi-form-group">
                <label for="fileToUpload" class="alpi-form-label">Select file to upload</label>
                <input type="file" name="fileToUpload" id="fileToUpload" class="alpi-form-input alpi-file-input" accept="<?= htmlspecialchars($upload->getAcceptAttribute(), ENT_QUOTES, 'UTF-8') ?>" required>
                <p class="alpi-form-help">Choose one file and the page will refresh with a confirmation message after upload.</p>
            </div>
            <button type="submit" name="submit" class="alpi-btn alpi-btn-primary" data-loading-label="Uploading...">Upload File</button>
        </form>
    </div>

    <?php if (empty($uploads)) : ?>
        <div class="alpi-card alpi-uploads-empty">
            <span class="alpi-badge alpi-badge-secondary">Library empty</span>
            <h2 class="alpi-uploads-empty-title">No uploads yet</h2>
            <p class="alpi-uploads-empty-copy">Start by uploading an image, video, audio file, or icon above. Your newest files will appear here first.</p>
        </div>
    <?php else : ?>
        <div class="alpi-uploads-grid">
            <?php foreach ($uploads as $fileInfo) : ?>
                <?php
                $fileName = basename($fileInfo['path']);
                $badgeConfig = alpiGetUploadBadgeConfig($fileInfo);
                ?>
                <div class="alpi-uploads-item alpi-card">
                    <div class="alpi-uploads-preview">
                        <?php if ($fileInfo['isImage']) : ?>
                            <img src="<?= htmlspecialchars($fileInfo['url'], ENT_QUOTES, 'UTF-8') ?>" alt="Preview of <?= htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') ?>" class="alpi-uploads-thumbnail">
                        <?php else : ?>
                            <div class="alpi-uploads-file-icon">
                                <span class="alpi-uploads-file-ext"><?= htmlspecialchars(strtoupper($fileInfo['extension'] ?? pathinfo($fileName, PATHINFO_EXTENSION)), ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="alpi-uploads-details">
                        <h4 class="alpi-uploads-filename">
                            <a href="<?= htmlspecialchars($fileInfo['url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="alpi-link" title="<?= htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars(alpiShortenUploadFilename($fileName), ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </h4>
                        <div class="alpi-uploads-meta">
                            <span class="alpi-badge <?= htmlspecialchars($badgeConfig['class'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($badgeConfig['label'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="alpi-uploads-meta-item"><?= htmlspecialchars(strtoupper((string) ($fileInfo['extension'] ?? '')), ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="alpi-uploads-meta-item"><?= htmlspecialchars(alpiFormatUploadSize((int) ($fileInfo['sizeBytes'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <p class="alpi-uploads-filetype"><?= htmlspecialchars($fileInfo['type'], ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="alpi-uploads-filedate">Updated <?= htmlspecialchars(date('F d, Y', (int) ($fileInfo['modifiedAt'] ?? 0)), ENT_QUOTES, 'UTF-8') ?></p>
                        <div class="alpi-uploads-actions">
                            <a href="<?= htmlspecialchars($fileInfo['url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="alpi-btn alpi-btn-secondary alpi-btn-sm">Open</a>
                            <button type="button" class="alpi-btn alpi-btn-secondary alpi-btn-sm" data-copy-text="<?= htmlspecialchars($fileInfo['url'], ENT_QUOTES, 'UTF-8') ?>" data-default-label="Copy URL" data-success-label="Copied">Copy URL</button>
                            <form method="post" class="alpi-uploads-delete-form" data-confirm-message="Delete {name}? This cannot be undone.">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(alpiGetCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="delete" value="<?= htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit" class="alpi-btn alpi-btn-danger alpi-btn-sm" data-confirm-message="Delete {name}? This cannot be undone." data-confirm-name="<?= htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') ?>" data-loading-label="Deleting...">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../../../templates/footer-admin.php'; ?>
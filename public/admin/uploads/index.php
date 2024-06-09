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
        } catch (Exception $e) {
            $errorMessage = 'Error: ' . htmlspecialchars($e->getMessage());
        }
    }
}

$uploads = $upload->listFiles();

$displayUploads = [];

foreach ($uploads as $fileInfo) {
    $fileName = basename($fileInfo['path']);
    $uploadPath = '/uploads/' . $fileName;
    $uploadUrl = BASE_URL . $uploadPath;
    $uploadSize = filesize($fileInfo['path']);
    $uploadDate = date("F d Y H:i:s", filemtime($fileInfo['path']));

    $displayUploads[] = [
        'path' => $uploadPath,
        'url' => $uploadUrl,
        'size' => $uploadSize,
        'date' => $uploadDate,
    ];
}

?>

<?php include '../../../templates/header-admin.php'; ?>

<h1>Uploads</h1>

<?php if ($errorMessage) : ?>
    <div class="error-message"><?= $errorMessage ?></div>
<?php endif; ?>

<?php if ($successMessage) : ?>
    <div class="success-message"><?= $successMessage ?></div>
<?php endif; ?>

<form action="" method="post" enctype="multipart/form-data">
    Select file to upload:
    <input type="file" name="fileToUpload" id="fileToUpload" required>
    <input type="submit" value="Upload File" name="submit">
</form>

<table>
    <thead>
        <tr>
            <th>File Name</th>
            <th>File Size</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($displayUploads as $fileInfo) : ?>
            <tr>
                <td><a href="<?= htmlspecialchars($fileInfo['url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank"><?= htmlspecialchars($fileInfo['path'], ENT_QUOTES, 'UTF-8') ?></a></td>
                <td><?= htmlspecialchars($fileInfo['size'], ENT_QUOTES, 'UTF-8') ?> bytes</td>
                <td><?= htmlspecialchars($fileInfo['date'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="delete" value="<?= htmlspecialchars(basename($fileInfo['path']), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="submit" value="Delete">
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../../../templates/footer-admin.php'; ?>
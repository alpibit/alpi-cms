<?php

require '../../../config/database.php';
require '../../../config/config.php';
require '../../../config/autoload.php';
require '../../../classes/Upload.php';

$db = new Database();
$conn = $db->connect();
$upload = new Upload($conn);


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $fileName = basename($_POST['delete']);
    try {
        $upload->deleteFile($fileName);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['fileToUpload'])) {
    try {
        $upload->uploadFile($_FILES['fileToUpload']);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

$uploads = $upload->listFiles();



$displayUploads = [];

foreach ($uploads as $fileInfo) {
    $fileName = basename($fileInfo['path']);
    $uploadPath = '/uploads/' . $fileName;
    $uploadUrl = BASE_URL . $uploadPath;
    $uploadSize = filesize($fileInfo['path']);
    $uploadDate = date("F d Y H:i:s.", filemtime($fileInfo['path']));

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

<form action="" method="post" enctype="multipart/form-data">
    Select file to upload:
    <input type="file" name="fileToUpload" id="fileToUpload">
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
                <td><a href="<?= $fileInfo['url'] ?>" target="_blank"><?= $fileInfo['path'] ?></a></td>
                <td><?= $fileInfo['size'] ?></td>
                <td><?= $fileInfo['date'] ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="delete" value="<?= basename($fileInfo['path']) ?>">
                        <input type="submit" value="Delete">
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../../../templates/footer-admin.php'; ?>
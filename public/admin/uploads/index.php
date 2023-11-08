<?php

require '../../../config/database.php';
require '../../../config/config.php';
require '../../../config/autoload.php';

function getUploads()
{
    $uploads = [];
    $uploadDir = '../../../uploads';
    $uploadFiles = scandir($uploadDir);
    foreach ($uploadFiles as $uploadFile) {
        if ($uploadFile !== '.' && $uploadFile !== '..') {
            $uploadPath = '/uploads/' . $uploadFile;
            $uploadUrl = BASE_URL . $uploadPath;
            $uploadSize = filesize($uploadDir . '/' . $uploadFile);
            $uploadDate = date("F d Y H:i:s.", filemtime($uploadDir . '/' . $uploadFile));
            $uploads[] = [
                'path' => $uploadPath,
                'url' => $uploadUrl,
                'size' => $uploadSize,
                'date' => $uploadDate,
            ];
        }
    }
    return $uploads;
}


$uploads = getUploads();

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Uploads</title>
    <link rel="stylesheet" href="/assets/css/uploads.css">
</head>

<body class="uploads-wrap">

    <h1>Uploads</h1>

    <div class="btn-group">
        <button onclick="window.location.href='<?= BASE_URL ?>/public/admin/index.php'">Admin Dashboard</button>
    </div>

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
            <?php foreach ($uploads as $upload) : ?>
                <tr>
                    <td><a href="<?= $upload['url'] ?>" target="_blank"><?= $upload['path'] ?></a></td>
                    <td><?= $upload['size'] ?></td>
                    <td><?= $upload['date'] ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="delete" value="<?= $upload['path'] ?>">
                            <input type="submit" value="Delete">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>

</html>
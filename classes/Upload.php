<?php
class Upload
{
    protected $db;
    protected $uploadDir;
    protected $uploadUrl;
    protected $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
    protected $allowedVideoTypes = ['video/mp4', 'video/avi', 'video/mpeg'];
    protected $maxFileSize = 10485760; // 10MB

    public function __construct(PDO $db, $uploadDir = null, $uploadUrl = BASE_URL . '/uploads/')
    {
        $this->db = $db;
        $this->uploadDir = $uploadDir ?? realpath(__DIR__ . '/../uploads');
        $this->uploadUrl = $uploadUrl;
    }
    

    public function uploadFile($file)
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error in file upload');
        }

        if ($file['size'] > $this->maxFileSize) {
            throw new Exception('File is too large');
        }

        $fileMimeType = mime_content_type($file['tmp_name']);
        if (!in_array($fileMimeType, array_merge($this->allowedImageTypes, $this->allowedVideoTypes))) {
            throw new Exception('Invalid file type');
        }

        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileName = basename($file['name'], ".$fileExt") . '-' . uniqid() . '.' . $fileExt;
        $filePath = $this->uploadDir . '/' . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('Failed to move uploaded file.');
        }

        return $filePath;
    }

    public function listFiles()
    {
        $files = [];
        $fileList = scandir($this->uploadDir);

        foreach ($fileList as $file) {
            if ($file !== '.' && $file !== '..') {
                $filePath = $this->uploadDir . '/' . $file;
                $fileUrl = $this->uploadUrl . $file;
                $files[] = [
                    'path' => $filePath,
                    'url' => $fileUrl
                ];
            }
        }
        return $files;
    }

    public function deleteFile($fileName)
    {
        $filePath = $this->uploadDir . '/' . $fileName;

        if (file_exists($filePath)) {
            unlink($filePath);
        } else {
            throw new Exception('File does not exist.');
        }
    }
}

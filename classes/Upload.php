<?php
class Upload
{
    protected $db;
    protected $uploadDir;

    public function __construct(PDO $db, $uploadDir = BASE_URL . '/uploads/')
    {
        $this->db = $db;
        $this->uploadDir = $uploadDir;
    }
  

    public function uploadFile($file)
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error in file upload');
        }

        $fileName = basename($file['name']);
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
                $fileUrl = BASE_URL . '/uploads/' . $file;
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

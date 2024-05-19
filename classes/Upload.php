<?php
class Upload
{
    protected $db;
    protected $uploadDir;
    protected $uploadUrl;

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

		$fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
		if ($fileExt != 'png' && $fileExt != 'jpg' && $fileExt != 'jpeg' && $fileExt != 'gif') {
			throw new Exception('File is not an image');
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

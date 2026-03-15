<?php

class UploadInUseException extends RuntimeException
{
    private $usages;

    public function __construct(array $usages)
    {
        $this->usages = array_values(array_unique($usages));

        parent::__construct('File is still in use and cannot be deleted until those references are removed.');
    }

    public function getUsages(): array
    {
        return $this->usages;
    }
}

class Upload
{
    private const FILE_RULES = [
        'jpg' => ['mimes' => ['image/jpeg' => ['image']]],
        'jpeg' => ['mimes' => ['image/jpeg' => ['image']]],
        'png' => ['mimes' => ['image/png' => ['image', 'icon']]],
        'gif' => ['mimes' => ['image/gif' => ['image']]],
        'ico' => ['mimes' => [
            'image/x-icon' => ['icon'],
            'image/vnd.microsoft.icon' => ['icon'],
            'application/octet-stream' => ['icon'],
        ]],
        'mp4' => ['mimes' => ['video/mp4' => ['video']]],
        'avi' => ['mimes' => ['video/x-msvideo' => ['video']]],
        'mpeg' => ['mimes' => [
            'video/mpeg' => ['video'],
            'audio/mpeg' => ['audio'],
        ]],
        'webm' => ['mimes' => [
            'video/webm' => ['video'],
            'audio/webm' => ['audio'],
        ]],
        'ogg' => ['mimes' => [
            'video/ogg' => ['video'],
            'audio/ogg' => ['audio'],
        ]],
        'mov' => ['mimes' => ['video/quicktime' => ['video']]],
        'wmv' => ['mimes' => ['video/x-ms-wmv' => ['video']]],
        'flv' => ['mimes' => ['video/x-flv' => ['video']]],
        '3gp' => ['mimes' => ['video/3gpp' => ['video']]],
        'mp3' => ['mimes' => ['audio/mpeg' => ['audio']]],
        'wav' => ['mimes' => [
            'audio/wav' => ['audio'],
            'audio/x-wav' => ['audio'],
        ]],
        'oga' => ['mimes' => ['audio/ogg' => ['audio']]],
        'm4a' => ['mimes' => [
            'audio/mp4' => ['audio'],
            'audio/x-m4a' => ['audio'],
        ]],
        'aac' => ['mimes' => ['audio/aac' => ['audio']]],
        'flac' => ['mimes' => ['audio/flac' => ['audio']]],
    ];

    private const SETTINGS_FILE_KEYS = [
        'site_logo' => 'Setting: Site logo',
        'site_favicon' => 'Setting: Site favicon',
        'default_post_thumbnail' => 'Setting: Default post thumbnail',
    ];

    protected $db;
    protected $uploadDir;
    protected $uploadUrl;
    protected $maxFileSize = 10485760; // 10MB

    public function __construct(PDO $db, $uploadDir = null, $uploadUrl = BASE_URL . '/uploads/')
    {
        $this->db = $db;
        $defaultUploadDir = __DIR__ . '/../uploads';
        $resolvedUploadDir = $uploadDir ?? (is_dir($defaultUploadDir) ? realpath($defaultUploadDir) : $defaultUploadDir);

        $this->uploadDir = $resolvedUploadDir ?: $defaultUploadDir;
        $this->uploadUrl = rtrim((string) $uploadUrl, '/') . '/';
    }

    public static function getSupportedMediaGroups(): array
    {
        $groups = [];

        foreach (self::FILE_RULES as $rule) {
            foreach ($rule['mimes'] as $mimeGroups) {
                foreach ($mimeGroups as $group) {
                    $groups[] = $group;
                }
            }
        }

        return array_values(array_unique($groups));
    }

    public static function isSupportedMediaGroup($group): bool
    {
        return is_string($group) && in_array($group, self::getSupportedMediaGroups(), true);
    }

    public function getAcceptAttribute(array $groups = []): string
    {
        $extensions = $this->getAllowedExtensions($groups);

        return implode(',', array_map(static function ($extension) {
            return '.' . $extension;
        }, $extensions));
    }


    public function uploadFile($file)
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error in file upload');
        }

        if ($file['size'] > $this->maxFileSize) {
            throw new Exception('File is too large');
        }

        $fileMimeType = $this->detectMimeType($file['tmp_name']);
        $fileExt = $this->getFileExtension($file['name'] ?? '');

        if (!$this->isAllowedFile($fileExt, $fileMimeType)) {
            throw new Exception('Invalid file type');
        }

        if (!is_dir($this->uploadDir) || !is_writable($this->uploadDir)) {
            throw new Exception('Upload directory is not writable.');
        }

        $fileName = $this->sanitizeFileBaseName($file['name'] ?? 'file') . '-' . bin2hex(random_bytes(8)) . '.' . $fileExt;
        $filePath = $this->uploadDir . '/' . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('Failed to move uploaded file.');
        }

        return $filePath;
    }

    public function listFiles(array $groups = [])
    {
        $files = [];

        if (!is_dir($this->uploadDir)) {
            return $files;
        }

        $normalizedGroups = $this->normalizeGroups($groups);
        $fileList = scandir($this->uploadDir);

        foreach ($fileList as $file) {
            if ($file !== '.' && $file !== '..') {
                $filePath = $this->uploadDir . '/' . $file;

                if (!is_file($filePath)) {
                    continue;
                }

                $fileInfo = $this->buildFileInfo($filePath, $file);
                if ($fileInfo === null) {
                    continue;
                }

                if (!empty($normalizedGroups) && empty(array_intersect($normalizedGroups, $fileInfo['groups']))) {
                    continue;
                }

                $files[] = $fileInfo;
            }
        }

        return $files;
    }

    public function filterFilesByGroups(array $files, array $groups = []): array
    {
        $normalizedGroups = $this->normalizeGroups($groups);

        if (empty($normalizedGroups)) {
            return $files;
        }

        return array_values(array_filter($files, static function ($fileInfo) use ($normalizedGroups) {
            $fileGroups = $fileInfo['groups'] ?? [];

            return !empty(array_intersect($normalizedGroups, $fileGroups));
        }));
    }

    public function isAllowedFileUrl($fileUrl, array $groups = []): bool
    {
        $fileName = basename((string) parse_url((string) $fileUrl, PHP_URL_PATH));
        if ($fileName === '' || $fileName === '.' || $fileName === '..') {
            return false;
        }

        $filePath = $this->uploadDir . '/' . $fileName;
        if (!is_file($filePath)) {
            return false;
        }

        $fileInfo = $this->buildFileInfo($filePath, $fileName);
        if ($fileInfo === null) {
            return false;
        }

        $normalizedGroups = $this->normalizeGroups($groups);

        return empty($normalizedGroups) || !empty(array_intersect($normalizedGroups, $fileInfo['groups']));
    }

    public function sanitizeFileUrl($fileUrl, array $groups = []): string
    {
        $fileUrl = trim((string) $fileUrl);

        if ($fileUrl === '') {
            return '';
        }

        return $this->isAllowedFileUrl($fileUrl, $groups) ? $fileUrl : '';
    }

    public function deleteFile($fileName)
    {
        $safeFileName = basename((string) $fileName);
        if ($safeFileName === '' || $safeFileName === '.' || $safeFileName === '..') {
            throw new Exception('Invalid file name.');
        }

        $filePath = $this->uploadDir . '/' . $safeFileName;

        if (file_exists($filePath)) {
            $fileUrl = $this->buildFileUrl($safeFileName);
            $usages = $this->findFileUsages($fileUrl);

            if (!empty($usages)) {
                throw new UploadInUseException($usages);
            }

            unlink($filePath);
        } else {
            throw new Exception('File does not exist.');
        }
    }

    private function buildFileInfo(string $filePath, string $fileName): ?array
    {
        $fileExt = $this->getFileExtension($fileName);
        $fileType = $this->detectMimeType($filePath);

        if (!$this->isAllowedFile($fileExt, $fileType)) {
            return null;
        }

        $groups = $this->getGroupsForFile($fileExt, $fileType);
        $normalizedType = $this->getNormalizedMimeType($fileExt, $fileType);

        return [
            'path' => $filePath,
            'url' => $this->buildFileUrl($fileName),
            'type' => $normalizedType,
            'extension' => $fileExt,
            'groups' => $groups,
            'isImage' => in_array('image', $groups, true),
            'isVideo' => in_array('video', $groups, true),
            'isAudio' => in_array('audio', $groups, true),
            'isIcon' => in_array('icon', $groups, true),
        ];
    }

    private function normalizeGroups(array $groups): array
    {
        return array_values(array_unique(array_filter(array_map(static function ($group) {
            return is_string($group) ? trim($group) : '';
        }, $groups), static function ($group) {
            return self::isSupportedMediaGroup($group);
        })));
    }

    private function getAllowedExtensions(array $groups = []): array
    {
        $normalizedGroups = $this->normalizeGroups($groups);
        $extensions = [];

        foreach (self::FILE_RULES as $extension => $rule) {
            foreach ($rule['mimes'] as $mimeGroups) {
                if (empty($normalizedGroups) || !empty(array_intersect($normalizedGroups, $mimeGroups))) {
                    $extensions[] = $extension;
                    break;
                }
            }
        }

        sort($extensions);

        return array_values(array_unique($extensions));
    }

    private function getFileExtension(string $fileName): string
    {
        return strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    }

    private function detectMimeType(string $filePath): string
    {
        $mimeType = mime_content_type($filePath);

        return is_string($mimeType) ? strtolower($mimeType) : '';
    }

    private function isAllowedFile(string $extension, string $mimeType): bool
    {
        if ($extension === '' || !isset(self::FILE_RULES[$extension])) {
            return false;
        }

        if ($mimeType === '' || $mimeType === 'application/octet-stream') {
            return $extension === 'ico' && isset(self::FILE_RULES[$extension]['mimes']['application/octet-stream']);
        }

        return isset(self::FILE_RULES[$extension]['mimes'][$mimeType]);
    }

    private function getGroupsForFile(string $extension, string $mimeType): array
    {
        if (!$this->isAllowedFile($extension, $mimeType)) {
            return [];
        }

        if ($mimeType === '' || !isset(self::FILE_RULES[$extension]['mimes'][$mimeType])) {
            $mimeType = array_key_first(self::FILE_RULES[$extension]['mimes']);
        }

        return self::FILE_RULES[$extension]['mimes'][$mimeType] ?? [];
    }

    private function getNormalizedMimeType(string $extension, string $mimeType): string
    {
        if ($mimeType !== '' && $mimeType !== 'application/octet-stream') {
            return $mimeType;
        }

        return array_key_first(self::FILE_RULES[$extension]['mimes']) ?: 'application/octet-stream';
    }

    private function sanitizeFileBaseName(string $fileName): string
    {
        $baseName = strtolower(pathinfo($fileName, PATHINFO_FILENAME));
        $sanitized = preg_replace('/[^a-z0-9_-]+/', '-', $baseName);
        $sanitized = trim((string) $sanitized, '-_');

        return $sanitized !== '' ? $sanitized : 'file';
    }

    private function buildFileUrl(string $fileName): string
    {
        return $this->uploadUrl . $fileName;
    }

    private function findFileUsages(string $fileUrl): array
    {
        return array_values(array_unique(array_merge(
            $this->findContentFeaturedImageUsages($fileUrl),
            $this->findSettingUsages($fileUrl),
            $this->findDirectBlockUsages($fileUrl),
            $this->findGalleryUsages($fileUrl)
        )));
    }

    private function findContentFeaturedImageUsages(string $fileUrl): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.title, ct.name AS content_type
             FROM contents c
             INNER JOIN content_types ct ON ct.id = c.content_type_id
             WHERE c.main_image_path = :file_url'
        );
        $stmt->execute(['file_url' => $fileUrl]);

        $usages = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $usages[] = ucfirst((string) $row['content_type']) . ' featured image: ' . $this->formatTitle($row['title'] ?? '');
        }

        return $usages;
    }

    private function findSettingUsages(string $fileUrl): array
    {
        $keys = array_keys(self::SETTINGS_FILE_KEYS);
        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $params = array_merge($keys, [$fileUrl]);

        $stmt = $this->db->prepare(
            "SELECT setting_key FROM settings WHERE setting_key IN ({$placeholders}) AND setting_value = ?"
        );
        $stmt->execute($params);

        $usages = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $settingKey = $row['setting_key'] ?? '';
            if (isset(self::SETTINGS_FILE_KEYS[$settingKey])) {
                $usages[] = self::SETTINGS_FILE_KEYS[$settingKey];
            }
        }

        return $usages;
    }

    private function findDirectBlockUsages(string $fileUrl): array
    {
        $fieldLabels = [
            'image_path' => 'image field',
            'video_file' => 'video upload',
            'audio_file' => 'audio upload',
            'background_image_desktop' => 'desktop background image',
            'background_image_tablet' => 'tablet background image',
            'background_image_mobile' => 'mobile background image',
            'thumbnail_path' => 'thumbnail image',
            'video_url' => 'legacy media URL',
            'audio_url' => 'audio URL',
        ];

        $columns = implode(', ', array_map(static function ($field) {
            return 'b.' . $field;
        }, array_keys($fieldLabels)));
        $conditions = implode(' OR ', array_map(static function ($field) {
            return 'b.' . $field . ' = ?';
        }, array_keys($fieldLabels)));
        $params = array_fill(0, count($fieldLabels), $fileUrl);

        $stmt = $this->db->prepare(
            "SELECT b.type, c.title, ct.name AS content_type, {$columns}
             FROM blocks b
             INNER JOIN contents c ON c.id = b.content_id
             INNER JOIN content_types ct ON ct.id = c.content_type_id
             WHERE {$conditions}"
        );
        $stmt->execute($params);

        $usages = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            foreach ($fieldLabels as $field => $label) {
                if (($row[$field] ?? '') === $fileUrl) {
                    $usages[] = ucfirst((string) $row['content_type']) . ' block: ' . $this->formatTitle($row['title'] ?? '') . ' (' . str_replace('_', ' ', (string) $row['type']) . ', ' . $label . ')';
                }
            }
        }

        return $usages;
    }

    private function findGalleryUsages(string $fileUrl): array
    {
        $stmt = $this->db->prepare(
            'SELECT b.type, c.title, ct.name AS content_type, b.gallery_data
             FROM blocks b
             INNER JOIN contents c ON c.id = b.content_id
             INNER JOIN content_types ct ON ct.id = c.content_type_id
             WHERE b.gallery_data LIKE :gallery_match'
        );
        $stmt->execute(['gallery_match' => '%' . $fileUrl . '%']);

        $usages = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $galleryData = json_decode((string) ($row['gallery_data'] ?? ''), true);
            if (!is_array($galleryData)) {
                continue;
            }

            foreach ($galleryData as $index => $imageData) {
                if (($imageData['url'] ?? '') === $fileUrl) {
                    $usages[] = ucfirst((string) $row['content_type']) . ' block: ' . $this->formatTitle($row['title'] ?? '') . ' (' . str_replace('_', ' ', (string) $row['type']) . ', gallery image ' . ($index + 1) . ')';
                }
            }
        }

        return $usages;
    }

    private function formatTitle(string $title): string
    {
        return $title !== '' ? $title : '(untitled)';
    }
}

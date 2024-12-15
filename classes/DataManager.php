<?php

class DataManager
{
    protected $db;
    protected $supportedFormats = ['json', 'xml'];
    protected $contentTypes = ['posts', 'pages', 'categories', 'blocks', 'settings'];
    protected $dateFormat = 'Y-m-d H:i:s';
    protected $errors = [];
    protected $warnings = [];
    protected $tableSchemas = [];
    protected $idMaps = [
        'contents' => [],
        'categories' => [],
        'blocks' => []
    ];
    protected $relationshipMaps = [
        'posts' => [
            'table' => 'contents',
            'conditions' => ['content_type_id' => ['table' => 'content_types', 'where' => ['name' => 'post']]],
            'relationships' => [
                'blocks' => ['type' => 'one_to_many', 'table' => 'blocks', 'foreign_key' => 'content_id'],
                'category' => ['type' => 'many_to_one', 'table' => 'categories', 'foreign_key' => 'category_id']
            ]
        ],
        'pages' => [
            'table' => 'contents',
            'conditions' => ['content_type_id' => ['table' => 'content_types', 'where' => ['name' => 'page']]],
            'relationships' => [
                'blocks' => ['type' => 'one_to_many', 'table' => 'blocks', 'foreign_key' => 'content_id']
            ]
        ],
        'categories' => [
            'table' => 'categories',
            'relationships' => [
                'posts' => ['type' => 'one_to_many', 'table' => 'contents', 'foreign_key' => 'category_id']
            ]
        ]
    ];

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->loadTableSchemas();
    }

    protected function loadTableSchemas()
    {
        $tables = ['contents', 'blocks', 'categories', 'settings', 'content_types'];
        foreach ($tables as $table) {
            $query = "DESCRIBE {$table}";
            $stmt = $this->db->query($query);
            $this->tableSchemas[$table] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    public function export($format, $types = [])
    {
        if (!in_array($format, $this->supportedFormats)) {
            throw new Exception("Unsupported format: {$format}");
        }

        if (empty($types)) {
            $types = $this->contentTypes;
        }

        $data = [
            'metadata' => [
                'export_date' => date($this->dateFormat),
                'version' => '1.0',
                'cms_version' => '1.0',
                'content_types' => $types
            ]
        ];

        foreach ($types as $type) {
            if (method_exists($this, "export{$type}")) {
                $data[$type] = $this->{"export{$type}"}();
            }
        }

        return $this->formatData($data, $format);
    }

    public function import($file, $format)
    {
        if (!in_array($format, $this->supportedFormats)) {
            throw new Exception("Unsupported format: {$format}");
        }

        $data = $this->parseImportFile($file, $format);

        if (!$this->validateImportData($data)) {
            throw new Exception("Invalid import data structure: " . implode(", ", $this->errors));
        }

        $this->db->beginTransaction();

        try {
            if (isset($data['categories'])) {
                $this->importCategories($data['categories']);
            }

            foreach ($data as $type => $items) {
                if ($type !== 'metadata' && $type !== 'categories' && method_exists($this, "import{$type}")) {
                    $this->{"import{$type}"}($items);
                }
            }

            $this->db->commit();
            return ['success' => true, 'warnings' => $this->warnings];
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    protected function validateImportData($data)
    {
        if (!isset($data['metadata'])) {
            $this->errors[] = "Missing metadata section";
            return false;
        }

        if (!isset($data['metadata']['version'])) {
            $this->errors[] = "Missing version information";
            return false;
        }

        return true;
    }

    protected function parseImportFile($file, $format)
    {
        $content = file_get_contents($file);

        switch ($format) {
            case 'json':
                $data = json_decode($content, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception("Invalid JSON format: " . json_last_error_msg());
                }
                break;

            case 'xml':
                $xml = simplexml_load_string($content);
                if ($xml === false) {
                    throw new Exception("Invalid XML format");
                }
                $data = json_decode(json_encode($xml), true);
                break;

            default:
                throw new Exception("Unsupported format");
        }

        return $data;
    }

    protected function mapFields($data, $table)
    {
        $schema = $this->tableSchemas[$table];
        $mapped = [];

        foreach ($schema as $field) {
            $fieldName = $field['Field'];

            if ($fieldName === 'id') {
                continue;
            }

            if (isset($data[$fieldName])) {
                $mapped[$fieldName] = $this->formatFieldValue($data[$fieldName], $field['Type']);
            } elseif ($field['Null'] === 'NO' && $field['Default'] === null && !$field['Extra'] === 'auto_increment') {
                switch ($fieldName) {
                    case 'created_at':
                    case 'updated_at':
                        $mapped[$fieldName] = date($this->dateFormat);
                        break;
                    default:
                        $this->warnings[] = "Missing required field: {$fieldName} in table {$table}";
                }
            }
        }

        return $mapped;
    }

    protected function formatFieldValue($value, $type)
    {
        if (strpos($type, 'int') !== false) {
            return (int)$value;
        } elseif (strpos($type, 'float') !== false || strpos($type, 'double') !== false || strpos($type, 'decimal') !== false) {
            return (float)$value;
        } elseif (strpos($type, 'datetime') !== false || strpos($type, 'timestamp') !== false) {
            return date($this->dateFormat, strtotime($value));
        } elseif (strpos($type, 'bool') !== false) {
            return (bool)$value;
        }
        return $value;
    }

    protected function generateUniqueSlug($table, $slug)
    {
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($table, $slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function slugExists($table, $slug)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$table} WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetchColumn() > 0;
    }

    protected function exportPosts()
    {
        $map = $this->relationshipMaps['posts'];
        $query = $this->buildExportQuery($map);
        $stmt = $this->db->query($query);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($posts as &$post) {
            foreach ($map['relationships'] as $relation => $config) {
                if ($config['type'] === 'one_to_many') {
                    $post[$relation] = $this->fetchRelatedRecords(
                        $config['table'],
                        $config['foreign_key'],
                        $post['id']
                    );
                }
            }
        }

        return $posts;
    }

    protected function importPosts($posts)
    {
        foreach ($posts as $post) {
            $oldId = $post['id'] ?? null;
            unset($post['id']);

            $postData = $this->mapFields($post, 'contents');

            $stmt = $this->db->prepare("SELECT id FROM content_types WHERE name = 'post'");
            $stmt->execute();
            $postData['content_type_id'] = $stmt->fetchColumn();

            if (isset($postData['category_id']) && isset($this->idMaps['categories'][$postData['category_id']])) {
                $postData['category_id'] = $this->idMaps['categories'][$postData['category_id']];
            }

            $postData['slug'] = $this->generateUniqueSlug('contents', $postData['slug']);

            $this->insertRecord('contents', $postData);
            $newId = $this->db->lastInsertId();

            if ($oldId) {
                $this->idMaps['contents'][$oldId] = $newId;
            }

            if (isset($post['blocks'])) {
                foreach ($post['blocks'] as $block) {
                    unset($block['id']);
                    $blockData = $this->mapFields($block, 'blocks');
                    $blockData['content_id'] = $newId;
                    $this->insertRecord('blocks', $blockData);
                }
            }
        }
    }

    protected function exportPages()
    {
        $map = $this->relationshipMaps['pages'];
        $query = $this->buildExportQuery($map);
        $stmt = $this->db->query($query);
        $pages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($pages as &$page) {
            foreach ($map['relationships'] as $relation => $config) {
                if ($config['type'] === 'one_to_many') {
                    $page[$relation] = $this->fetchRelatedRecords(
                        $config['table'],
                        $config['foreign_key'],
                        $page['id']
                    );
                }
            }
        }

        return $pages;
    }

    protected function importPages($pages)
    {
        foreach ($pages as $page) {
            $oldId = $page['id'] ?? null;
            unset($page['id']);

            $pageData = $this->mapFields($page, 'contents');

            $stmt = $this->db->prepare("SELECT id FROM content_types WHERE name = 'page'");
            $stmt->execute();
            $pageData['content_type_id'] = $stmt->fetchColumn();

            $pageData['slug'] = $this->generateUniqueSlug('contents', $pageData['slug']);

            $this->insertRecord('contents', $pageData);
            $newId = $this->db->lastInsertId();

            if ($oldId) {
                $this->idMaps['contents'][$oldId] = $newId;
            }

            if (isset($page['blocks'])) {
                foreach ($page['blocks'] as $block) {
                    unset($block['id']);
                    $blockData = $this->mapFields($block, 'blocks');
                    $blockData['content_id'] = $newId;
                    $this->insertRecord('blocks', $blockData);
                }
            }
        }
    }

    protected function exportCategories()
    {
        $stmt = $this->db->query("SELECT * FROM categories");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function importCategories($categories)
    {
        foreach ($categories as $category) {
            $oldId = $category['id'] ?? null;
            unset($category['id']);

            $stmt = $this->db->prepare("SELECT id FROM categories WHERE slug = ?");
            $stmt->execute([$category['slug']]);
            $existingId = $stmt->fetchColumn();

            if ($existingId) {
                $this->idMaps['categories'][$oldId] = $existingId;
                $this->warnings[] = "Category with slug '{$category['slug']}' already exists. Mapped to existing category.";
            } else {
                $categoryData = $this->mapFields($category, 'categories');
                $this->insertRecord('categories', $categoryData);
                $newId = $this->db->lastInsertId();

                if ($oldId) {
                    $this->idMaps['categories'][$oldId] = $newId;
                }
            }
        }
    }

    protected function exportSettings()
    {
        $stmt = $this->db->query("SELECT * FROM settings");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function importSettings($settings)
    {
        foreach ($settings as $setting) {
            unset($setting['id']);

            $stmt = $this->db->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?");
            $stmt->execute([$setting['setting_key']]);
            $exists = $stmt->fetchColumn();

            $settingData = $this->mapFields($setting, 'settings');

            if ($exists) {
                $updateQuery = "UPDATE settings SET setting_value = ? WHERE setting_key = ?";
                $stmt = $this->db->prepare($updateQuery);
                $stmt->execute([$settingData['setting_value'], $settingData['setting_key']]);
                $this->warnings[] = "Updated existing setting: {$settingData['setting_key']}";
            } else {
                $this->insertRecord('settings', $settingData);
            }
        }
    }

    protected function buildExportQuery($map)
    {
        $query = "SELECT * FROM {$map['table']}";

        if (isset($map['conditions'])) {
            $conditions = [];
            foreach ($map['conditions'] as $field => $condition) {
                if (isset($condition['table']) && isset($condition['where'])) {
                    $subquery = "SELECT id FROM {$condition['table']} WHERE ";
                    $subconditions = [];
                    foreach ($condition['where'] as $key => $value) {
                        $subconditions[] = "{$key} = '{$value}'";
                    }
                    $subquery .= implode(' AND ', $subconditions);
                    $conditions[] = "{$field} = ({$subquery})";
                }
            }
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(' AND ', $conditions);
            }
        }

        return $query;
    }

    protected function fetchRelatedRecords($table, $foreignKey, $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$table} WHERE {$foreignKey} = ?");
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function insertRecord($table, $data)
    {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');

        $query = "INSERT INTO {$table} (" . implode(', ', $fields) . ") 
                 VALUES (" . implode(', ', $placeholders) . ")";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute(array_values($data));
        } catch (PDOException $e) {
            throw new Exception("Error inserting into {$table}: " . $e->getMessage() .
                "\nData: " . print_r($data, true));
        }
    }

    protected function formatData($data, $format)
    {
        switch ($format) {
            case 'json':
                return json_encode($data, JSON_PRETTY_PRINT);
            case 'xml':
                $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><export></export>');
                $this->arrayToXml($data, $xml);
                return $xml->asXML();
            default:
                throw new Exception("Unsupported format");
        }
    }

    protected function arrayToXml($data, &$xml)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'item';
                }
                $subnode = $xml->addChild($key);
                $this->arrayToXml($value, $subnode);
            } else {
                $xml->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getWarnings()
    {
        return $this->warnings;
    }

    public function getSupportedFormats()
    {
        return $this->supportedFormats;
    }

    public function getContentTypes()
    {
        return $this->contentTypes;
    }
}

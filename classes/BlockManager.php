<?php

require_once __DIR__ . '/BlockData.php';

class BlockManager
{
    protected $db;
    protected $insertSql;

    public function __construct(PDO $db)
    {
        $this->db = $db;

        $columns = array_merge(['content_id'], BlockData::getFieldNames());
        $placeholders = array_map(function ($column) {
            return ':' . $column;
        }, $columns);

        $this->insertSql = 'INSERT INTO blocks (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';
    }

    public function insertBlocksForContent($contentId, array $blocks)
    {
        $normalizedBlocks = BlockData::normalizeSubmittedBlocks($blocks);

        foreach ($normalizedBlocks as $block) {
            $this->insertBlock($contentId, $block);
        }
    }

    public function replaceBlocksForContent($contentId, array $blocks)
    {
        $this->deleteBlocksByContentId($contentId);
        $this->insertBlocksForContent($contentId, $blocks);
    }

    public function deleteBlocksByContentId($contentId)
    {
        $stmt = $this->db->prepare('DELETE FROM blocks WHERE content_id = :contentId');
        $stmt->bindValue(':contentId', (int) $contentId, PDO::PARAM_INT);
        $stmt->execute();
    }

    protected function insertBlock($contentId, array $block)
    {
        $stmt = $this->db->prepare($this->insertSql);
        $stmt->bindValue(':content_id', (int) $contentId, PDO::PARAM_INT);

        foreach ($block as $field => $value) {
            $placeholder = ':' . $field;
            if ($value === null) {
                $stmt->bindValue($placeholder, null, PDO::PARAM_NULL);
                continue;
            }

            $stmt->bindValue($placeholder, $value, BlockData::getPdoType($field));
        }

        $stmt->execute();
    }
}
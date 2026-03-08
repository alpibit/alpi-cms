<?php

class BlockRenderer
{
    protected $conn;
    protected $assetManager;
    protected $blockBasePath;
    protected $viewContext;

    public function __construct(PDO $conn, AssetManager $assetManager, array $viewContext = [], $blockBasePath = null)
    {
        $this->conn = $conn;
        $this->assetManager = $assetManager;
        $this->viewContext = $viewContext;
        $this->blockBasePath = $blockBasePath ?: __DIR__ . '/../blocks/types/';
    }

    public function preloadAssets(array $blocks)
    {
        foreach ($blocks as $block) {
            $this->registerAssets($block['type'] ?? '');
        }
    }

    public function renderBlocks(array $blocks)
    {
        foreach ($blocks as $block) {
            $this->renderBlock($block);
        }
    }

    public function renderBlock(array $block)
    {
        $blockType = trim((string) ($block['type'] ?? ''));
        if ($blockType === '') {
            return;
        }

        $this->registerAssets($blockType);

        $blockPath = $this->blockBasePath . $blockType . '.php';
        if (!file_exists($blockPath)) {
            echo 'Block type not found.';
            return;
        }

        $conn = $this->conn;
        $assetManager = $this->assetManager;
        $blockAccordionData = $block['accordion_data'] ?? '';

        extract($this->viewContext, EXTR_SKIP);

        include $blockPath;
    }

    protected function registerAssets($blockType)
    {
        $blockType = trim((string) $blockType);
        if ($blockType === '') {
            return;
        }

        $this->assetManager->addCss("blocks/{$blockType}.css");
        $this->assetManager->addJs("blocks/{$blockType}.js");
    }
}
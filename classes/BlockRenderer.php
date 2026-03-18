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

        if (!BlockRegistry::hasType($blockType)) {
            $this->renderUnavailableComment("Unknown block type: {$blockType}");
            return;
        }

        if (!BlockRegistry::supportsFrontendRendering($blockType)) {
            $this->renderUnavailableComment("Frontend renderer unavailable for block type: {$blockType}");
            return;
        }

        $this->registerAssets($blockType);

        $templateFile = BlockRegistry::getTemplateFile($blockType);
        if ($templateFile === null) {
            $this->renderUnavailableComment("Missing block renderer mapping for type: {$blockType}");
            return;
        }

        $blockPath = $this->blockBasePath . $templateFile;
        if (!file_exists($blockPath)) {
            $this->renderUnavailableComment("Missing block renderer file for type: {$blockType}");
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
        if ($blockType === '' || !BlockRegistry::supportsFrontendRendering($blockType)) {
            return;
        }

        $this->assetManager->addCss("blocks/{$blockType}.css");
        $this->assetManager->addJs("blocks/{$blockType}.js");
    }

    protected function renderUnavailableComment($message)
    {
        echo '<!-- ' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . ' -->';
    }
}

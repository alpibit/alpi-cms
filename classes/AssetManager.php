<?php

class AssetManager
{
    private $cssLinks = [];
    private $jsLinks = [];
    private $addedCss = [];
    private $addedJs = [];

    public function addCss($cssPath)
    {
        if (!in_array($cssPath, $this->addedCss)) {
            if (file_exists(__DIR__ . '/../assets/css/' . $cssPath)) {
                $cssLink = "<link rel='stylesheet' href='" . BASE_URL . "/assets/css/{$cssPath}'>";
                $this->cssLinks[] = $cssLink;
                $this->addedCss[] = $cssPath;
            }
        }
    }

    public function addJs($jsPath)
    {
        if (!in_array($jsPath, $this->addedJs)) {
            if (file_exists(__DIR__ . '/../assets/js/' . $jsPath)) {
                $jsLink = "<script src='" . BASE_URL . "/assets/js/{$jsPath}'></script>";
                $this->jsLinks[] = $jsLink;
                $this->addedJs[] = $jsPath;
            }
        }
    }

    public function getCssLinks()
    {
        return implode("\n", $this->cssLinks);
    }

    public function getJsLinks()
    {
        return implode("\n", $this->jsLinks);
    }
}

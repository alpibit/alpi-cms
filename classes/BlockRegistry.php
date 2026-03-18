<?php

class BlockRegistry
{
    private const BLOCK_DEFINITIONS = [
        'text' => [
            'label' => 'Text',
            'editor_enabled' => true,
            'template' => 'text.php',
            'frontend_enabled' => true,
        ],
        'image_text' => [
            'label' => 'Image + Text',
            'editor_enabled' => true,
            'template' => 'image_text.php',
            'frontend_enabled' => true,
        ],
        'image' => [
            'label' => 'Image',
            'editor_enabled' => true,
            'template' => 'image.php',
            'frontend_enabled' => true,
        ],
        'cta' => [
            'label' => 'Call to Action',
            'editor_enabled' => true,
            'template' => 'cta.php',
            'frontend_enabled' => true,
        ],
        'post_picker' => [
            'label' => 'Post Picker',
            'editor_enabled' => true,
            'template' => 'post_picker.php',
            'frontend_enabled' => true,
        ],
        'video' => [
            'label' => 'Video',
            'editor_enabled' => true,
            'template' => 'video.php',
            'frontend_enabled' => true,
        ],
        'slider_gallery' => [
            'label' => 'Slider Gallery',
            'editor_enabled' => true,
            'template' => 'slider_gallery.php',
            'frontend_enabled' => true,
        ],
        'quote' => [
            'label' => 'Quote',
            'editor_enabled' => true,
            'template' => 'quote.php',
            'frontend_enabled' => true,
        ],
        'accordion' => [
            'label' => 'Accordion',
            'editor_enabled' => true,
            'template' => 'accordion.php',
            'frontend_enabled' => true,
        ],
        'audio' => [
            'label' => 'Audio',
            'editor_enabled' => true,
            'template' => 'audio.php',
            'frontend_enabled' => true,
        ],
        'free_code' => [
            'label' => 'Free Code',
            'editor_enabled' => true,
            'template' => 'free_code.php',
            'frontend_enabled' => true,
        ],
        'map' => [
            'label' => 'Map',
            'editor_enabled' => true,
            'template' => 'map.php',
            'frontend_enabled' => true,
        ],
        'form' => [
            'label' => 'Form',
            'editor_enabled' => true,
            'template' => null,
            'frontend_enabled' => false,
        ],
        'hero' => [
            'label' => 'Hero',
            'editor_enabled' => true,
            'template' => 'hero.php',
            'frontend_enabled' => true,
        ],
    ];

    public static function getDefinitions()
    {
        return self::BLOCK_DEFINITIONS;
    }

    public static function getEditorTypes()
    {
        return array_keys(self::getEditorDefinitions());
    }

    public static function getEditorOptions()
    {
        $options = [];

        foreach (self::getEditorDefinitions() as $type => $definition) {
            $options[] = [
                'value' => $type,
                'label' => $definition['label'],
            ];
        }

        return $options;
    }

    public static function renderEditorOptionTags($selectedType = '')
    {
        $selectedType = trim((string) $selectedType);
        $optionTags = [];

        foreach (self::getEditorOptions() as $option) {
            $value = htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8');
            $label = htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8');
            $selected = $option['value'] === $selectedType ? ' selected' : '';
            $optionTags[] = "<option value='{$value}'{$selected}>{$label}</option>";
        }

        return implode('', $optionTags);
    }

    public static function getLabel($type)
    {
        if (self::hasType($type)) {
            return self::BLOCK_DEFINITIONS[$type]['label'];
        }

        return self::formatFallbackLabel($type);
    }

    public static function hasType($type)
    {
        $type = trim((string) $type);
        return $type !== '' && array_key_exists($type, self::BLOCK_DEFINITIONS);
    }

    public static function isEditorEnabled($type)
    {
        return self::hasType($type) && !empty(self::BLOCK_DEFINITIONS[$type]['editor_enabled']);
    }

    public static function supportsFrontendRendering($type)
    {
        return self::hasType($type) && !empty(self::BLOCK_DEFINITIONS[$type]['frontend_enabled']);
    }

    public static function getTemplateFile($type)
    {
        if (!self::supportsFrontendRendering($type)) {
            return null;
        }

        $template = trim((string) (self::BLOCK_DEFINITIONS[$type]['template'] ?? ''));
        return $template === '' ? null : $template;
    }

    private static function getEditorDefinitions()
    {
        return array_filter(self::BLOCK_DEFINITIONS, static function (array $definition) {
            return !empty($definition['editor_enabled']);
        });
    }

    private static function formatFallbackLabel($type)
    {
        return ucfirst(str_replace('_', ' ', trim((string) $type)));
    }
}

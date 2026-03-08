<?php

class BlockData
{
    private const FIELD_DEFINITIONS = [
        'type' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'title' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'content' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'selected_post_ids' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'image_path' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'alt_text' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'caption' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'url1' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'cta_text1' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'url2' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'cta_text2' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'video_url' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'video_source' => ['default' => 'url', 'pdo' => PDO::PARAM_STR],
        'video_file' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'audio_url' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'audio_source' => ['default' => 'url', 'pdo' => PDO::PARAM_STR],
        'audio_file' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'slider_type' => ['default' => 'image', 'pdo' => PDO::PARAM_STR],
        'slider_speed' => ['default' => 0, 'pdo' => PDO::PARAM_INT],
        'free_code_content' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'map_embed_code' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'form_shortcode' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'gallery_data' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'quotes_data' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'accordion_data' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'transition_speed' => ['default' => 0, 'pdo' => PDO::PARAM_INT],
        'transition_effect' => ['default' => 'slide', 'pdo' => PDO::PARAM_STR],
        'autoplay' => ['default' => false, 'pdo' => PDO::PARAM_BOOL],
        'pause_on_hover' => ['default' => false, 'pdo' => PDO::PARAM_BOOL],
        'infinite_loop' => ['default' => false, 'pdo' => PDO::PARAM_BOOL],
        'show_arrows' => ['default' => false, 'pdo' => PDO::PARAM_BOOL],
        'show_dots' => ['default' => false, 'pdo' => PDO::PARAM_BOOL],
        'dot_style' => ['default' => 'classic', 'pdo' => PDO::PARAM_STR],
        'lazy_load' => ['default' => false, 'pdo' => PDO::PARAM_BOOL],
        'aspect_ratio' => ['default' => '16:9', 'pdo' => PDO::PARAM_STR],
        'lightbox_enabled' => ['default' => false, 'pdo' => PDO::PARAM_BOOL],
        'thumbnail_path' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'background_type_desktop' => ['default' => 'image', 'pdo' => PDO::PARAM_STR],
        'background_type_tablet' => ['default' => 'image', 'pdo' => PDO::PARAM_STR],
        'background_type_mobile' => ['default' => 'image', 'pdo' => PDO::PARAM_STR],
        'background_image_desktop' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'background_image_tablet' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'background_image_mobile' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'background_video_url' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'background_color' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'background_opacity_desktop' => ['default' => '1.0', 'pdo' => PDO::PARAM_STR],
        'background_opacity_tablet' => ['default' => '1.0', 'pdo' => PDO::PARAM_STR],
        'background_opacity_mobile' => ['default' => '1.0', 'pdo' => PDO::PARAM_STR],
        'background_style' => ['default' => 'cover', 'pdo' => PDO::PARAM_STR],
        'hero_layout' => ['default' => 'center', 'pdo' => PDO::PARAM_STR],
        'overlay_color' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'text_color' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'text_size_desktop' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'text_size_tablet' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'text_size_mobile' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'padding_top_desktop' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'padding_bottom_desktop' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'padding_top_tablet' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'padding_bottom_tablet' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'padding_top_mobile' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'padding_bottom_mobile' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'margin_top_desktop' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'margin_bottom_desktop' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'margin_top_tablet' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'margin_bottom_tablet' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'margin_top_mobile' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'margin_bottom_mobile' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'layout1' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'layout2' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'layout3' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'layout4' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'layout5' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'layout6' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'layout7' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'layout8' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'layout9' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'layout10' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'style1' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'style2' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'style3' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'style4' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'style5' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'style6' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'style7' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'style8' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'style9' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'style10' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'responsive_class' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'responsive_style' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'border_style' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'border_color' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'border_width' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'animation_type' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'animation_duration' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'custom_css' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'custom_js' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'aria_label' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'text_size' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'class' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'metafield1' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'metafield2' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'metafield3' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'metafield4' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'metafield5' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'metafield6' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'metafield7' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'metafield8' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'metafield9' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'metafield10' => ['default' => '', 'pdo' => PDO::PARAM_STR],
        'order_num' => ['default' => 0, 'pdo' => PDO::PARAM_INT],
        'status' => ['default' => 'active', 'pdo' => PDO::PARAM_STR],
        'start_date' => ['default' => null, 'pdo' => PDO::PARAM_STR],
        'end_date' => ['default' => null, 'pdo' => PDO::PARAM_STR],
    ];

    private const BOOLEAN_FIELDS = [
        'autoplay',
        'pause_on_hover',
        'infinite_loop',
        'show_arrows',
        'show_dots',
        'lazy_load',
        'lightbox_enabled',
    ];

    private const INTEGER_FIELDS = [
        'slider_speed',
        'transition_speed',
        'order_num',
    ];

    private const DECIMAL_FIELDS = [
        'background_opacity_desktop',
        'background_opacity_tablet',
        'background_opacity_mobile',
    ];

    private const JSON_FIELDS = [
        'gallery_data',
        'quotes_data',
        'accordion_data',
    ];

    public static function normalizeSubmittedBlocks(array $blocks)
    {
        $normalizedBlocks = [];

        foreach ($blocks as $index => $block) {
            $normalizedBlock = self::normalizeBlock($block, $index + 1);
            if ($normalizedBlock !== null) {
                $normalizedBlocks[] = $normalizedBlock;
            }
        }

        return $normalizedBlocks;
    }

    public static function getFieldNames()
    {
        return array_keys(self::FIELD_DEFINITIONS);
    }

    public static function getPdoType($field)
    {
        return self::FIELD_DEFINITIONS[$field]['pdo'] ?? PDO::PARAM_STR;
    }

    private static function normalizeBlock(array $block, $orderNum)
    {
        $type = trim((string) ($block['type'] ?? ''));
        if ($type === '') {
            return null;
        }

        $normalized = [];
        foreach (self::FIELD_DEFINITIONS as $field => $definition) {
            $normalized[$field] = array_key_exists($field, $block) ? $block[$field] : $definition['default'];
        }

        $normalized['type'] = $type;
        $normalized['selected_post_ids'] = self::normalizeSelectedPostIds($normalized['selected_post_ids']);

        foreach (self::JSON_FIELDS as $field) {
            $normalized[$field] = self::normalizeJsonField($normalized[$field]);
        }

        foreach (self::BOOLEAN_FIELDS as $field) {
            $normalized[$field] = !empty($normalized[$field]);
        }

        foreach (self::INTEGER_FIELDS as $field) {
            $normalized[$field] = (int) ($normalized[$field] ?? 0);
        }

        foreach (self::DECIMAL_FIELDS as $field) {
            $normalized[$field] = self::normalizeDecimal($normalized[$field], self::FIELD_DEFINITIONS[$field]['default']);
        }

        $normalized['video_source'] = self::normalizeEnum($normalized['video_source'], ['url', 'upload'], 'url');
        $normalized['audio_source'] = self::normalizeEnum($normalized['audio_source'], ['url', 'upload'], 'url');
        $normalized['slider_type'] = self::normalizeEnum($normalized['slider_type'], ['image', 'quote'], 'image');
        $normalized['transition_effect'] = self::normalizeEnum($normalized['transition_effect'], ['slide', 'fade'], 'slide');
        $normalized['dot_style'] = self::normalizeEnum($normalized['dot_style'], ['classic', 'thumbnail'], 'classic');
        $normalized['aspect_ratio'] = self::normalizeEnum($normalized['aspect_ratio'], ['16:9', '4:3', '1:1', 'custom'], '16:9');
        $normalized['background_type_desktop'] = self::normalizeEnum($normalized['background_type_desktop'], ['image', 'video', 'color'], 'image');
        $normalized['background_type_tablet'] = self::normalizeEnum($normalized['background_type_tablet'], ['image', 'video', 'color'], 'image');
        $normalized['background_type_mobile'] = self::normalizeEnum($normalized['background_type_mobile'], ['image', 'video', 'color'], 'image');
        $normalized['background_style'] = self::normalizeEnum($normalized['background_style'], ['cover', 'contain', 'repeat', 'no-repeat'], 'cover');
        $normalized['hero_layout'] = self::normalizeEnum($normalized['hero_layout'], ['left', 'center', 'right'], 'center');

        $normalized['status'] = trim((string) ($normalized['status'] ?? 'active')) ?: 'active';
        $normalized['order_num'] = (int) $orderNum;
        $normalized['start_date'] = self::normalizeNullableString($normalized['start_date']);
        $normalized['end_date'] = self::normalizeNullableString($normalized['end_date']);

        foreach ($normalized as $field => $value) {
            if (in_array($field, self::BOOLEAN_FIELDS, true) || in_array($field, self::INTEGER_FIELDS, true) || in_array($field, self::DECIMAL_FIELDS, true)) {
                continue;
            }

            if ($value === null) {
                continue;
            }

            if (is_array($value)) {
                $normalized[$field] = '';
                continue;
            }

            $normalized[$field] = (string) $value;
        }

        return $normalized;
    }

    private static function normalizeSelectedPostIds($selectedPostIds)
    {
        if (is_array($selectedPostIds)) {
            $selectedPostIds = array_map('strval', $selectedPostIds);
        } else {
            $selectedPostIds = explode(',', (string) $selectedPostIds);
        }

        $selectedPostIds = array_filter(array_map('trim', $selectedPostIds), 'strlen');

        return implode(',', $selectedPostIds);
    }

    private static function normalizeJsonField($value)
    {
        if (is_array($value)) {
            $encoded = json_encode(array_values($value));
            return $encoded === false ? '[]' : $encoded;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $value : '[]';
    }

    private static function normalizeEnum($value, array $allowedValues, $defaultValue)
    {
        $value = trim((string) $value);
        return in_array($value, $allowedValues, true) ? $value : $defaultValue;
    }

    private static function normalizeDecimal($value, $defaultValue)
    {
        if ($value === null || $value === '') {
            return $defaultValue;
        }

        if (!is_numeric($value)) {
            return $defaultValue;
        }

        return (string) $value;
    }

    private static function normalizeNullableString($value)
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }
}
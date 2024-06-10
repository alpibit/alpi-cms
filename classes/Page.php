<?php

class Page
{
    protected $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Helper function to get the content type ID for 'page'
    private function getPageContentTypeId()
    {
        $sql = "SELECT id FROM content_types WHERE name = 'page'";
        return $this->db->query($sql)->fetchColumn();
    }

    // Fetch all pages
    public function getAllPages()
    {
        $pageTypeId = $this->getPageContentTypeId();
        $sql = "SELECT * FROM contents WHERE content_type_id = :pageTypeId ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':pageTypeId', $pageTypeId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch a page by its ID
    public function getPageById($id)
    {
        $sql = "SELECT contents.title AS content_title, contents.subtitle, contents.main_image_path, 
                   contents.show_main_image, contents.is_active, contents.slug, blocks.*
            FROM contents
            LEFT JOIN blocks ON contents.id = blocks.content_id 
            WHERE contents.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $page = [];
        if (!empty($results)) {
            $pageId = $results[0]['id'];
            $page = [
                'title' => $results[0]['content_title'],
                'subtitle' => $results[0]['subtitle'],
                'main_image_path' => $results[0]['main_image_path'],
                'show_main_image' => $results[0]['show_main_image'],
                'is_active' => $results[0]['is_active'],
                'slug' => $results[0]['slug'],
                'blocks' => [],
            ];

            foreach ($results as $result) {
                if (isset($result['type'])) {
                    $page['blocks'][] = [
                        'id' => $result['id'],
                        'type' => $result['type'],
                        'title' => $result['title'],
                        'content' => $result['content'],
                        'block_data' => $result
                    ];
                }
            }
        }
        return $page;
    }



    // Fetch a page by its slug
    public function getPageBySlug($slug)
    {
        $sql = "SELECT * FROM contents WHERE slug = :slug";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePage($id, $title, $subtitle, $mainImagePath, $showMainImage, $isActive, $contentBlocks, $userId)
    {
        $sql = "UPDATE contents SET 
            title = :title, 
            subtitle = :subtitle, 
            main_image_path = :mainImagePath, 
            show_main_image = :showMainImage, 
            is_active = :isActive, 
            user_id = :userId
        WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':subtitle', $subtitle);
        $stmt->bindParam(':mainImagePath', $mainImagePath);
        $stmt->bindParam(':showMainImage', $showMainImage, PDO::PARAM_BOOL);
        $stmt->bindParam(':isActive', $isActive, PDO::PARAM_BOOL);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Delete existing blocks for this page
        $sqlDelete = "DELETE FROM blocks WHERE content_id = :id";
        $stmtDelete = $this->db->prepare($sqlDelete);
        $stmtDelete->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtDelete->execute();

        // Inserting updated blocks for this page
        foreach ($contentBlocks as $index => $block) {
            if (!empty($block['type'])) {
                $orderNum = $index + 1;
                $videoSource = in_array($block['video_source'], ['url', 'upload']) ? $block['video_source'] : 'url';
                $audioSource = in_array($block['audio_source'], ['url', 'upload']) ? $block['audio_source'] : 'url';
                $sqlBlock = "INSERT INTO blocks (
                    content_id, type, title, content, selected_post_ids, image_path, alt_text, caption, 
                    url1, cta_text1, url2, cta_text2, video_url, video_source, video_file, audio_url, audio_source, audio_file, 
                    slider_type, slider_speed, free_code_content, map_embed_code, form_shortcode, gallery_data, quotes_data, 
                    accordion_data, transition_speed, transition_effect, autoplay, pause_on_hover, infinite_loop, show_arrows, 
                    show_dots, dot_style, lazy_load, aspect_ratio, lightbox_enabled, thumbnail_path, background_type_desktop, 
                    background_type_tablet, background_type_mobile, background_image_desktop, background_image_tablet, 
                    background_image_mobile, background_video_url, background_color, background_opacity_desktop, 
                    background_opacity_tablet, background_opacity_mobile, background_style, hero_layout, overlay_color, 
                    text_color, text_size_desktop, text_size_tablet, text_size_mobile, padding_top_desktop, 
                    padding_right_desktop, padding_bottom_desktop, padding_left_desktop, padding_top_tablet, 
                    padding_right_tablet, padding_bottom_tablet, padding_left_tablet, padding_top_mobile, 
                    padding_right_mobile, padding_bottom_mobile, padding_left_mobile, margin_top_desktop, 
                    margin_right_desktop, margin_bottom_desktop, margin_left_desktop, margin_top_tablet, 
                    margin_right_tablet, margin_bottom_tablet, margin_left_tablet, margin_top_mobile, 
                    margin_right_mobile, margin_bottom_mobile, margin_left_mobile, layout1, layout2, layout3, 
                    layout4, layout5, layout6, layout7, layout8, layout9, layout10, style1, style2, style3, 
                    style4, style5, style6, style7, style8, style9, style10, responsive_class, responsive_style, 
                    border_style, border_color, border_width, animation_type, animation_duration, custom_css, 
                    custom_js, aria_label, text_size, class, metafield1, metafield2, metafield3, metafield4, 
                    metafield5, metafield6, metafield7, metafield8, metafield9, metafield10, order_num, status, 
                    start_date, end_date
                ) VALUES (
                    :contentId, :type, :title, :content, :selectedPostIds, :imagePath, :altText, :caption, 
                    :url1, :ctaText1, :url2, :ctaText2, :videoUrl, :videoSource, :videoFile, :audioUrl, :audioSource, :audioFile, 
                    :sliderType, :sliderSpeed, :freeCodeContent, :mapEmbedCode, :formShortcode, :galleryData, :quotesData, 
                    :accordionData, :transitionSpeed, :transitionEffect, :autoplay, :pauseOnHover, :infiniteLoop, :showArrows, 
                    :showDots, :dotStyle, :lazyLoad, :aspectRatio, :lightboxEnabled, :thumbnailPath, :backgroundTypeDesktop, 
                    :backgroundTypeTablet, :backgroundTypeMobile, :backgroundImageDesktop, :backgroundImageTablet, 
                    :backgroundImageMobile, :backgroundVideoUrl, :backgroundColor, :backgroundOpacityDesktop, 
                    :backgroundOpacityTablet, :backgroundOpacityMobile, :backgroundStyle, :heroLayout, :overlayColor, 
                    :textColor, :textSizeDesktop, :textSizeTablet, :textSizeMobile, :paddingTopDesktop, 
                    :paddingRightDesktop, :paddingBottomDesktop, :paddingLeftDesktop, :paddingTopTablet, 
                    :paddingRightTablet, :paddingBottomTablet, :paddingLeftTablet, :paddingTopMobile, 
                    :paddingRightMobile, :paddingBottomMobile, :paddingLeftMobile, :marginTopDesktop, 
                    :marginRightDesktop, :marginBottomDesktop, :marginLeftDesktop, :marginTopTablet, 
                    :marginRightTablet, :marginBottomTablet, :marginLeftTablet, :marginTopMobile, 
                    :marginRightMobile, :marginBottomMobile, :marginLeftMobile, :layout1, :layout2, :layout3, 
                    :layout4, :layout5, :layout6, :layout7, :layout8, :layout9, :layout10, :style1, :style2, :style3, 
                    :style4, :style5, :style6, :style7, :style8, :style9, :style10, :responsiveClass, :responsiveStyle, 
                    :borderStyle, :borderColor, :borderWidth, :animationType, :animationDuration, :customCss, 
                    :customJs, :ariaLabel, :textSize, :class, :metafield1, :metafield2, :metafield3, :metafield4, 
                    :metafield5, :metafield6, :metafield7, :metafield8, :metafield9, :metafield10, :orderNum, 'active', 
                    :startDate, :endDate
                )";

                $stmtBlock = $this->db->prepare($sqlBlock);
                $stmtBlock->bindValue(':contentId', $id, PDO::PARAM_INT);
                $stmtBlock->bindValue(':type', $block['type'], PDO::PARAM_STR);
                $stmtBlock->bindValue(':title', $block['title'], PDO::PARAM_STR);
                $stmtBlock->bindValue(':content', $block['content'], PDO::PARAM_STR);
                $stmtBlock->bindValue(':selectedPostIds', $block['selected_post_ids'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':imagePath', $block['image_path'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':altText', $block['alt_text'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':caption', $block['caption'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':url1', $block['url1'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':ctaText1', $block['cta_text1'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':url2', $block['url2'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':ctaText2', $block['cta_text2'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':videoUrl', $block['video_url'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':videoSource', $videoSource, PDO::PARAM_STR);
                $stmtBlock->bindValue(':videoFile', $block['video_file'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':audioUrl', $block['audio_url'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':audioSource', $audioSource, PDO::PARAM_STR);
                $stmtBlock->bindValue(':audioFile', $block['audio_file'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':sliderType', $block['slider_type'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':sliderSpeed', $block['slider_speed'] ?? 0, PDO::PARAM_INT);
                $stmtBlock->bindValue(':freeCodeContent', $block['free_code_content'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':mapEmbedCode', $block['map_embed_code'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':formShortcode', $block['form_shortcode'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':galleryData', $block['gallery_data'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':quotesData', $block['quotes_data'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':accordionData', $block['accordion_data'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':transitionSpeed', $block['transition_speed'] ?? 0, PDO::PARAM_INT);
                $stmtBlock->bindValue(':transitionEffect', $block['transition_effect'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':autoplay', $block['autoplay'] ?? false, PDO::PARAM_BOOL);
                $stmtBlock->bindValue(':pauseOnHover', $block['pause_on_hover'] ?? false, PDO::PARAM_BOOL);
                $stmtBlock->bindValue(':infiniteLoop', $block['infinite_loop'] ?? false, PDO::PARAM_BOOL);
                $stmtBlock->bindValue(':showArrows', $block['show_arrows'] ?? false, PDO::PARAM_BOOL);
                $stmtBlock->bindValue(':showDots', $block['show_dots'] ?? false, PDO::PARAM_BOOL);
                $stmtBlock->bindValue(':dotStyle', $block['dot_style'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':lazyLoad', $block['lazy_load'] ?? false, PDO::PARAM_BOOL);
                $stmtBlock->bindValue(':aspectRatio', $block['aspect_ratio'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':lightboxEnabled', $block['lightbox_enabled'] ?? false, PDO::PARAM_BOOL);
                $stmtBlock->bindValue(':thumbnailPath', $block['thumbnail_path'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':backgroundTypeDesktop', $block['background_type_desktop'] ?? 'image', PDO::PARAM_STR);
                $stmtBlock->bindValue(':backgroundTypeTablet', $block['background_type_tablet'] ?? 'image', PDO::PARAM_STR);
                $stmtBlock->bindValue(':backgroundTypeMobile', $block['background_type_mobile'] ?? 'image', PDO::PARAM_STR);
                $stmtBlock->bindValue(':backgroundImageDesktop', $block['background_image_desktop'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':backgroundImageTablet', $block['background_image_tablet'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':backgroundImageMobile', $block['background_image_mobile'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':backgroundVideoUrl', $block['background_video_url'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':backgroundColor', $block['background_color'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':backgroundOpacityDesktop', $block['background_opacity_desktop'] ?? 1.0, PDO::PARAM_STR);
                $stmtBlock->bindValue(':backgroundOpacityTablet', $block['background_opacity_tablet'] ?? 1.0, PDO::PARAM_STR);
                $stmtBlock->bindValue(':backgroundOpacityMobile', $block['background_opacity_mobile'] ?? 1.0, PDO::PARAM_STR);
                $stmtBlock->bindValue(':backgroundStyle', $block['background_style'] ?? 'cover', PDO::PARAM_STR);
                $stmtBlock->bindValue(':heroLayout', $block['hero_layout'] ?? 'center', PDO::PARAM_STR);
                $stmtBlock->bindValue(':overlayColor', $block['overlay_color'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':textColor', $block['text_color'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':textSizeDesktop', $block['text_size_desktop'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':textSizeTablet', $block['text_size_tablet'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':textSizeMobile', $block['text_size_mobile'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':paddingTopDesktop', $block['padding_top_desktop'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':paddingRightDesktop', $block['padding_right_desktop'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':paddingBottomDesktop', $block['padding_bottom_desktop'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':paddingLeftDesktop', $block['padding_left_desktop'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':paddingTopTablet', $block['padding_top_tablet'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':paddingRightTablet', $block['padding_right_tablet'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':paddingBottomTablet', $block['padding_bottom_tablet'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':paddingLeftTablet', $block['padding_left_tablet'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':paddingTopMobile', $block['padding_top_mobile'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':paddingRightMobile', $block['padding_right_mobile'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':paddingBottomMobile', $block['padding_bottom_mobile'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':paddingLeftMobile', $block['padding_left_mobile'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':marginTopDesktop', $block['margin_top_desktop'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':marginRightDesktop', $block['margin_right_desktop'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':marginBottomDesktop', $block['margin_bottom_desktop'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':marginLeftDesktop', $block['margin_left_desktop'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':marginTopTablet', $block['margin_top_tablet'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':marginRightTablet', $block['margin_right_tablet'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':marginBottomTablet', $block['margin_bottom_tablet'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':marginLeftTablet', $block['margin_left_tablet'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':marginTopMobile', $block['margin_top_mobile'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':marginRightMobile', $block['margin_right_mobile'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':marginBottomMobile', $block['margin_bottom_mobile'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':marginLeftMobile', $block['margin_left_mobile'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':layout1', $block['layout1'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':layout2', $block['layout2'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':layout3', $block['layout3'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':layout4', $block['layout4'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':layout5', $block['layout5'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':layout6', $block['layout6'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':layout7', $block['layout7'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':layout8', $block['layout8'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':layout9', $block['layout9'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':layout10', $block['layout10'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':style1', $block['style1'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':style2', $block['style2'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':style3', $block['style3'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':style4', $block['style4'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':style5', $block['style5'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':style6', $block['style6'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':style7', $block['style7'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':style8', $block['style8'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':style9', $block['style9'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':style10', $block['style10'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':responsiveClass', $block['responsive_class'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':responsiveStyle', $block['responsive_style'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':borderStyle', $block['border_style'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':borderColor', $block['border_color'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':borderWidth', $block['border_width'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':animationType', $block['animation_type'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':animationDuration', $block['animation_duration'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':customCss', $block['custom_css'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':customJs', $block['custom_js'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':ariaLabel', $block['aria_label'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':textSize', $block['text_size'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':class', $block['class'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':metafield1', $block['metafield1'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':metafield2', $block['metafield2'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':metafield3', $block['metafield3'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':metafield4', $block['metafield4'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':metafield5', $block['metafield5'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':metafield6', $block['metafield6'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':metafield7', $block['metafield7'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':metafield8', $block['metafield8'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':metafield9', $block['metafield9'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':metafield10', $block['metafield10'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':orderNum', $orderNum, PDO::PARAM_INT);
                $startDate = !empty($block['start_date']) ? $block['start_date'] : null;
                $endDate = !empty($block['end_date']) ? $block['end_date'] : null;
                $stmtBlock->bindValue(':startDate', $startDate, PDO::PARAM_STR);
                $stmtBlock->bindValue(':endDate', $endDate, PDO::PARAM_STR);
                $stmtBlock->execute();
            }
        }
    }








    public function addPage($title, $subtitle, $mainImagePath, $showMainImage, $isActive, $contentBlocks, $userId)
    {
        $slug = $this->generateSlug($title);
        $pageTypeId = $this->getPageContentTypeId();

        $sql = "INSERT INTO contents (content_type_id, title, subtitle, main_image_path, show_main_image, is_active, slug, user_id) 
            VALUES (:pageTypeId, :title, :subtitle, :mainImagePath, :showMainImage, :isActive, :slug, :userId)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':pageTypeId', $pageTypeId, PDO::PARAM_INT);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':subtitle', $subtitle, PDO::PARAM_STR);
        $stmt->bindParam(':mainImagePath', $mainImagePath, PDO::PARAM_STR);
        $stmt->bindParam(':showMainImage', $showMainImage, PDO::PARAM_BOOL);
        $stmt->bindParam(':isActive', $isActive, PDO::PARAM_BOOL);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $contentId = $this->db->lastInsertId();

        foreach ($contentBlocks as $index => $block) {
            $orderNum = $index + 1;
            $videoSource = in_array($block['video_source'], ['url', 'upload']) ? $block['video_source'] : 'url';
            $audioSource = in_array($block['audio_source'], ['url', 'upload']) ? $block['audio_source'] : 'url';
            $sqlBlock = "INSERT INTO blocks (
                content_id, type, title, content, selected_post_ids, image_path, alt_text, caption, 
                url1, cta_text1, url2, cta_text2, video_url, video_source, video_file, audio_url, audio_source, audio_file, 
                slider_type, slider_speed, free_code_content, map_embed_code, form_shortcode, gallery_data, quotes_data, 
                accordion_data, transition_speed, transition_effect, autoplay, pause_on_hover, infinite_loop, show_arrows, 
                show_dots, dot_style, lazy_load, aspect_ratio, lightbox_enabled, thumbnail_path, background_type_desktop, 
                background_type_tablet, background_type_mobile, background_image_desktop, background_image_tablet, 
                background_image_mobile, background_video_url, background_color, background_opacity_desktop, 
                background_opacity_tablet, background_opacity_mobile, background_style, hero_layout, overlay_color, 
                text_color, text_size_desktop, text_size_tablet, text_size_mobile, padding_top_desktop, 
                padding_right_desktop, padding_bottom_desktop, padding_left_desktop, padding_top_tablet, 
                padding_right_tablet, padding_bottom_tablet, padding_left_tablet, padding_top_mobile, 
                padding_right_mobile, padding_bottom_mobile, padding_left_mobile, margin_top_desktop, 
                margin_right_desktop, margin_bottom_desktop, margin_left_desktop, margin_top_tablet, 
                margin_right_tablet, margin_bottom_tablet, margin_left_tablet, margin_top_mobile, 
                margin_right_mobile, margin_bottom_mobile, margin_left_mobile, layout1, layout2, layout3, 
                layout4, layout5, layout6, layout7, layout8, layout9, layout10, style1, style2, style3, 
                style4, style5, style6, style7, style8, style9, style10, responsive_class, responsive_style, 
                border_style, border_color, border_width, animation_type, animation_duration, custom_css, 
                custom_js, aria_label, text_size, class, metafield1, metafield2, metafield3, metafield4, 
                metafield5, metafield6, metafield7, metafield8, metafield9, metafield10, order_num, status, 
                start_date, end_date
            ) VALUES (
                :contentId, :type, :title, :content, :selectedPostIds, :imagePath, :altText, :caption, 
                :url1, :ctaText1, :url2, :ctaText2, :videoUrl, :videoSource, :videoFile, :audioUrl, :audioSource, :audioFile, 
                :sliderType, :sliderSpeed, :freeCodeContent, :mapEmbedCode, :formShortcode, :galleryData, :quotesData, 
                :accordionData, :transitionSpeed, :transitionEffect, :autoplay, :pauseOnHover, :infiniteLoop, :showArrows, 
                :showDots, :dotStyle, :lazyLoad, :aspectRatio, :lightboxEnabled, :thumbnailPath, :backgroundTypeDesktop, 
                :backgroundTypeTablet, :backgroundTypeMobile, :backgroundImageDesktop, :backgroundImageTablet, 
                :backgroundImageMobile, :backgroundVideoUrl, :backgroundColor, :backgroundOpacityDesktop, 
                :backgroundOpacityTablet, :backgroundOpacityMobile, :backgroundStyle, :heroLayout, :overlayColor, 
                :textColor, :textSizeDesktop, :textSizeTablet, :textSizeMobile, :paddingTopDesktop, 
                :paddingRightDesktop, :paddingBottomDesktop, :paddingLeftDesktop, :paddingTopTablet, 
                :paddingRightTablet, :paddingBottomTablet, :paddingLeftTablet, :paddingTopMobile, 
                :paddingRightMobile, :paddingBottomMobile, :paddingLeftMobile, :marginTopDesktop, 
                :marginRightDesktop, :marginBottomDesktop, :marginLeftDesktop, :marginTopTablet, 
                :marginRightTablet, :marginBottomTablet, :marginLeftTablet, :marginTopMobile, 
                :marginRightMobile, :marginBottomMobile, :marginLeftMobile, :layout1, :layout2, :layout3, 
                :layout4, :layout5, :layout6, :layout7, :layout8, :layout9, :layout10, :style1, :style2, :style3, 
                :style4, :style5, :style6, :style7, :style8, :style9, :style10, :responsiveClass, :responsiveStyle, 
                :borderStyle, :borderColor, :borderWidth, :animationType, :animationDuration, :customCss, 
                :customJs, :ariaLabel, :textSize, :class, :metafield1, :metafield2, :metafield3, :metafield4, 
                :metafield5, :metafield6, :metafield7, :metafield8, :metafield9, :metafield10, :orderNum, 'active', 
                :startDate, :endDate
            )";
            $stmtBlock = $this->db->prepare($sqlBlock);
            $stmtBlock->bindValue(':contentId', $contentId, PDO::PARAM_INT);
            $stmtBlock->bindValue(':type', $block['type'], PDO::PARAM_STR);
            $stmtBlock->bindValue(':title', $block['title'], PDO::PARAM_STR);
            $stmtBlock->bindValue(':content', $block['content'], PDO::PARAM_STR);
            $stmtBlock->bindValue(':selectedPostIds', $block['selected_post_ids'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':imagePath', $block['image_path'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':altText', $block['alt_text'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':caption', $block['caption'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':url1', $block['url1'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':ctaText1', $block['cta_text1'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':url2', $block['url2'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':ctaText2', $block['cta_text2'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':videoUrl', $block['video_url'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':videoSource', $videoSource, PDO::PARAM_STR);
            $stmtBlock->bindValue(':videoFile', $block['video_file'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':audioUrl', $block['audio_url'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':audioSource', $audioSource, PDO::PARAM_STR);
            $stmtBlock->bindValue(':audioFile', $block['audio_file'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':sliderType', $block['slider_type'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':sliderSpeed', $block['slider_speed'] ?? 0, PDO::PARAM_INT);
            $stmtBlock->bindValue(':freeCodeContent', $block['free_code_content'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':mapEmbedCode', $block['map_embed_code'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':formShortcode', $block['form_shortcode'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':galleryData', $block['gallery_data'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':quotesData', $block['quotes_data'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':accordionData', $block['accordion_data'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':transitionSpeed', $block['transition_speed'] ?? 0, PDO::PARAM_INT);
            $stmtBlock->bindValue(':transitionEffect', $block['transition_effect'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':autoplay', $block['autoplay'] ?? false, PDO::PARAM_BOOL);
            $stmtBlock->bindValue(':pauseOnHover', $block['pause_on_hover'] ?? false, PDO::PARAM_BOOL);
            $stmtBlock->bindValue(':infiniteLoop', $block['infinite_loop'] ?? false, PDO::PARAM_BOOL);
            $stmtBlock->bindValue(':showArrows', $block['show_arrows'] ?? false, PDO::PARAM_BOOL);
            $stmtBlock->bindValue(':showDots', $block['show_dots'] ?? false, PDO::PARAM_BOOL);
            $stmtBlock->bindValue(':dotStyle', $block['dot_style'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':lazyLoad', $block['lazy_load'] ?? false, PDO::PARAM_BOOL);
            $stmtBlock->bindValue(':aspectRatio', $block['aspect_ratio'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':lightboxEnabled', $block['lightbox_enabled'] ?? false, PDO::PARAM_BOOL);
            $stmtBlock->bindValue(':thumbnailPath', $block['thumbnail_path'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':backgroundTypeDesktop', $block['background_type_desktop'] ?? 'image', PDO::PARAM_STR);
            $stmtBlock->bindValue(':backgroundTypeTablet', $block['background_type_tablet'] ?? 'image', PDO::PARAM_STR);
            $stmtBlock->bindValue(':backgroundTypeMobile', $block['background_type_mobile'] ?? 'image', PDO::PARAM_STR);
            $stmtBlock->bindValue(':backgroundImageDesktop', $block['background_image_desktop'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':backgroundImageTablet', $block['background_image_tablet'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':backgroundImageMobile', $block['background_image_mobile'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':backgroundVideoUrl', $block['background_video_url'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':backgroundColor', $block['background_color'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':backgroundOpacityDesktop', $block['background_opacity_desktop'] ?? 1.0, PDO::PARAM_STR);
            $stmtBlock->bindValue(':backgroundOpacityTablet', $block['background_opacity_tablet'] ?? 1.0, PDO::PARAM_STR);
            $stmtBlock->bindValue(':backgroundOpacityMobile', $block['background_opacity_mobile'] ?? 1.0, PDO::PARAM_STR);
            $stmtBlock->bindValue(':backgroundStyle', $block['background_style'] ?? 'cover', PDO::PARAM_STR);
            $stmtBlock->bindValue(':heroLayout', $block['hero_layout'] ?? 'center', PDO::PARAM_STR);
            $stmtBlock->bindValue(':overlayColor', $block['overlay_color'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':textColor', $block['text_color'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':textSizeDesktop', $block['text_size_desktop'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':textSizeTablet', $block['text_size_tablet'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':textSizeMobile', $block['text_size_mobile'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':paddingTopDesktop', $block['padding_top_desktop'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':paddingRightDesktop', $block['padding_right_desktop'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':paddingBottomDesktop', $block['padding_bottom_desktop'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':paddingLeftDesktop', $block['padding_left_desktop'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':paddingTopTablet', $block['padding_top_tablet'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':paddingRightTablet', $block['padding_right_tablet'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':paddingBottomTablet', $block['padding_bottom_tablet'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':paddingLeftTablet', $block['padding_left_tablet'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':paddingTopMobile', $block['padding_top_mobile'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':paddingRightMobile', $block['padding_right_mobile'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':paddingBottomMobile', $block['padding_bottom_mobile'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':paddingLeftMobile', $block['padding_left_mobile'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':marginTopDesktop', $block['margin_top_desktop'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':marginRightDesktop', $block['margin_right_desktop'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':marginBottomDesktop', $block['margin_bottom_desktop'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':marginLeftDesktop', $block['margin_left_desktop'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':marginTopTablet', $block['margin_top_tablet'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':marginRightTablet', $block['margin_right_tablet'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':marginBottomTablet', $block['margin_bottom_tablet'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':marginLeftTablet', $block['margin_left_tablet'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':marginTopMobile', $block['margin_top_mobile'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':marginRightMobile', $block['margin_right_mobile'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':marginBottomMobile', $block['margin_bottom_mobile'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':marginLeftMobile', $block['margin_left_mobile'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':layout1', $block['layout1'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':layout2', $block['layout2'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':layout3', $block['layout3'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':layout4', $block['layout4'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':layout5', $block['layout5'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':layout6', $block['layout6'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':layout7', $block['layout7'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':layout8', $block['layout8'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':layout9', $block['layout9'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':layout10', $block['layout10'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':style1', $block['style1'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':style2', $block['style2'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':style3', $block['style3'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':style4', $block['style4'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':style5', $block['style5'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':style6', $block['style6'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':style7', $block['style7'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':style8', $block['style8'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':style9', $block['style9'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':style10', $block['style10'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':responsiveClass', $block['responsive_class'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':responsiveStyle', $block['responsive_style'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':borderStyle', $block['border_style'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':borderColor', $block['border_color'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':borderWidth', $block['border_width'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':animationType', $block['animation_type'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':animationDuration', $block['animation_duration'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':customCss', $block['custom_css'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':customJs', $block['custom_js'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':ariaLabel', $block['aria_label'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':textSize', $block['text_size'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':class', $block['class'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':metafield1', $block['metafield1'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':metafield2', $block['metafield2'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':metafield3', $block['metafield3'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':metafield4', $block['metafield4'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':metafield5', $block['metafield5'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':metafield6', $block['metafield6'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':metafield7', $block['metafield7'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':metafield8', $block['metafield8'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':metafield9', $block['metafield9'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':metafield10', $block['metafield10'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':orderNum', $orderNum, PDO::PARAM_INT);
            $startDate = !empty($block['start_date']) ? $block['start_date'] : null;
            $endDate = !empty($block['end_date']) ? $block['end_date'] : null;
            $stmtBlock->bindValue(':startDate', $startDate, PDO::PARAM_STR);
            $stmtBlock->bindValue(':endDate', $endDate, PDO::PARAM_STR);
            $stmtBlock->execute();
        }
    }

    public function deletePage($id)
    {
        if ($id == 1) {
            return false;
        }
        $sqlBlocks = "DELETE FROM blocks WHERE content_id = :id";
        $stmtBlocks = $this->db->prepare($sqlBlocks);
        $stmtBlocks->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtBlocks->execute();

        $sql = "DELETE FROM contents WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function generateSlug($title)
    {
        $title = trim($title);
        $slug = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $title));
        $originalSlug = $slug;
        $i = 1;

        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $i;
            $i++;
        }

        return $slug;
    }

    private function slugExists($slug)
    {
        $postTypeId = $this->getPageContentTypeId();
        $sql = "SELECT COUNT(*) FROM contents WHERE slug = :slug AND content_type_id = :postTypeId";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->bindParam(':postTypeId', $postTypeId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function countPages()
    {
        $sql = "SELECT COUNT(*) FROM contents WHERE content_type_id = (SELECT id FROM content_types WHERE name = 'page')";
        return $this->db->query($sql)->fetchColumn();
    }
}

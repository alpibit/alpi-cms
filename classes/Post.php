<?php

class Post
{
    protected $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Helper function to get the content type ID for 'post'
    private function getPostContentTypeId()
    {
        $sql = "SELECT id FROM content_types WHERE name = 'post'";
        return $this->db->query($sql)->fetchColumn();
    }

    // Fetch the latest 10 posts
    public function getLatestPosts()
    {
        $sql = "SELECT contents.id, contents.title, contents.slug, blocks.content, blocks.type 
                FROM contents 
                INNER JOIN blocks ON contents.id = blocks.content_id 
                WHERE contents.content_type_id = (SELECT id FROM content_types WHERE name = 'post') 
                ORDER BY contents.created_at DESC, blocks.order_num ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $posts = [];
        foreach ($results as $result) {
            $postId = $result['id'];
            if (!isset($posts[$postId])) {
                $posts[$postId] = [
                    'title' => $result['title'],
                    'slug' => $result['slug'],
                    'blocks' => [],
                ];
            }
            $posts[$postId]['blocks'][] = [
                'type' => $result['type'],
                'content' => $result['content'],
            ];
        }
        return array_values($posts);
    }

    // Fetch all posts
    public function getAllPosts()
    {
        $postTypeId = $this->getPostContentTypeId();
        $sql = "SELECT * FROM contents WHERE content_type_id = :postTypeId ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':postTypeId', $postTypeId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPostDetailsById($id)
    {
        $sql = "SELECT * FROM contents WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Add a new post
    public function addPost($title, $subtitle, $mainImagePath, $showMainImage, $isActive, $contentBlocks, $userId, $categoryId)
    {
        $slug = $this->generateSlug($title);
        $postTypeId = $this->getPostContentTypeId();
        $categoryId = (int) $categoryId;

        $sql = "INSERT INTO contents (content_type_id, title, subtitle, main_image_path, show_main_image, is_active, slug, user_id, category_id) 
        VALUES (:postTypeId, :title, :subtitle, :mainImagePath, :showMainImage, :isActive, :slug, :userId, :categoryId)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':postTypeId', $postTypeId, PDO::PARAM_INT);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':subtitle', $subtitle, PDO::PARAM_STR);
        $stmt->bindParam(':mainImagePath', $mainImagePath, PDO::PARAM_STR);
        $stmt->bindParam(':showMainImage', $showMainImage, PDO::PARAM_BOOL);
        $stmt->bindParam(':isActive', $isActive, PDO::PARAM_BOOL);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
        $stmt->execute();

        $contentId = $this->db->lastInsertId();

        // Inserting blocks related to this post
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
                    padding_bottom_desktop, padding_top_tablet, 
                    padding_bottom_tablet, padding_top_mobile, 
                    padding_bottom_mobile, margin_top_desktop, 
                    margin_bottom_desktop, margin_top_tablet, 
                    margin_bottom_tablet, margin_top_mobile, 
                    margin_bottom_mobile, layout1, layout2, layout3, 
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
                    :paddingBottomDesktop, :paddingTopTablet, 
                    :paddingBottomTablet, :paddingTopMobile, 
                    :paddingBottomMobile, :marginTopDesktop, 
                    :marginBottomDesktop, :marginTopTablet, 
                    :marginBottomTablet, :marginTopMobile, 
                    :marginBottomMobile, :layout1, :layout2, :layout3, 
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
            $stmtBlock->bindValue(':paddingBottomDesktop', $block['padding_bottom_desktop'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':paddingTopTablet', $block['padding_top_tablet'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':paddingBottomTablet', $block['padding_bottom_tablet'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':paddingTopMobile', $block['padding_top_mobile'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':paddingBottomMobile', $block['padding_bottom_mobile'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':marginTopDesktop', $block['margin_top_desktop'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':marginBottomDesktop', $block['margin_bottom_desktop'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':marginTopTablet', $block['margin_top_tablet'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':marginBottomTablet', $block['margin_bottom_tablet'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':marginTopMobile', $block['margin_top_mobile'] ?? '', PDO::PARAM_STR);
            $stmtBlock->bindValue(':marginBottomMobile', $block['margin_bottom_mobile'] ?? '', PDO::PARAM_STR);
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
        $postTypeId = $this->getPostContentTypeId();
        $sql = "SELECT COUNT(*) FROM contents WHERE slug = :slug AND content_type_id = :postTypeId";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->bindParam(':postTypeId', $postTypeId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    // Update an existing post by ID
    public function updatePost($id, $title, $contentBlocks, $slug, $userId, $subtitle, $mainImagePath, $showMainImage, $isActive, $categoryId)
    {
        $sql = "UPDATE contents SET 
        title = :title, 
        subtitle = :subtitle, 
        main_image_path = :mainImagePath, 
        show_main_image = :showMainImage, 
        is_active = :isActive, 
        user_id = :userId,
        category_id = :categoryId
    WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':title' => $title,
            ':subtitle' => $subtitle,
            ':mainImagePath' => $mainImagePath,
            ':showMainImage' => $showMainImage,
            ':isActive' => $isActive,
            ':userId' => $userId,
            ':categoryId' => $categoryId,
            ':id' => $id
        ]);

        // Delete existing blocks for this post
        $sqlDelete = "DELETE FROM blocks WHERE content_id = :id";
        $stmtDelete = $this->db->prepare($sqlDelete);
        $stmtDelete->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtDelete->execute();

        // Update the slug if it's empty
        $sqlSlug = "SELECT slug FROM contents WHERE id = :id";
        $stmtSlug = $this->db->prepare($sqlSlug);
        $stmtSlug->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtSlug->execute();
        $currentSlug = $stmtSlug->fetchColumn();
        if (empty($currentSlug)) {
            $sqlUpdateSlug = "UPDATE contents SET slug = :slug WHERE id = :id";
            $stmtUpdateSlug = $this->db->prepare($sqlUpdateSlug);
            $stmtUpdateSlug->bindParam(':slug', $slug, PDO::PARAM_STR);
            $stmtUpdateSlug->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtUpdateSlug->execute();
        }


        // Inserting updated blocks for this post
        if (!empty($contentBlocks)) {
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
                    padding_bottom_desktop, padding_top_tablet, 
                    padding_bottom_tablet, padding_top_mobile, 
                    padding_bottom_mobile, margin_top_desktop, 
                    margin_bottom_desktop, margin_top_tablet, 
                    margin_bottom_tablet, margin_top_mobile, 
                    margin_bottom_mobile, layout1, layout2, layout3, 
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
                    :paddingBottomDesktop, :paddingTopTablet, 
                    :paddingBottomTablet, :paddingTopMobile, 
                    :paddingBottomMobile, :marginTopDesktop, 
                    :marginBottomDesktop, :marginTopTablet, 
                    :marginBottomTablet, :marginTopMobile, 
                    :marginBottomMobile, :layout1, :layout2, :layout3, 
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
                $stmtBlock->bindValue(':backgroundOpacityTablet', $block['background_opacity_tablet'] ?? 1.0, PDO::PARAM_STR);
                $stmtBlock->bindValue(':backgroundOpacityMobile', $block['background_opacity_mobile'] ?? 1.0, PDO::PARAM_STR);
                $stmtBlock->bindValue(':backgroundStyle', $block['background_style'] ?? 'cover', PDO::PARAM_STR);
                $stmtBlock->bindValue(':heroLayout', $block['hero_layout'] ?? 'center', PDO::PARAM_STR);
                $stmtBlock->bindValue(':overlayColor', $block['overlay_color'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':textColor', $block['text_color'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':textSizeDesktop', $block['text_size_desktop'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':orderNum', $orderNum, PDO::PARAM_INT);
                $stmtBlock->bindValue(':textSizeTablet', $block['text_size_tablet'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':textSizeMobile', $block['text_size_mobile'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':paddingTopDesktop', $block['padding_top_desktop'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':paddingBottomDesktop', $block['padding_bottom_desktop'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':paddingTopTablet', $block['padding_top_tablet'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':paddingBottomTablet', $block['padding_bottom_tablet'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':paddingTopMobile', $block['padding_top_mobile'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':paddingBottomMobile', $block['padding_bottom_mobile'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':marginTopDesktop', $block['margin_top_desktop'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':marginBottomDesktop', $block['margin_bottom_desktop'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':marginTopTablet', $block['margin_top_tablet'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':marginBottomTablet', $block['margin_bottom_tablet'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':marginTopMobile', $block['margin_top_mobile'] ?? '', PDO::PARAM_STR);
                $stmtBlock->bindValue(':marginBottomMobile', $block['margin_bottom_mobile'] ?? '', PDO::PARAM_STR);
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
                $startDate = !empty($block['start_date']) ? $block['start_date'] : null;
                $endDate = !empty($block['end_date']) ? $block['end_date'] : null;
                $stmtBlock->bindValue(':startDate', $startDate, PDO::PARAM_STR);
                $stmtBlock->bindValue(':endDate', $endDate, PDO::PARAM_STR);
                $stmtBlock->execute();
            }
        }
    }




    public function getBlocksByPostId($postId)
    {
        $sql = "SELECT * FROM blocks WHERE content_id = :postId ORDER BY order_num ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':postId', $postId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch a post by its ID
    public function getPostById($id)
    {
        $sql = "SELECT contents.title AS content_title, contents.subtitle, 
            contents.main_image_path, contents.show_main_image, 
            contents.is_active, contents.slug, contents.category_id,
            categories.name AS category_name, categories.slug AS category_slug,
            blocks.* 
            FROM contents 
            LEFT JOIN blocks ON contents.id = blocks.content_id
            LEFT JOIN categories ON contents.category_id = categories.id
            WHERE contents.id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $posts = [];
        if (!empty($results)) {
            $postId = $results[0]['id'];
            $posts[$postId] = [
                'title' => $results[0]['content_title'],
                'subtitle' => $results[0]['subtitle'],
                'main_image_path' => $results[0]['main_image_path'],
                'show_main_image' => $results[0]['show_main_image'],
                'is_active' => $results[0]['is_active'],
                'slug' => $results[0]['slug'],
                'category_id' => $results[0]['category_id'],
                'category_name' => $results[0]['category_name'],
                'category_slug' => $results[0]['category_slug'],
                'blocks' => [],
            ];

            foreach ($results as $result) {
                $posts[$postId]['blocks'][] = [
                    'type' => $result['type'],
                    'content' => $result['content'],
                    'block_data' => $result
                ];
            }
        }

        return array_values($posts);
    }


    // Fetch a post by its slug
    public function getPostBySlug($slug)
    {
        $sql = "SELECT * FROM contents WHERE slug = :slug";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Fetch a post by its category slug and post slug
    public function getPostByCategoryAndSlug($categorySlug, $postSlug)
    {
        $sql = "SELECT contents.*, categories.name AS category_name, categories.slug AS category_slug 
            FROM contents 
            INNER JOIN categories ON contents.category_id = categories.id 
            WHERE categories.slug = :categorySlug AND contents.slug = :postSlug";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':categorySlug', $categorySlug, PDO::PARAM_STR);
        $stmt->bindParam(':postSlug', $postSlug, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Delete a post by its ID
    public function deletePost($id)
    {
        // Delete associated blocks
        $sqlBlocks = "DELETE FROM blocks WHERE content_id = :id";
        $stmtBlocks = $this->db->prepare($sqlBlocks);
        $stmtBlocks->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtBlocks->execute();

        // Delete the post
        $sql = "DELETE FROM contents WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getPostsByCategoryId($categoryId)
    {
        $sql = "SELECT * FROM contents 
                WHERE category_id = :categoryId AND content_type_id = (SELECT id FROM content_types WHERE name = 'post')
                ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategorySlugByPostId($postId)
    {
        $sql = "SELECT categories.slug FROM contents
            JOIN categories ON contents.category_id = categories.id
            WHERE contents.id = :postId";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':postId', $postId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['slug'] ?? null;
    }

    public function countPosts()
    {
        $sql = "SELECT COUNT(*) FROM contents WHERE content_type_id = (SELECT id FROM content_types WHERE name = 'post')";
        return $this->db->query($sql)->fetchColumn();
    }
}

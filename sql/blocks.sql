CREATE TABLE blocks (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    content_id INT(11) NOT NULL,
    type ENUM(
        'text',
        'image_text',
        'image',
        'cta',
        'post_picker',
        'video',
        'slider_gallery',
        'quote',
        'accordion',
        'audio',
        'free_code',
        'map',
        'form',
        'hero'
    ) NOT NULL,
    title VARCHAR(255),
    content MEDIUMTEXT,
    selected_post_ids TEXT,
    image_path TEXT,
    alt_text TEXT,
    caption TEXT,
    url1 TEXT,
    cta_text1 TEXT,
    url2 TEXT,
    cta_text2 TEXT,
    video_url TEXT,
    video_source ENUM('url', 'upload') DEFAULT 'url',
    video_file TEXT,
    audio_url TEXT,
    audio_source ENUM('url', 'upload') DEFAULT 'url',
    audio_file TEXT,
    slider_type ENUM('image', 'quote'),
    slider_speed INT(11),
    free_code_content MEDIUMTEXT,
    map_embed_code TEXT,
    form_shortcode TEXT,
    gallery_data MEDIUMTEXT,
    quotes_data MEDIUMTEXT,
    accordion_data MEDIUMTEXT,
    transition_speed INT,
    transition_effect ENUM('slide', 'fade'),
    autoplay BOOLEAN,
    pause_on_hover BOOLEAN,
    infinite_loop BOOLEAN,
    show_arrows BOOLEAN,
    show_dots BOOLEAN,
    dot_style ENUM('classic', 'thumbnail'),
    lazy_load BOOLEAN,
    aspect_ratio ENUM('16:9', '4:3', '1:1', 'custom'),
    lightbox_enabled BOOLEAN,
    thumbnail_path TEXT,
    background_type_desktop ENUM('image', 'video', 'color') DEFAULT 'image',
    background_type_tablet ENUM('image', 'video', 'color') DEFAULT 'image',
    background_type_mobile ENUM('image', 'video', 'color') DEFAULT 'image',
    background_image_desktop TEXT,
    background_image_tablet TEXT,
    background_image_mobile TEXT,
    background_video_url TEXT,
    background_color TEXT,
    background_opacity_desktop DECIMAL(3, 2),
    background_opacity_tablet DECIMAL(3, 2),
    background_opacity_mobile DECIMAL(3, 2),
    background_style ENUM('cover', 'contain', 'repeat', 'no-repeat') DEFAULT 'cover',
    hero_layout ENUM('left', 'center', 'right') DEFAULT 'center',
    overlay_color TEXT,
    text_color TEXT,
    text_size_desktop TEXT,
    text_size_tablet TEXT,
    text_size_mobile TEXT,
    padding_top_desktop TEXT,
    padding_bottom_desktop TEXT,
    padding_top_tablet TEXT,
    padding_bottom_tablet TEXT,
    padding_top_mobile TEXT,
    padding_bottom_mobile TEXT,
    margin_top_desktop TEXT,
    margin_bottom_desktop TEXT,
    margin_top_tablet TEXT,
    margin_bottom_tablet TEXT,
    margin_top_mobile TEXT,
    margin_bottom_mobile TEXT,
    layout1 TEXT,
    layout2 TEXT,
    layout3 TEXT,
    layout4 TEXT,
    layout5 TEXT,
    layout6 TEXT,
    layout7 TEXT,
    layout8 TEXT,
    layout9 TEXT,
    layout10 TEXT,
    style1 TEXT,
    style2 TEXT,
    style3 TEXT,
    style4 TEXT,
    style5 TEXT,
    style6 TEXT,
    style7 TEXT,
    style8 TEXT,
    style9 TEXT,
    style10 TEXT,
    responsive_class TEXT,
    responsive_style TEXT,
    border_style TEXT,
    border_color TEXT,
    border_width TEXT,
    animation_type TEXT,
    animation_duration TEXT,
    custom_css TEXT,
    custom_js TEXT,
    aria_label TEXT,
    text_size TEXT,
    class TEXT,
    metafield1 TEXT,
    metafield2 TEXT,
    metafield3 TEXT,
    metafield4 TEXT,
    metafield5 TEXT,
    metafield6 TEXT,
    metafield7 TEXT,
    metafield8 TEXT,
    metafield9 TEXT,
    metafield10 TEXT,
    order_num INT(11) NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    start_date DATETIME,
    end_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (content_id) REFERENCES contents(id)
);
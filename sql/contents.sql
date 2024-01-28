CREATE TABLE contents (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    content_type_id INT(11) NOT NULL,
    user_id INT(11),
    title VARCHAR(255) NOT NULL,
    subtitle VARCHAR(255),
    main_image_path VARCHAR(255),
    show_main_image BOOLEAN DEFAULT true,
    is_active BOOLEAN DEFAULT true,
    slug VARCHAR(255) UNIQUE NOT NULL,
    category_id INT(11) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (content_type_id) REFERENCES content_types(id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE settings (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(255) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL
);

INSERT INTO
    settings (setting_key, setting_value)
VALUES
    ('site_title', ''),
    ('site_description', ''),
    ('site_logo', ''),
    ('site_favicon', ''),
    ('default_language', 'en'),
    ('timezone', 'UTC'),
    ('date_format', 'Y-m-d'),
    ('time_format', 'H:i:s'),
    ('posts_per_page', '10'),
    ('google_analytics_code', ''),
    ('custom_css', ''),
    ('maintenance_mode', 'false'),
    ('header_scripts', ''),
    ('footer_scripts', ''),
    ('default_post_thumbnail', ''),
    ('pagination_type', 'numbered'),
    ('email_from', ''),
    ('email_smtp_host', ''),
    ('email_smtp_port', ''),
    ('email_smtp_username', ''),
    ('email_smtp_password', ''),
    ('email_smtp_encryption', '');
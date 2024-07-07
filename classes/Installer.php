<?php
require 'config/autoload.php';

class Installer
{
    private $conn;

    public function install($adminUser, $adminPass, $adminEmail, $host, $name, $user, $pass, $websiteUrl, $smtpHost = '', $smtpPort = '', $smtpUser = '', $smtpPass = '', $smtpEncryption = '')
    {
        $this->writeConfigFile($host, $name, $user, $pass);

        require_once 'config/database.php';

        $db = new Database();
        $this->conn = $db->connect();

        $this->setupTables();
        $this->insertDefaultContent();
        $this->setDefaultSettings();
        $this->updateEmailSettings($adminEmail, $smtpHost, $smtpPort, $smtpUser, $smtpPass, $smtpEncryption);
        $this->createAdminUser($adminUser, $adminPass, $adminEmail);
        $this->flagAsInstalledIfNotSet();
        $this->sendWelcomeEmail($adminEmail, $websiteUrl, $smtpHost, $smtpPort, $smtpUser, $smtpPass, $smtpEncryption);
    }

    private function writeConfigFile($host, $name, $user, $pass)
    {
        $configContent = "<?php\n\n";
        $configContent .= "define('DB_HOST', '{$host}');\n";
        $configContent .= "define('DB_NAME', '{$name}');\n";
        $configContent .= "define('DB_USER', '{$user}');\n";
        $configContent .= "define('DB_PASS', '{$pass}');\n\n";
        $configContent .= "?>";

        file_put_contents('config/database.php', $configContent);
    }

    private function setupTables()
    {
        $sqlFiles = ['sql/content_types.sql', 'sql/categories.sql', 'sql/contents.sql', 'sql/settings.sql', 'sql/users.sql', 'sql/blocks.sql'];
        foreach ($sqlFiles as $file) {
            $this->executeSQLFromFile($file);
        }

        $this->conn->exec("INSERT INTO content_types (name) VALUES ('post'), ('page');");
        $this->conn->exec("INSERT INTO categories (name, slug) VALUES ('General', 'general');");
    }

    private function executeSQLFromFile($filePath)
    {
        $query = file_get_contents($filePath);
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
    }

    private function insertDefaultContent()
    {
        $defaultPages = [
            ['title' => 'Home', 'content' => 'Welcome to the Home page'],
            ['title' => 'About', 'content' => 'Information about us'],
            ['title' => 'Contact', 'content' => 'Contact us here'],
            ['title' => 'Blog', 'content' => 'Latest news and updates'],
            ['title' => 'FAQ', 'content' => 'Frequently Asked Questions'],
            ['title' => 'Privacy Policy', 'content' => 'Our Privacy Policy'],
            ['title' => 'Terms of Service', 'content' => 'Terms and Conditions'],
            ['title' => 'Portfolio', 'content' => 'Showcase of our work'],
            ['title' => 'Services', 'content' => 'Our Services']
        ];

        foreach ($defaultPages as $page) {
            $this->insertSampleContent('page', $page);
        }

        $samplePost = [
            'title' => 'Welcome to Your New CMS!',
            'content' => 'Congratulations on successfully installing your new CMS. This is a sample post. You can edit or delete it to start creating your own content!'
        ];

        $categoryId = $this->conn->query("SELECT id FROM categories WHERE slug = 'general'")->fetchColumn();
        $samplePostId = $this->insertSampleContent('post', $samplePost, $categoryId);

        $sampleBlocks = [
            [
                'type' => 'text',
                'title' => 'Sample Text Block',
                'content' => 'This is a text block content.',
                'image_path' => '',
                'alt_text' => '',
                'caption' => '',
                'url1' => '',
                'class' => '',
                'metafield1' => null,
                'metafield2' => null,
                'metafield3' => null,
                'cta_text1' => '',
                'order_num' => 1,
                'status' => 'active',
                'start_date' => null,
                'end_date' => null,
            ],
            [
                'type' => 'image_text',
                'title' => 'Sample Image Text Block',
                'content' => 'This is the text content for the image-text block.',
                'image_path' => 'path_to_sample_image.jpg',
                'alt_text' => 'Sample Image',
                'caption' => 'This is a caption.',
                'url1' => '',
                'class' => '',
                'metafield1' => null,
                'metafield2' => null,
                'metafield3' => null,
                'cta_text1' => 'Click here',
                'order_num' => 2,
                'status' => 'active',
                'start_date' => null,
                'end_date' => null,
            ]
        ];

        foreach ($sampleBlocks as $block) {
            $this->insertSampleBlock($samplePostId, $block);
        }
    }

    private function insertSampleContent($contentType, $content, $categoryId = null)
    {
        $sqlContentType = "SELECT id FROM content_types WHERE name = :contentType";
        $stmt = $this->conn->prepare($sqlContentType);
        $stmt->bindParam(':contentType', $contentType);
        $stmt->execute();
        $contentTypeId = $stmt->fetchColumn();

        $slug = $this->generateSlug($content['title']);
        $sql = "INSERT INTO contents (content_type_id, title, slug, category_id) VALUES (:contentTypeId, :title, :slug, :categoryId)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':contentTypeId', $contentTypeId);
        $stmt->bindParam(':title', $content['title']);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':categoryId', $categoryId);
        $stmt->execute();
        return $this->conn->lastInsertId();
    }

    private function generateSlug($string)
    {
        $string = trim($string);
        $slug = strtolower($string);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }

    private function insertSampleBlock($contentId, $block)
    {
        $sql = "INSERT INTO blocks (
            content_id, type, title, content,
            image_path, alt_text, caption, url1,
            class, metafield1, metafield2, metafield3, cta_text1,
            order_num, status, start_date, end_date
        ) VALUES (
            :contentId, :type, :title, :content,
            :image_path, :alt_text, :caption, :url1,
            :class, :metafield1, :metafield2, :metafield3, :cta_text1,
            :order_num, :status, :start_date, :end_date
        )";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':contentId', $contentId);
        $stmt->bindParam(':type', $block['type']);
        $stmt->bindParam(':title', $block['title']);
        $stmt->bindParam(':content', $block['content']);
        $stmt->bindParam(':image_path', $block['image_path']);
        $stmt->bindParam(':alt_text', $block['alt_text']);
        $stmt->bindParam(':caption', $block['caption']);
        $stmt->bindParam(':url1', $block['url1']);
        $stmt->bindParam(':class', $block['class']);
        $stmt->bindParam(':metafield1', $block['metafield1']);
        $stmt->bindParam(':metafield2', $block['metafield2']);
        $stmt->bindParam(':metafield3', $block['metafield3']);
        $stmt->bindParam(':cta_text1', $block['cta_text1']);
        $stmt->bindParam(':order_num', $block['order_num']);
        $stmt->bindParam(':status', $block['status']);
        $stmt->bindParam(':start_date', $block['start_date']);
        $stmt->bindParam(':end_date', $block['end_date']);
        $stmt->execute();
    }

    private function setDefaultSettings()
    {
        $defaultSettings = [
            ['site_title', 'My New Site'],
            ['site_description', ''],
            ['site_logo', ''],
            ['site_favicon', ''],
            ['default_language', 'en'],
            ['timezone', 'UTC'],
            ['date_format', 'Y-m-d'],
            ['time_format', 'H:i:s'],
            ['posts_per_page', '10'],
            ['social_media_links', ''],
            ['google_analytics_code', ''],
            ['custom_css', ''],
            ['custom_js', ''],
            ['maintenance_mode', 'false'],
            ['header_scripts', ''],
            ['footer_scripts', ''],
            ['default_post_thumbnail', ''],
            ['pagination_type', 'numbered'],
            ['footer_text', 'My Site powered by AlpiCMS'],
            ['header_logo', 'path_to_logo_image.jpg'],
            ['email_from', ''],
            ['email_smtp_host', ''],
            ['email_smtp_port', ''],
            ['email_smtp_username', ''],
            ['email_smtp_password', ''],
            ['email_smtp_encryption', ''],
            ['installed', 'true']
        ];

        foreach ($defaultSettings as $setting) {
            $settingKey = $setting[0];
            $settingValue = $setting[1];

            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = :settingKey");
            $stmt->bindParam(':settingKey', $settingKey);
            $stmt->execute();
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                $stmt = $this->conn->prepare("UPDATE settings SET setting_value = :settingValue WHERE setting_key = :settingKey");
                $stmt->bindParam(':settingValue', $settingValue);
                $stmt->bindParam(':settingKey', $settingKey);
                $stmt->execute();
            } else {
                $stmt = $this->conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (:settingKey, :settingValue)");
                $stmt->bindParam(':settingKey', $settingKey);
                $stmt->bindParam(':settingValue', $settingValue);
                $stmt->execute();
            }
        }
    }

    private function updateEmailSettings($adminEmail, $smtpHost, $smtpPort, $smtpUser, $smtpPass, $smtpEncryption)
    {
        $this->conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'email_from'")->execute([$adminEmail]);
        $this->conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'email_smtp_host'")->execute([$smtpHost]);
        $this->conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'email_smtp_port'")->execute([$smtpPort]);
        $this->conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'email_smtp_username'")->execute([$smtpUser]);
        $this->conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'email_smtp_password'")->execute([$smtpPass]);
        $this->conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'email_smtp_encryption'")->execute([$smtpEncryption]);
    }

    private function createAdminUser($username, $password, $email)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password, email, role) VALUES (:username, :hashedPassword, :email, 'admin')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':hashedPassword', $hashedPassword);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
    }

    private function flagAsInstalledIfNotSet()
    {
        $sql = "SELECT COUNT(*) FROM settings WHERE setting_key = 'installed'";
        $stmt = $this->conn->query($sql);
        $count = $stmt->fetchColumn();

        if ($count === 0) {
            $sql = "INSERT INTO settings (setting_key, setting_value) VALUES ('installed', 'true')";
            $this->conn->exec($sql);
        }
    }

    private function sendWelcomeEmail($email, $websiteUrl, $smtpHost, $smtpPort, $smtpUser, $smtpPass, $smtpEncryption)
    {
        $subject = 'Welcome to AlpiCMS';
        $message = "Dear Admin,\n\nThank you for installing AlpiCMS. Your new CMS is now ready to use.\n\nBest regards,\nThe AlpiCMS Team";

        $fromEmail = $this->generateFromEmail($websiteUrl);

        $mail = new Email();
        $mail->setTo($email);
        $mail->setFrom($fromEmail);
        $mail->setSubject($subject);
        $mail->setMessage($message);
        $mail->setAltMessage(strip_tags($message));

        if ($smtpHost) {
            $mail->setSmtpSettings($smtpHost, $smtpPort, $smtpUser, $smtpPass, $smtpEncryption);
        }

        $mail->send();
    }

    private function generateFromEmail($websiteUrl)
    {
        $urlParts = parse_url($websiteUrl);
        $domain = $urlParts['host'];

        $domain = preg_replace('/^www\./', '', $domain);

        $fromEmail = "info@{$domain}";

        return $fromEmail;
    }
}

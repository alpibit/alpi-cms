<?php
class Installer
{
    private $conn;

    public function install($adminUser, $adminPass, $host, $name, $user, $pass)
    {
        $this->writeConfigFile($host, $name, $user, $pass);

        require_once 'config/database.php';

        $db = new Database();
        $this->conn = $db->connect();

        $this->setupTables();
        $this->insertDefaultContent();
        $this->setDefaultSettings();
        $this->createAdminUser($adminUser, $adminPass);
        $this->flagAsInstalledIfNotSet();
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
        $sql = "INSERT INTO settings (setting_key, setting_value) VALUES 
        ('site_name', 'My New Site'),
        ('footer_text', 'My Site powered by AlpiCMS'),
        ('header_logo', 'path_to_logo_image.jpg'),
        ('installed', 'true')";
        $this->conn->exec($sql);
    }

    private function createAdminUser($username, $password)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password, role) VALUES (:username, :hashedPassword, 'admin')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':hashedPassword', $hashedPassword);
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
}

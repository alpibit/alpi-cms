<?php

function executeSQLFromFile($conn, $filePath)
{
    $query = file_get_contents($filePath);
    $stmt = $conn->prepare($query);
    $stmt->execute();
}

function setupTables($conn)
{

    $sqlFiles = ['sql/content_types.sql', 'sql/categories.sql', 'sql/contents.sql', 'sql/settings.sql', 'sql/users.sql', 'sql/blocks.sql'];
    foreach ($sqlFiles as $file) {
        executeSQLFromFile($conn, $file);
    }

    // Insert default content types
    $conn->exec("INSERT INTO content_types (name) VALUES ('post'), ('page');");

    // Insert a sample category
    $conn->exec("INSERT INTO categories (name, slug) VALUES ('General', 'general');");
}

function generateSlug($string)
{
    $string = trim($string);
    $slug = strtolower($string);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

function insertSampleContent($conn, $contentType, $content, $categoryId = null)
{
    // Get the ID of the content type
    $sqlContentType = "SELECT id FROM content_types WHERE name = :contentType";
    $stmt = $conn->prepare($sqlContentType);
    $stmt->bindParam(':contentType', $contentType);
    $stmt->execute();
    $contentTypeId = $stmt->fetchColumn();

    $slug = generateSlug($content['title']);
    $sql = "INSERT INTO contents (content_type_id, title, slug, category_id) VALUES (:contentTypeId, :title, :slug, :categoryId)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':contentTypeId', $contentTypeId);
    $stmt->bindParam(':title', $content['title']);
    $stmt->bindParam(':slug', $slug);
    $stmt->bindParam(':categoryId', $categoryId);
    $stmt->execute();
    return $conn->lastInsertId();
}

function insertSampleBlock($conn, $contentId, $block)
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

    $stmt = $conn->prepare($sql);
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

function insertDefaultPages($conn)
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
        insertSampleContent($conn, 'page', $page);
    }
}

function setDefaultSettings($conn)
{
    $sql = "INSERT INTO settings (setting_key, setting_value) VALUES 
    ('site_name', 'My New Site'),
    ('footer_text', 'My Site powered by AlpiCMS'),
    ('header_logo', 'path_to_logo_image.jpg')"; // !!! Need to change this
    $conn->exec($sql);
}

function flagAsInstalled($conn)
{
    $sql = "INSERT INTO settings (setting_key, setting_value) VALUES ('installed', 'true')";
    $conn->exec($sql);
}

function createAdminUser($conn, $username, $password)
{
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password, role) VALUES (:username, :hashedPassword, 'admin')";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':hashedPassword', $hashedPassword);
    $stmt->execute();
}

// Check if config/database.php exists
if (file_exists('config/database.php')) {
    die("The CMS is already installed. For security reasons, please delete or rename the install.php file.");
}

// If the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $host = $_POST['db_host'];
    $name = $_POST['db_name'];
    $user = $_POST['db_user'];
    $pass = $_POST['db_pass'];
    $admin_user = trim($_POST['admin_user']);
    $admin_pass = trim($_POST['admin_pass']);

    if (empty($host) || empty($name) || empty($user) || empty($admin_user) || empty($admin_pass)) {
        echo "Please fill in all the fields!";
        exit;
    }

    if (strlen($admin_user) < 5 || strlen($admin_pass) < 5) {
        echo "Admin username and password should be at least 5 characters long!";
        exit;
    }

    $configContent = "<?php\n\n";
    $configContent .= "define('DB_HOST', '{$host}');\n";
    $configContent .= "define('DB_NAME', '{$name}');\n";
    $configContent .= "define('DB_USER', '{$user}');\n";
    $configContent .= "define('DB_PASS', '{$pass}');\n\n";
    $configContent .= "?>";

    require 'classes/Database.php';
    $db = new Database($host, $name, $user, $pass);

    // Test the database connection
    $conn = $db->connect();

    if ($conn instanceof PDO) {
        file_put_contents('config/database.php', $configContent);
        require 'config/database.php';

        $samplePost = [
            'title' => 'Welcome to Your New CMS!',
            'content' => 'Congratulations on successfully installing your new CMS. This is a sample post. You can edit or delete it to start creating your own content!'
        ];

        setupTables($conn);

        insertDefaultPages($conn);

        $categoryId = $conn->query("SELECT id FROM categories WHERE slug = 'general'")->fetchColumn();
        $samplePostId = insertSampleContent($conn, 'post', $samplePost, $categoryId);

        $sampleBlocks = [
            [
                'type' => 'text',
                'title' => 'Sample Text Block',
                'content' => 'This is a text block content.',
                'image_path' => '',
                'alt_text' => '',
                'caption' => '',
                'url' => '',
                'class' => '',
                'metafield1' => null,
                'metafield2' => null,
                'metafield3' => null,
                'cta_text' => '',
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
                'url' => '',
                'class' => '',
                'metafield1' => null,
                'metafield2' => null,
                'metafield3' => null,
                'cta_text' => 'Click here',
                'order_num' => 2,
                'status' => 'active',
                'start_date' => null,
                'end_date' => null,
            ]
        ];

        foreach ($sampleBlocks as $block) {
            insertSampleBlock($conn, $samplePostId, $block);
        }

        setDefaultSettings($conn);
        flagAsInstalled($conn);
        createAdminUser($conn, $admin_user, $admin_pass);

        echo "Installation successful! For security reasons, please delete or rename the install.php file.";
        exit;
    }
}

?>

<form method="post">
    DB Host: <input type="text" name="db_host" required><br>
    DB Name: <input type="text" name="db_name" required><br>
    DB User: <input type="text" name="db_user" required><br>
    DB Password: <input type="text" name="db_pass" required><br><br>
    Admin Username: <input type="text" name="admin_user" required><br>
    Admin Password: <input type="password" name="admin_pass" required><br>
    <input type="submit" value="Install">
</form>
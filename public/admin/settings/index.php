<?php
ob_start();

require '../../../config/database.php';
require '../../../config/config.php';
require '../../../config/autoload.php';
require '../auth_check.php';

$db = new Database();
$conn = $db->connect();

if (!$conn instanceof PDO) {
    die("Error establishing a database connection.");
}

$settings = new Settings($conn);
$upload = new Upload($conn);

$site_title = htmlspecialchars($settings->getSetting('site_title'), ENT_QUOTES, 'UTF-8');
$site_description = htmlspecialchars($settings->getSetting('site_description'), ENT_QUOTES, 'UTF-8');
$site_logo = htmlspecialchars($settings->getSetting('site_logo'), ENT_QUOTES, 'UTF-8');
$site_favicon = htmlspecialchars($settings->getSetting('site_favicon'), ENT_QUOTES, 'UTF-8');
$footer_text = htmlspecialchars($settings->getSetting('footer_text'), ENT_QUOTES, 'UTF-8');
$default_language = htmlspecialchars($settings->getSetting('default_language'), ENT_QUOTES, 'UTF-8');
$timezone = htmlspecialchars($settings->getSetting('timezone'), ENT_QUOTES, 'UTF-8');
$date_format = htmlspecialchars($settings->getSetting('date_format'), ENT_QUOTES, 'UTF-8');
$time_format = htmlspecialchars($settings->getSetting('time_format'), ENT_QUOTES, 'UTF-8');
$posts_per_page = htmlspecialchars($settings->getSetting('posts_per_page'), ENT_QUOTES, 'UTF-8');
$social_media_links = htmlspecialchars($settings->getSetting('social_media_links'), ENT_QUOTES, 'UTF-8');
$google_analytics_code = htmlspecialchars($settings->getSetting('google_analytics_code'), ENT_QUOTES, 'UTF-8');
$custom_css = htmlspecialchars($settings->getSetting('custom_css'), ENT_QUOTES, 'UTF-8');
$custom_js = htmlspecialchars($settings->getSetting('custom_js'), ENT_QUOTES, 'UTF-8');
$maintenance_mode = htmlspecialchars($settings->getSetting('maintenance_mode'), ENT_QUOTES, 'UTF-8');
$header_scripts = htmlspecialchars($settings->getSetting('header_scripts'), ENT_QUOTES, 'UTF-8');
$footer_scripts = htmlspecialchars($settings->getSetting('footer_scripts'), ENT_QUOTES, 'UTF-8');
$default_post_thumbnail = htmlspecialchars($settings->getSetting('default_post_thumbnail'), ENT_QUOTES, 'UTF-8');
$pagination_type = htmlspecialchars($settings->getSetting('pagination_type'), ENT_QUOTES, 'UTF-8');

// New email settings
$email_from = htmlspecialchars($settings->getSetting('email_from'), ENT_QUOTES, 'UTF-8');
$email_smtp_host = htmlspecialchars($settings->getSetting('email_smtp_host'), ENT_QUOTES, 'UTF-8');
$email_smtp_port = htmlspecialchars($settings->getSetting('email_smtp_port'), ENT_QUOTES, 'UTF-8');
$email_smtp_username = htmlspecialchars($settings->getSetting('email_smtp_username'), ENT_QUOTES, 'UTF-8');
$email_smtp_password = htmlspecialchars($settings->getSetting('email_smtp_password'), ENT_QUOTES, 'UTF-8');
$email_smtp_encryption = htmlspecialchars($settings->getSetting('email_smtp_encryption'), ENT_QUOTES, 'UTF-8');

$timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

$date_formats = [
    'F j, Y' => date('F j, Y'),
    'Y-m-d' => date('Y-m-d'),
    'm/d/Y' => date('m/d/Y'),
    'd/m/Y' => date('d/m/Y'),
    'M j, Y' => date('M j, Y'),
];

$time_formats = [
    'g:i a' => date('g:i a'),
    'g:i A' => date('g:i A'),
    'H:i' => date('H:i'),
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update settings securely
    $site_title = $_POST['site_title'];
    $site_description = $_POST['site_description'];
    $site_logo = $_POST['site_logo'];
    $site_favicon = $_POST['site_favicon'];
    $footer_text = $_POST['footer_text'];
    $default_language = $_POST['default_language'];
    $timezone = $_POST['timezone'];
    $date_format = $_POST['date_format'];
    $time_format = $_POST['time_format'];
    $posts_per_page = $_POST['posts_per_page'];
    $social_media_links = $_POST['social_media_links'];
    $google_analytics_code = $_POST['google_analytics_code'];
    $custom_css = $_POST['custom_css'];
    $custom_js = $_POST['custom_js'];
    $maintenance_mode = $_POST['maintenance_mode'];
    $header_scripts = $_POST['header_scripts'];
    $footer_scripts = $_POST['footer_scripts'];
    $default_post_thumbnail = $_POST['default_post_thumbnail'];
    $pagination_type = $_POST['pagination_type'];

    $settings->updateSetting('site_title', $site_title);
    $settings->updateSetting('site_description', $site_description);
    $settings->updateSetting('site_logo', $site_logo);
    $settings->updateSetting('site_favicon', $site_favicon);
    $settings->updateSetting('footer_text', $footer_text);
    $settings->updateSetting('default_language', $default_language);
    $settings->updateSetting('timezone', $timezone);
    $settings->updateSetting('date_format', $date_format);
    $settings->updateSetting('time_format', $time_format);
    $settings->updateSetting('posts_per_page', $posts_per_page);
    $settings->updateSetting('social_media_links', $social_media_links);
    $settings->updateSetting('google_analytics_code', $google_analytics_code);
    $settings->updateSetting('custom_css', $custom_css);
    $settings->updateSetting('custom_js', $custom_js);
    $settings->updateSetting('maintenance_mode', $maintenance_mode);
    $settings->updateSetting('header_scripts', $header_scripts);
    $settings->updateSetting('footer_scripts', $footer_scripts);
    $settings->updateSetting('default_post_thumbnail', $default_post_thumbnail);
    $settings->updateSetting('pagination_type', $pagination_type);

    // Update new email settings
    $settings->updateSetting('email_from', $_POST['email_from']);
    $settings->updateSetting('email_smtp_host', $_POST['email_smtp_host']);
    $settings->updateSetting('email_smtp_port', $_POST['email_smtp_port']);
    $settings->updateSetting('email_smtp_username', $_POST['email_smtp_username']);
    $settings->updateSetting('email_smtp_password', $_POST['email_smtp_password']);
    $settings->updateSetting('email_smtp_encryption', $_POST['email_smtp_encryption']);

    $success_message = "Settings updated successfully.";
}

$uploads = $upload->listFiles();

include '../../../templates/header-admin.php';
?>

<div class="settings-container">
    <h1 class="settings-title">Settings</h1>

    <?php if (isset($_GET['email_test_status']) && isset($_GET['email_test_message'])) : ?>
        <div class="alert alert-<?php echo $_GET['email_test_status'] === 'success' ? 'success' : 'danger'; ?>">
            <?php echo htmlspecialchars($_GET['email_test_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($success_message)) : ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <form action="" method="POST" class="settings-form">

        <div class="form-group">
            <label for="site_title" class="form-label">Site Title:</label>
            <input type="text" id="site_title" name="site_title" class="form-input" value="<?= $site_title ?>">
        </div>

        <div class="form-group">
            <label for="site_description" class="form-label">Site Description:</label>
            <textarea id="site_description" name="site_description" class="form-input"><?= $site_description ?></textarea>
        </div>

        <div class="form-group">
            <label for="site_logo" class="form-label">Site Logo:</label>
            <select name="site_logo" id="site_logo" class="form-input">
                <option value="">Select a logo</option>
                <?php foreach ($uploads as $file) : ?>
                    <?php if (in_array($file['type'], ['image/jpeg', 'image/png', 'image/gif'])) : ?>
                        <option value="<?= htmlspecialchars($file['url']) ?>" <?= ($site_logo == $file['url']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($file['url']) ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="site_favicon" class="form-label">Site Favicon:</label>
            <select name="site_favicon" id="site_favicon" class="form-input">
                <option value="">Select a favicon</option>
                <?php foreach ($uploads as $file) : ?>
                    <?php if (in_array($file['type'], ['image/x-icon', 'image/vnd.microsoft.icon', 'image/png'])) : ?>
                        <option value="<?= htmlspecialchars($file['url']) ?>" <?= ($site_favicon == $file['url']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($file['url']) ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="footer_text" class="form-label">Footer Text:</label>
            <input type="text" id="footer_text" name="footer_text" class="form-input" value="<?= $footer_text ?>">
        </div>

        <div class="form-group">
            <label for="default_language" class="form-label">Default Language:</label>
            <input type="text" id="default_language" name="default_language" class="form-input" value="<?= $default_language ?>">
        </div>

        <div class="form-group">
            <label for="timezone" class="form-label">Timezone:</label>
            <select id="timezone" name="timezone" class="form-input">
                <?php foreach ($timezones as $tz) : ?>
                    <option value="<?= htmlspecialchars($tz) ?>" <?= ($timezone == $tz) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($tz) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="date_format" class="form-label">Date Format:</label>
            <select id="date_format" name="date_format" class="form-input">
                <?php foreach ($date_formats as $format => $example) : ?>
                    <option value="<?= htmlspecialchars($format) ?>" <?= ($date_format == $format) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($example) ?> (<?= htmlspecialchars($format) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="time_format" class="form-label">Time Format:</label>
            <select id="time_format" name="time_format" class="form-input">
                <?php foreach ($time_formats as $format => $example) : ?>
                    <option value="<?= htmlspecialchars($format) ?>" <?= ($time_format == $format) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($example) ?> (<?= htmlspecialchars($format) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="posts_per_page" class="form-label">Posts per Page:</label>
            <input type="number" id="posts_per_page" name="posts_per_page" class="form-input" value="<?= $posts_per_page ?>">
        </div>

        <div class="form-group">
            <label for="social_media_links" class="form-label">Social Media Links:</label>
            <textarea id="social_media_links" name="social_media_links" class="form-input"><?= $social_media_links ?></textarea>
        </div>

        <div class="form-group">
            <label for="google_analytics_code" class="form-label">Google Analytics Code:</label>
            <textarea id="google_analytics_code" name="google_analytics_code" class="form-input"><?= $google_analytics_code ?></textarea>
        </div>

        <div class="form-group">
            <label for="custom_css" class="form-label">Custom CSS:</label>
            <textarea id="custom_css" name="custom_css" class="form-input"><?= $custom_css ?></textarea>
        </div>

        <div class="form-group">
            <label for="custom_js" class="form-label">Custom JS:</label>
            <textarea id="custom_js" name="custom_js" class="form-input"><?= $custom_js ?></textarea>
        </div>

        <div class="form-group">
            <label for="maintenance_mode" class="form-label">Maintenance Mode:</label>
            <select id="maintenance_mode" name="maintenance_mode" class="form-input">
                <option value="false" <?= $maintenance_mode == 'false' ? 'selected' : '' ?>>Disabled</option>
                <option value="true" <?= $maintenance_mode == 'true' ? 'selected' : '' ?>>Enabled</option>
            </select>
        </div>

        <div class="form-group">
            <label for="header_scripts" class="form-label">Header Scripts:</label>
            <textarea id="header_scripts" name="header_scripts" class="form-input"><?= $header_scripts ?></textarea>
        </div>

        <div class="form-group">
            <label for="footer_scripts" class="form-label">Footer Scripts:</label>
            <textarea id="footer_scripts" name="footer_scripts" class="form-input"><?= $footer_scripts ?></textarea>
        </div>


        <div class="form-group">
            <label for="default_post_thumbnail" class="form-label">Default Post Thumbnail:</label>
            <select name="default_post_thumbnail" id="default_post_thumbnail" class="form-input">
                <option value="">Select a default thumbnail</option>
                <?php foreach ($uploads as $file) : ?>
                    <?php if (in_array($file['type'], ['image/jpeg', 'image/png', 'image/gif'])) : ?>
                        <option value="<?= htmlspecialchars($file['url']) ?>" <?= ($default_post_thumbnail == $file['url']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($file['url']) ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="pagination_type" class="form-label">Pagination Type:</label>
            <select id="pagination_type" name="pagination_type" class="form-input">
                <option value="numbered" <?= $pagination_type == 'numbered' ? 'selected' : '' ?>>Numbered</option>
                <option value="load_more" <?= $pagination_type == 'load_more' ? 'selected' : '' ?>>Load More</option>
                <option value="infinite_scroll" <?= $pagination_type == 'infinite_scroll' ? 'selected' : '' ?>>Infinite Scroll</option>
            </select>
        </div>

        <!-- New Email Settings Fields -->
        <h2>Email Settings</h2>
        <div class="form-group">
            <label for="email_from" class="form-label">From Email:</label>
            <input type="email" id="email_from" name="email_from" class="form-input" value="<?= $email_from ?>">
        </div>

        <div class="form-group">
            <label for="email_smtp_host" class="form-label">SMTP Host:</label>
            <input type="text" id="email_smtp_host" name="email_smtp_host" class="form-input" value="<?= $email_smtp_host ?>">
        </div>

        <div class="form-group">
            <label for="email_smtp_port" class="form-label">SMTP Port:</label>
            <input type="number" id="email_smtp_port" name="email_smtp_port" class="form-input" value="<?= $email_smtp_port ?>">
        </div>

        <div class="form-group">
            <label for="email_smtp_username" class="form-label">SMTP Username:</label>
            <input type="text" id="email_smtp_username" name="email_smtp_username" class="form-input" value="<?= $email_smtp_username ?>">
        </div>

        <div class="form-group">
            <label for="email_smtp_password" class="form-label">SMTP Password:</label>
            <input type="password" id="email_smtp_password" name="email_smtp_password" class="form-input" value="<?= $email_smtp_password ?>">
        </div>

        <div class="form-group">
            <label for="email_smtp_encryption" class="form-label">SMTP Encryption:</label>
            <select id="email_smtp_encryption" name="email_smtp_encryption" class="form-input">
                <option value="" <?= $email_smtp_encryption == '' ? 'selected' : '' ?>>None</option>
                <option value="tls" <?= $email_smtp_encryption == 'tls' ? 'selected' : '' ?>>TLS</option>
                <option value="ssl" <?= $email_smtp_encryption == 'ssl' ? 'selected' : '' ?>>SSL</option>
            </select>
        </div>

        <input type="submit" value="Update" class="form-submit">
    </form>



    <!-- Test Email Section -->
    <div class="form-group">
        <h2>Test Email Configuration</h2>
        <form action="test_email.php" method="POST" class="settings-form">
            <div class="form-group">
                <label for="test_email" class="form-label">Send Test Email To:</label>
                <input type="email" id="test_email" name="test_email" class="form-input" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-secondary">Send Test Email</button>
            </div>
        </form>
    </div>
</div>


<?php include '../../../templates/footer-admin.php'; ?>
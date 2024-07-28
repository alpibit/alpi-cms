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
$google_analytics_code = htmlspecialchars($settings->getSetting('google_analytics_code'), ENT_QUOTES, 'UTF-8');
$custom_css = htmlspecialchars($settings->getSetting('custom_css'), ENT_QUOTES, 'UTF-8');
$maintenance_mode = htmlspecialchars($settings->getSetting('maintenance_mode'), ENT_QUOTES, 'UTF-8');
$header_scripts = htmlspecialchars($settings->getSetting('header_scripts'), ENT_QUOTES, 'UTF-8');
$footer_scripts = htmlspecialchars($settings->getSetting('footer_scripts'), ENT_QUOTES, 'UTF-8');
$default_post_thumbnail = htmlspecialchars($settings->getSetting('default_post_thumbnail'), ENT_QUOTES, 'UTF-8');
$pagination_type = htmlspecialchars($settings->getSetting('pagination_type'), ENT_QUOTES, 'UTF-8');

// Email settings
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
    $google_analytics_code = $_POST['google_analytics_code'];
    $custom_css = $_POST['custom_css'];
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
    $settings->updateSetting('google_analytics_code', $google_analytics_code);
    $settings->updateSetting('custom_css', $custom_css);
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

<div class="alpi-admin-content">
    <h1 class="alpi-text-primary alpi-mb-lg">Settings</h1>

    <?php if (isset($_GET['email_test_status']) && isset($_GET['email_test_message'])) : ?>
        <div class="alpi-alert <?php echo $_GET['email_test_status'] === 'success' ? 'alpi-alert-success' : 'alpi-alert-danger'; ?> alpi-mb-md">
            <?php echo htmlspecialchars($_GET['email_test_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($success_message)) : ?>
        <div class="alpi-alert alpi-alert-success alpi-mb-md"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <form action="" method="POST" class="alpi-form">
        <div class="alpi-card alpi-p-lg alpi-mb-lg">
            <h2 class="alpi-text-secondary alpi-mb-md">General Settings</h2>

            <div class="alpi-form-group">
                <label for="site_title" class="alpi-form-label">Site Title:</label>
                <input type="text" id="site_title" name="site_title" class="alpi-form-input" value="<?= $site_title ?>">
                <p class="alpi-form-help">The name of your website, displayed in the browser tab and various other places.</p>
            </div>

            <div class="alpi-form-group">
                <label for="site_description" class="alpi-form-label">Site Description:</label>
                <textarea id="site_description" name="site_description" class="alpi-form-input"><?= $site_description ?></textarea>
                <p class="alpi-form-help">A brief description of your website, often used by search engines.</p>
            </div>

            <div class="alpi-form-group">
                <label for="site_logo" class="alpi-form-label">Site Logo:</label>
                <select name="site_logo" id="site_logo" class="alpi-form-input">
                    <option value="">Select a logo</option>
                    <?php foreach ($uploads as $file) : ?>
                        <?php if (in_array($file['type'], ['image/jpeg', 'image/png', 'image/gif'])) : ?>
                            <option value="<?= htmlspecialchars($file['url']) ?>" <?= ($site_logo == $file['url']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($file['url']) ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <p class="alpi-form-help">The main logo of your website, typically displayed in the header.</p>
            </div>

            <div class="alpi-form-group">
                <label for="site_favicon" class="alpi-form-label">Site Favicon:</label>
                <select name="site_favicon" id="site_favicon" class="alpi-form-input">
                    <option value="">Select a favicon</option>
                    <?php foreach ($uploads as $file) : ?>
                        <?php if (in_array($file['type'], ['image/x-icon', 'image/vnd.microsoft.icon', 'image/png'])) : ?>
                            <option value="<?= htmlspecialchars($file['url']) ?>" <?= ($site_favicon == $file['url']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($file['url']) ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <p class="alpi-form-help">The small icon displayed in the browser's address bar and tabs.</p>
            </div>

            <div class="alpi-form-group">
                <label for="footer_text" class="alpi-form-label">Footer Text:</label>
                <input type="text" id="footer_text" name="footer_text" class="alpi-form-input" value="<?= $footer_text ?>">
                <p class="alpi-form-help">Text displayed in the footer of your website, often used for copyright notices.</p>
            </div>

            <div class="alpi-form-group">
                <label for="default_language" class="alpi-form-label">Default Language:</label>
                <input type="text" id="default_language" name="default_language" class="alpi-form-input" value="<?= $default_language ?>">
                <p class="alpi-form-help">The primary language of your website (e.g., 'en' for English).</p>
            </div>

            <div class="alpi-form-group">
                <label for="timezone" class="alpi-form-label">Timezone:</label>
                <select id="timezone" name="timezone" class="alpi-form-input">
                    <?php foreach ($timezones as $tz) : ?>
                        <option value="<?= htmlspecialchars($tz) ?>" <?= ($timezone == $tz) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tz) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="alpi-form-help">The timezone used for displaying dates and times on your website.</p>
            </div>

            <div class="alpi-form-group">
                <label for="date_format" class="alpi-form-label">Date Format:</label>
                <select id="date_format" name="date_format" class="alpi-form-input">
                    <?php foreach ($date_formats as $format => $example) : ?>
                        <option value="<?= htmlspecialchars($format) ?>" <?= ($date_format == $format) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($example) ?> (<?= htmlspecialchars($format) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="alpi-form-help">The format used for displaying dates throughout your website.</p>
            </div>

            <div class="alpi-form-group">
                <label for="time_format" class="alpi-form-label">Time Format:</label>
                <select id="time_format" name="time_format" class="alpi-form-input">
                    <?php foreach ($time_formats as $format => $example) : ?>
                        <option value="<?= htmlspecialchars($format) ?>" <?= ($time_format == $format) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($example) ?> (<?= htmlspecialchars($format) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="alpi-form-help">The format used for displaying times throughout your website.</p>
            </div>
        </div>

        <div class="alpi-card alpi-p-lg alpi-mb-lg">
            <h2 class="alpi-text-secondary alpi-mb-md">Content Settings</h2>

            <div class="alpi-form-group">
                <label for="posts_per_page" class="alpi-form-label">Posts per Page:</label>
                <input type="number" id="posts_per_page" name="posts_per_page" class="alpi-form-input" value="<?= $posts_per_page ?>">
                <p class="alpi-form-help">The number of posts to display on each page.</p>
            </div>

            <div class="alpi-form-group">
                <label for="default_post_thumbnail" class="alpi-form-label">Default Post Thumbnail:</label>
                <select name="default_post_thumbnail" id="default_post_thumbnail" class="alpi-form-input">
                    <option value="">Select a default thumbnail</option>
                    <?php foreach ($uploads as $file) : ?>
                        <?php if (in_array($file['type'], ['image/jpeg', 'image/png', 'image/gif'])) : ?>
                            <option value="<?= htmlspecialchars($file['url']) ?>" <?= ($default_post_thumbnail == $file['url']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($file['url']) ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <p class="alpi-form-help">The default image to use when a post doesn't have a specific thumbnail set.</p>
            </div>

            <div class="alpi-form-group">
                <label for="pagination_type" class="alpi-form-label">Pagination Type:</label>
                <select id="pagination_type" name="pagination_type" class="alpi-form-input">
                    <option value="numbered" <?= $pagination_type == 'numbered' ? 'selected' : '' ?>>Numbered</option>
                    <option value="load_more" <?= $pagination_type == 'load_more' ? 'selected' : '' ?>>Load More</option>
                    <option value="infinite_scroll" <?= $pagination_type == 'infinite_scroll' ? 'selected' : '' ?>>Infinite Scroll</option>
                </select>
                <p class="alpi-form-help">Choose how you want to display multiple pages of content.</p>
            </div>
        </div>

        <div class="alpi-card alpi-p-lg alpi-mb-lg">
            <h2 class="alpi-text-secondary alpi-mb-md">Custom Code and Analytics</h2>

            <div class="alpi-form-group">
                <label for="google_analytics_code" class="alpi-form-label">Google Analytics Code:</label>
                <textarea id="google_analytics_code" name="google_analytics_code" class="alpi-form-input" rows="4"><?= $google_analytics_code ?></textarea>
                <p class="alpi-form-help">Your Google Analytics tracking code. Paste the entire &lt;script&gt; tag provided by Google.</p>
            </div>

            <div class="alpi-form-group">
                <label for="custom_css" class="alpi-form-label">Custom CSS:</label>
                <textarea id="custom_css" name="custom_css" class="alpi-form-input" rows="6"><?= $custom_css ?></textarea>
                <p class="alpi-form-help">Custom CSS styles to be applied to your website. These styles will override the default theme styles.</p>
            </div>

            <div class="alpi-form-group">
                <label for="header_scripts" class="alpi-form-label">Header Scripts:</label>
                <textarea id="header_scripts" name="header_scripts" class="alpi-form-input" rows="4"><?= $header_scripts ?></textarea>
                <p class="alpi-form-help">Scripts to be included in the &lt;head&gt; section of your website. Useful for adding third-party integrations.</p>
            </div>

            <div class="alpi-form-group">
                <label for="footer_scripts" class="alpi-form-label">Footer Scripts:</label>
                <textarea id="footer_scripts" name="footer_scripts" class="alpi-form-input" rows="4"><?= $footer_scripts ?></textarea>
                <p class="alpi-form-help">Scripts to be included just before the closing &lt;/body&gt; tag. Useful for analytics or other tracking codes.</p>
            </div>
        </div>

        <div class="alpi-card alpi-p-lg alpi-mb-lg">
            <h2 class="alpi-text-secondary alpi-mb-md">Site Maintenance</h2>

            <div class="alpi-form-group">
                <label for="maintenance_mode" class="alpi-form-label">Maintenance Mode:</label>
                <select id="maintenance_mode" name="maintenance_mode" class="alpi-form-input">
                    <option value="false" <?= $maintenance_mode == 'false' ? 'selected' : '' ?>>Disabled</option>
                    <option value="true" <?= $maintenance_mode == 'true' ? 'selected' : '' ?>>Enabled</option>
                </select>
                <p class="alpi-form-help">When enabled, visitors will see a maintenance message instead of your website.</p>
            </div>
        </div>

        <div class="alpi-card alpi-p-lg alpi-mb-lg">
            <h2 class="alpi-text-secondary alpi-mb-md">Email Settings</h2>

            <div class="alpi-form-group">
                <label for="email_from" class="alpi-form-label">From Email:</label>
                <input type="email" id="email_from" name="email_from" class="alpi-form-input" value="<?= $email_from ?>">
                <p class="alpi-form-help">The email address that will appear as the sender for all emails sent by the system.</p>
            </div>

            <div class="alpi-form-group">
                <label for="email_smtp_host" class="alpi-form-label">SMTP Host:</label>
                <input type="text" id="email_smtp_host" name="email_smtp_host" class="alpi-form-input" value="<?= $email_smtp_host ?>">
                <p class="alpi-form-help">The hostname of your SMTP server (e.g., smtp.gmail.com).</p>
            </div>

            <div class="alpi-form-group">
                <label for="email_smtp_port" class="alpi-form-label">SMTP Port:</label>
                <input type="number" id="email_smtp_port" name="email_smtp_port" class="alpi-form-input" value="<?= $email_smtp_port ?>">
                <p class="alpi-form-help">The port number for your SMTP server (common ports are 25, 465, or 587).</p>
            </div>

            <div class="alpi-form-group">
                <label for="email_smtp_username" class="alpi-form-label">SMTP Username:</label>
                <input type="text" id="email_smtp_username" name="email_smtp_username" class="alpi-form-input" value="<?= $email_smtp_username ?>">
                <p class="alpi-form-help">The username for authenticating with your SMTP server.</p>
            </div>

            <div class="alpi-form-group">
                <label for="email_smtp_password" class="alpi-form-label">SMTP Password:</label>
                <input type="password" id="email_smtp_password" name="email_smtp_password" class="alpi-form-input" value="<?= $email_smtp_password ?>">
                <p class="alpi-form-help">The password for authenticating with your SMTP server.</p>
            </div>

            <div class="alpi-form-group">
                <label for="email_smtp_encryption" class="alpi-form-label">SMTP Encryption:</label>
                <select id="email_smtp_encryption" name="email_smtp_encryption" class="alpi-form-input">
                    <option value="" <?= $email_smtp_encryption == '' ? 'selected' : '' ?>>None</option>
                    <option value="tls" <?= $email_smtp_encryption == 'tls' ? 'selected' : '' ?>>TLS</option>
                    <option value="ssl" <?= $email_smtp_encryption == 'ssl' ? 'selected' : '' ?>>SSL</option>
                </select>
                <p class="alpi-form-help">The encryption method used by your SMTP server. Choose 'None' if your server doesn't use encryption.</p>
            </div>
        </div>

        <div class="alpi-text-right">
            <button type="submit" class="alpi-btn alpi-btn-primary">Update Settings</button>
        </div>
    </form>

    <div class="alpi-card alpi-p-lg alpi-mt-lg">
        <h2 class="alpi-text-secondary alpi-mb-md">Test Email Configuration</h2>
        <form action="test_email.php" method="POST" class="alpi-form">
            <div class="alpi-form-group">
                <label for="test_email" class="alpi-form-label">Send Test Email To:</label>
                <input type="email" id="test_email" name="test_email" class="alpi-form-input" required>
                <p class="alpi-form-help">Enter an email address to send a test email and verify your email settings.</p>
            </div>
            <div class="alpi-text-right">
                <button type="submit" class="alpi-btn alpi-btn-secondary">Send Test Email</button>
            </div>
        </form>
    </div>
</div>

<?php
include '../../../templates/footer-admin.php';
ob_end_flush();
?>
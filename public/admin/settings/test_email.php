<?php
require '../../../config/autoload.php';
require '../../../config/database.php';
require '../../../config/config.php';
require '../auth_check.php';

$db = new Database();
$conn = $db->connect();

if (!$conn instanceof PDO) {
    die("Error establishing a database connection.");
}

$settings = new Settings($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['test_email'])) {
    $to = $_POST['test_email'];
    $from = $settings->getSetting('email_from');
    $smtp_host = $settings->getSetting('email_smtp_host');
    $smtp_port = $settings->getSetting('email_smtp_port');
    $smtp_username = $settings->getSetting('email_smtp_username');
    $smtp_password = $settings->getSetting('email_smtp_password');
    $smtp_encryption = $settings->getSetting('email_smtp_encryption');

    $email = new Email();
    $email->setTo($to);
    $email->setFrom($from);
    $email->setSubject('Test Email from Your CMS');
    $email->setMessage('<h1>Test Email</h1><p>This is a test email from your CMS. If you received this, your email configuration is working correctly.</p>');
    $email->setAltMessage('Test Email: This is a test email from your CMS. If you received this, your email configuration is working correctly.');

    if ($smtp_host) {
        $email->setSmtpSettings($smtp_host, $smtp_port, $smtp_username, $smtp_password, $smtp_encryption);
    }

    try {
        if ($email->send()) {
            $message = "Test email sent successfully to {$to}";
            $status = 'success';
        } else {
            $message = "Failed to send test email to {$to}";
            $status = 'error';
        }
    } catch (Exception $e) {
        $message = "Error sending email: " . $e->getMessage();
        $status = 'error';
    }

    // Redirect back to the settings page with a status message
    header("Location: index.php?email_test_status={$status}&email_test_message=" . urlencode($message));
    exit;
}

// If accessed directly without POST data, redirect to the settings page
header("Location: index.php");
exit;

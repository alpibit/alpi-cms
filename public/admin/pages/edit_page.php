<?php
ob_start();

require '../../../config/autoload.php';
require '../../../config/database.php';
require '../../../config/config.php';
require_once '../../../classes/BlockData.php';
require '../auth_check.php';

$db = new Database();
$conn = $db->connect();
$page = new Page($conn);
$error = '';

$pageData = $page->getPageById($_GET['id']);
$blocksData = $pageData['blocks'] ?? [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!alpiVerifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token. Please refresh and try again.';
        alpiRegenerateCsrfToken();
    } else {
        $title = $_POST['title'];
        $subtitle = $_POST['subtitle'];
        $mainImagePath = $_POST['main_image_path'];
        $showMainImage = isset($_POST['show_main_image']) ? 1 : 0;
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $userId = $_SESSION['user_id'] ?? 0;
        $contentBlocks = BlockData::normalizeSubmittedBlocks($_POST['blocks'] ?? []);

        $page->updatePage($_GET['id'], $title, $subtitle, $mainImagePath, $showMainImage, $isActive, $contentBlocks, $userId);

        alpiRegenerateCsrfToken();
        $updateSuccess = true;
        $message = "Page updated successfully!";

        $pageData = $page->getPageById($_GET['id']);
        $blocksData = $pageData['blocks'] ?? [];
    }
}

include '../../../templates/header-admin.php';
?>

<div class="alpi-admin-content">
    <h1 class="alpi-text-primary alpi-mb-lg">Edit Page</h1>

    <?php if (isset($updateSuccess) && $updateSuccess) : ?>
        <div class="alpi-alert alpi-alert-success alpi-mb-md">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error) : ?>
        <div class="alpi-alert alpi-alert-danger alpi-mb-md">
            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" class="alpi-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(alpiGetCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <div class="alpi-card alpi-mb-lg">
            <h2 class="alpi-card-header">Page Details</h2>
            <div class="alpi-card-body">
                <div class="alpi-form-group">
                    <label for="title" class="alpi-form-label">Title:</label>
                    <input type="text" id="title" name="title" class="alpi-form-input" value="<?= isset($pageData['title']) ? htmlspecialchars($pageData['title'], ENT_QUOTES, 'UTF-8') : '' ?>" placeholder="Enter page title" required>
                </div>

                <div class="alpi-form-group">
                    <label for="subtitle" class="alpi-form-label">Subtitle:</label>
                    <input type="text" id="subtitle" name="subtitle" class="alpi-form-input" value="<?= isset($pageData['subtitle']) ? htmlspecialchars($pageData['subtitle'], ENT_QUOTES, 'UTF-8') : '' ?>" placeholder="Enter page subtitle">
                </div>

                <div class="alpi-form-group">
                    <label for="main_image_path" class="alpi-form-label">Featured Image:</label>
                    <select id="main_image_path" name="main_image_path" class="alpi-form-input">
                        <option value="">Select an image</option>
                        <?php
                        $uploads = (new Upload($conn))->listFiles();
                        foreach ($uploads as $upload) {
                            $selected = ($pageData['main_image_path'] ?? '') == $upload['url'] ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($upload['url'], ENT_QUOTES, 'UTF-8') . "' {$selected}>" . htmlspecialchars($upload['url'], ENT_QUOTES, 'UTF-8') . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="alpi-form-group">
                    <label class="alpi-form-checkbox">
                        <input type="checkbox" name="show_main_image" <?= isset($pageData['show_main_image']) && $pageData['show_main_image'] ? 'checked' : '' ?>>
                        Show Main Image
                    </label>
                </div>

                <div class="alpi-form-group">
                    <label class="alpi-form-checkbox">
                        <input type="checkbox" name="is_active" <?= isset($pageData['is_active']) && $pageData['is_active'] ? 'checked' : '' ?>>
                        Is Active
                    </label>
                </div>
            </div>
        </div>

        <div class="alpi-card alpi-mb-lg">
            <h2 class="alpi-card-header">Content Blocks</h2>
            <div class="alpi-card-body">
                <fieldset id="contentBlocks">
                    <?php
                    if (isset($blocksData) && is_array($blocksData)) {
                        foreach ($blocksData as $index => $block) {
                            echo "<div class='alpi-block alpi-mb-md' data-index='{$index}'>";
                            echo "<label class='alpi-form-label'>Block Type:</label>";
                            echo "<select name='blocks[{$index}][type]' class='alpi-form-input alpi-mb-sm' onchange='loadSelectedBlockContent(this, {$index})'>";
                            $blockTypes = ['text', 'image_text', 'image', 'cta', 'post_picker', 'video', 'slider_gallery', 'quote', 'accordion', 'audio', 'free_code', 'map', 'form', 'hero'];
                            foreach ($blockTypes as $type) {
                                $selected = ($block['type'] == $type) ? 'selected' : '';
                                echo "<option value='{$type}' {$selected}>" . ucfirst(str_replace('_', ' ', $type)) . "</option>";
                            }
                            echo "</select>";
                            $blockDataJson = htmlspecialchars(json_encode($block), ENT_QUOTES, 'UTF-8');
                            echo "<div class='alpi-block-content alpi-mb-sm' data-value='{$blockDataJson}'></div>";
                            echo "<div class='alpi-btn-group'>";
                            echo "</div>";
                            echo "</div>";
                        }
                    }
                    ?>
                </fieldset>
                <button type="button" onclick="addBlock()" class="alpi-btn alpi-btn-secondary alpi-mt-md">Add Another Block</button>
            </div>
        </div>

        <div class="alpi-text-right">
            <button type="submit" class="alpi-btn alpi-btn-primary">Update Page</button>
        </div>
    </form>
</div>

<script src="/assets/js/posts-blocks.js"></script>

<?php
include '../../../templates/footer-admin.php';
ob_end_flush();
?>
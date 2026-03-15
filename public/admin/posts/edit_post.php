<?php
ob_start();

require '../../../config/autoload.php';
require '../../../config/database.php';
require '../../../config/config.php';
require_once '../../../classes/BlockData.php';
require '../auth_check.php';


$db = new Database();
$conn = $db->connect();
$post = new Post($conn);
$category = new Category($conn);
$postId = (int) ($_GET['id'] ?? 0);

$postData = $post->getPostById($postId);
$postData = $postData[0];
$categories = $category->getAllCategories();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!alpiVerifyCsrfToken($_POST['csrf_token'] ?? '')) {
        alpiRegenerateCsrfToken();
        alpiSetFlashValue('post_edit_message', [
            'type' => 'danger',
            'message' => 'Invalid CSRF token. Please refresh and try again.',
        ]);
        header('Location: ' . BASE_URL . '/public/admin/posts/edit_post.php?id=' . urlencode((string) $postId));
        exit;
    } else {
        $title = $_POST['title'];
        $subtitle = $_POST['subtitle'];
        $mainImagePath = (new Upload($conn))->sanitizeFileUrl($_POST['main_image_path'] ?? '', ['image']);
        $showMainImage = isset($_POST['show_main_image']) ? 1 : 0;
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $categoryId = $_POST['category_id'] ?? null;
        $slug = $post->generateSlug($title);
        $contentBlocks = BlockData::normalizeSubmittedBlocks($_POST['blocks'] ?? []);

        $userId = $_SESSION['user_id'] ?? 0;
        $post->updatePost($postId, $title, $contentBlocks, $slug, $userId, $subtitle, $mainImagePath, $showMainImage, $isActive, $categoryId);

        alpiRegenerateCsrfToken();
        alpiSetFlashValue('post_edit_message', [
            'type' => 'success',
            'message' => 'Post updated successfully!',
        ]);
        header('Location: ' . BASE_URL . '/public/admin/posts/edit_post.php?id=' . urlencode((string) $postId));
        exit;
    }
}

$flashMessage = alpiConsumeFlashValue('post_edit_message');


include '../../../templates/header-admin.php';
?>

<div class="alpi-admin-content">
    <h1 class="alpi-text-primary alpi-mb-lg">Edit Post</h1>

    <?php if ($flashMessage) : ?>
        <div class="alpi-alert <?= $flashMessage['type'] === 'success' ? 'alpi-alert-success' : 'alpi-alert-danger' ?> alpi-mb-md">
            <?php echo htmlspecialchars($flashMessage['message'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" class="alpi-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(alpiGetCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <div class="alpi-card alpi-mb-lg">
            <h2 class="alpi-card-header">Post Details</h2>
            <div class="alpi-card-body">
                <div class="alpi-form-group">
                    <label for="title" class="alpi-form-label">Title:</label>
                    <input type="text" id="title" name="title" class="alpi-form-input" value="<?= isset($postData['title']) ? htmlspecialchars($postData['title'], ENT_QUOTES, 'UTF-8') : '' ?>" placeholder="Title" required>
                </div>

                <div class="alpi-form-group">
                    <label for="subtitle" class="alpi-form-label">Subtitle:</label>
                    <input type="text" id="subtitle" name="subtitle" class="alpi-form-input" value="<?= isset($postData['subtitle']) ? htmlspecialchars($postData['subtitle'], ENT_QUOTES, 'UTF-8') : '' ?>" placeholder="Subtitle">
                </div>

                <div class="alpi-form-group">
                    <label for="main_image_path" class="alpi-form-label">Featured Image:</label>
                    <select id="main_image_path" name="main_image_path" class="alpi-form-input">
                        <option value="">Select an image</option>
                        <?php
                        $uploads = (new Upload($conn))->listFiles(['image']);
                        foreach ($uploads as $upload) {
                            $selected = $postData['main_image_path'] == $upload['url'] ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($upload['url'], ENT_QUOTES, 'UTF-8') . "' {$selected}>" . htmlspecialchars($upload['url'], ENT_QUOTES, 'UTF-8') . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="alpi-form-group">
                    <label class="alpi-form-checkbox">
                        <input type="checkbox" name="show_main_image" <?= $postData['show_main_image'] ? 'checked' : '' ?>>
                        Show Main Image
                    </label>
                </div>

                <div class="alpi-form-group">
                    <label class="alpi-form-checkbox">
                        <input type="checkbox" name="is_active" <?= $postData['is_active'] ? 'checked' : '' ?>>
                        Is Active
                    </label>
                </div>

                <div class="alpi-form-group">
                    <label for="category_id" class="alpi-form-label">Category:</label>
                    <select id="category_id" name="category_id" class="alpi-form-input">
                        <?php foreach ($categories as $cat) : ?>
                            <?php $selected = ($postData['category_id'] == $cat['id']) ? 'selected' : ''; ?>
                            <option value="<?= $cat['id'] ?>" <?= $selected ?>><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="alpi-card alpi-mb-lg">
            <h2 class="alpi-card-header">Content Blocks</h2>
            <div class="alpi-card-body">
                <fieldset id="contentBlocks">
                    <?php
                    if (isset($postData['blocks']) && is_array($postData['blocks'])) {
                        foreach ($postData['blocks'] as $index => $block) {
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
            <button type="submit" class="alpi-btn alpi-btn-primary">Update Post</button>
        </div>
    </form>
</div>

<script src="/assets/js/posts-blocks.js"></script>

<?php
include '../../../templates/footer-admin.php';
ob_end_flush();
?>
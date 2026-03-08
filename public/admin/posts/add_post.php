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
$categories = $category->getAllCategories();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $title = $_POST['title'];
    $subtitle = $_POST['subtitle'];
    $mainImagePath = $_POST['main_image_path'];
    $showMainImage = isset($_POST['show_main_image']) ? 1 : 0;
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $categoryId = $_POST['category_id'];
    $userId = $_SESSION['user_id'];
    $contentBlocks = BlockData::normalizeSubmittedBlocks($_POST['blocks'] ?? []);

    // Call the addPost function
    $post->addPost($title, $subtitle, $mainImagePath, $showMainImage, $isActive, $contentBlocks, $userId, $categoryId);

    header("Location: " . BASE_URL . "/public/admin/index.php");
    exit;
}

include '../../../templates/header-admin.php';
?>

<div class="alpi-admin-content">
    <h1 class="alpi-text-primary alpi-mb-lg">Add New Post</h1>

    <form action="" method="POST" class="alpi-form">
        <div class="alpi-card alpi-mb-lg">
            <h2 class="alpi-card-header">Post Details</h2>
            <div class="alpi-card-body">
                <div class="alpi-form-group">
                    <label for="title" class="alpi-form-label">Title:</label>
                    <input type="text" id="title" name="title" class="alpi-form-input" placeholder="Enter post title" required>
                </div>

                <div class="alpi-form-group">
                    <label for="subtitle" class="alpi-form-label">Subtitle:</label>
                    <input type="text" id="subtitle" name="subtitle" class="alpi-form-input" placeholder="Enter post subtitle">
                </div>

                <div class="alpi-form-group">
                    <label for="main_image_path" class="alpi-form-label">Featured Image:</label>
                    <select id="main_image_path" name="main_image_path" class="alpi-form-input">
                        <option value="">Select an image</option>
                        <?php
                        $uploads = (new Upload($conn))->listFiles();
                        foreach ($uploads as $upload) {
                            echo "<option value='" . htmlspecialchars($upload['url'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($upload['url'], ENT_QUOTES, 'UTF-8') . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="alpi-form-group">
                    <label class="alpi-form-checkbox">
                        <input type="checkbox" name="show_main_image">
                        Show Main Image
                    </label>
                </div>

                <div class="alpi-form-group">
                    <label class="alpi-form-checkbox">
                        <input type="checkbox" name="is_active">
                        Is Active
                    </label>
                </div>

                <div class="alpi-form-group">
                    <label for="category_id" class="alpi-form-label">Category:</label>
                    <select id="category_id" name="category_id" class="alpi-form-input">
                        <?php foreach ($categories as $cat) : ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="alpi-card alpi-mb-lg">
            <h2 class="alpi-card-header">Content Blocks</h2>
            <div class="alpi-card-body">
                <fieldset id="contentBlocks">
                    <div class='alpi-block' data-index='0'>
                        <label class="alpi-form-label">Block Type:</label>
                        <select name='blocks[0][type]' class="alpi-form-input alpi-mb-sm" onchange='loadSelectedBlockContent(this, 0)'>
                            <option value='text'>Text</option>
                            <option value='image_text'>Image + Text</option>
                            <option value='image'>Image</option>
                            <option value='cta'>Call to Action</option>
                            <option value='post_picker'>Post Picker</option>
                            <option value='video'>Video</option>
                            <option value='slider_gallery'>Slider Gallery</option>
                            <option value='quote'>Quote</option>
                            <option value='accordion'>Accordion</option>
                            <option value='audio'>Audio</option>
                            <option value='free_code'>Free Code</option>
                            <option value='map'>Map</option>
                            <option value='form'>Form</option>
                            <option value='hero'>Hero</option>
                        </select>
                        <div class='alpi-block-content alpi-mb-md'></div>
                        <div class='alpi-btn-group'>
                            <!-- Buttons will be dynamically added here by JS -->
                        </div>
                    </div>
                </fieldset>
                <button type="button" onclick="addBlock()" class="alpi-btn alpi-btn-secondary alpi-mt-md">Add Another Block</button>
            </div>
        </div>

        <div class="alpi-text-right">
            <button type="submit" class="alpi-btn alpi-btn-primary">Add Post</button>
        </div>
    </form>
</div>

<script src="/assets/js/posts-blocks.js"></script>

<?php
include '../../../templates/footer-admin.php';
ob_end_flush();
?>
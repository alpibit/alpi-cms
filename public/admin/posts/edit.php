<?php
ob_start();

session_start();

if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    header('Location: /public/admin/login.php');
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

require '../../../config/database.php';
require '../../../config/config.php';
require '../../../config/autoload.php';

$db = new Database();
$conn = $db->connect();
$post = new Post($conn);

$postData = $post->getPostById($_GET['id']);
$postData = $postData[0];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $contentBlocks = [];
    $slug = $post->generateSlug($title);


    foreach ($_POST['blocks'] as $block) {
        $contentBlocks[] = [
            'type' => $block['type'],
            'content' => $block['content'],
        ];
    }

    if (!isset($_SESSION['user_id'])) {
        die("User ID not set in session. Ensure you're setting this on login.");
    }
    $userId = $_SESSION['user_id'];

    $post->updatePost($_GET['id'], $title, $contentBlocks, $slug, $userId);
    header("Location: " . BASE_URL . "/public/admin/index.php");
    exit;
}

?>

<h1>Edit Post</h1>
<form action="" method="POST">
    Title: <input type="text" name="title" value="<?= isset($postData['title']) ? $postData['title'] : '' ?>"><br>
    <div id="contentBlocks">
        <?php
        if (isset($postData['blocks']) && is_array($postData['blocks'])) {
            foreach ($postData['blocks'] as $index => $block) :
                if (isset($block['type']) && isset($block['content'])) : ?>
                    <div class="block">
                        <label>Type:</label>
                        <select name="blocks[<?= $index ?>][type]">
                            <option value="text" <?= $block['type'] == 'text' ? 'selected' : '' ?>>Text</option>
                            <option value="image_text" <?= $block['type'] == 'image_text' ? 'selected' : '' ?>>Image Text</option>
                            <option value="image" <?= $block['type'] == 'image' ? 'selected' : '' ?>>Image</option>
                            <option value="cta" <?= $block['type'] == 'cta' ? 'selected' : '' ?>>CTA</option>
                        </select><br>
                        <textarea name="blocks[<?= $index ?>][content]"><?= $block['content'] ?></textarea>
                        <div class="buttons">
                        </div>
                        <br>
                    </div>
        <?php endif;
            endforeach;
        } ?>
    </div>
    <input type="submit" value="Update Post">
    <button type="button" onclick="addBlock()">Add Another Block</button>
</form>


<?php
ob_end_flush();
?>


<script>
    function addBlock() {
        var blockHTML = `
        <div class="block"> 
        <label>Type:</label>
        <select name="blocks[${document.getElementById('contentBlocks').childElementCount}][type]">
            <option value="text">Text</option>
            <option value="image_text">Image Text</option>
            <option value="image">Image</option>
            <option value="cta">CTA</option>
        </select><br>
        <textarea name="blocks[${document.getElementById('contentBlocks').childElementCount}][content]"></textarea><br>
        <div class="buttons">
            <button type="button" onclick="moveUp(this)">Move Up</button>
            <button type="button" onclick="moveDown(this)">Move Down</button>
            <button type="button" onclick="deleteBlock(this)">Delete</button>
        </div>
        <br>
        </div>
        `;
        document.getElementById('contentBlocks').insertAdjacentHTML('beforeend', blockHTML);
        updateButtons();
    }

    function deleteBlock(button) {
        var block = button.closest('div.block');
        block.remove();
        updateButtons();
    }

    function moveUp(button) {
        var block = button.closest('div.block');
        var prevBlock = block.previousElementSibling;
        if (prevBlock) {
            block.parentNode.insertBefore(block, prevBlock);
            updateButtons();
        }
    }

    function moveDown(button) {
        var block = button.closest('div.block');
        var nextBlock = block.nextElementSibling;
        if (nextBlock) {
            block.parentNode.insertBefore(nextBlock, block);
            updateButtons();
        }
    }

    function updateButtons() {
        var blocks = document.querySelectorAll('#contentBlocks .block');
        blocks.forEach(function(block, index) {
            var buttonsDiv = block.querySelector('.buttons');
            buttonsDiv.innerHTML = ''; // Clear the current buttons

            if (index > 0) {
                var moveUpButton = document.createElement('button');
                moveUpButton.type = 'button';
                moveUpButton.textContent = 'Move Up';
                moveUpButton.onclick = function() {
                    moveUp(this);
                };
                buttonsDiv.appendChild(moveUpButton);
            }

            if (index < blocks.length - 1) {
                var moveDownButton = document.createElement('button');
                moveDownButton.type = 'button';
                moveDownButton.textContent = 'Move Down';
                moveDownButton.onclick = function() {
                    moveDown(this);
                };
                buttonsDiv.appendChild(moveDownButton);
            }

            var deleteButton = document.createElement('button');
            deleteButton.type = 'button';
            deleteButton.textContent = 'Delete';
            deleteButton.onclick = function() {
                deleteBlock(this);
            };
            buttonsDiv.appendChild(deleteButton);
        });
    }

    window.onload = updateButtons;
</script>
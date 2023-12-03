<?php

// allow AJAX requests to this script
header('Access-Control-Allow-Origin: *');

require '../config/database.php';
require '../config/config.php';
require '../config/autoload.php';
$blockType = $_GET['type'] ?? '';
$index = $_GET['index'] ?? 0;
$block = $_GET['blockData'] ?? [];

if (is_string($block)) {
    $block = json_decode($block, true);
}

$db = new Database();
$conn = $db->connect();

$upload = new Upload($conn);
$uploads = $upload->listFiles();

switch ($blockType) {
    case 'text':
        $title = isset($block['title']) ? htmlspecialchars($block['title']) : '';
        $titleFontSize = isset($block['style6']) ? htmlspecialchars($block['style6']) : '';
        $titleColor = isset($block['style7']) ? htmlspecialchars($block['style7']) : '';
        $titleAlignment = isset($block['style8']) ? htmlspecialchars($block['style8']) : '';
        $content = isset($block['content']) ? htmlspecialchars($block['content']) : '';
        $textSize = isset($block['style1']) ? htmlspecialchars($block['style1']) : '';
        $textColor = isset($block['style2']) ? htmlspecialchars($block['style2']) : '';
        $backgroundColor = isset($block['background_color']) ? htmlspecialchars($block['background_color']) : '';
        $topPadding = isset($block['style4']) ? htmlspecialchars($block['style4']) : '';
        $bottomPadding = isset($block['style5']) ? htmlspecialchars($block['style5']) : '';

        ?>
        <div class="block-wrapper">
            <input type='text' name='blocks[<?php echo $index; ?>][title]' value='<?php echo $title; ?>' placeholder='Title'><br>
            <input type='text' name='blocks[<?php echo $index; ?>][style6]' value='<?php echo $titleFontSize; ?>' placeholder='Title Font Size'><br>
            <input type='text' name='blocks[<?php echo $index; ?>][style7]' value='<?php echo $titleColor; ?>' placeholder='Title Color'><br>
            <title>Text Alignment: </title><br>
            <select name='blocks[<?php echo $index; ?>][style8]'>
                <option value='left' <?php echo ($titleAlignment == 'left' ? 'selected' : ''); ?>>Left</option>
                <option value='center' <?php echo ($titleAlignment == 'center' ? 'selected' : ''); ?>>Center</option>
                <option value='right' <?php echo ($titleAlignment == 'right' ? 'selected' : ''); ?>>Right</option>
            </select><br>
            <textarea name='blocks[<?php echo $index; ?>][content]' placeholder="Text Area"><?php echo $content; ?></textarea><br>
            <input type='text' name='blocks[<?php echo $index; ?>][style1]' value='<?php echo $textSize; ?>' placeholder='Text Size'><br>
            <input type='text' name='blocks[<?php echo $index; ?>][style2]' value='<?php echo $textColor; ?>' placeholder='Text Color'><br>
            <input type='text' name='blocks[<?php echo $index; ?>][style3]' value='<?php echo $backgroundColor; ?>' placeholder='Background Color'><br>
            <input type='text' name='blocks[<?php echo $index; ?>][style4]' value='<?php echo $topPadding; ?>' placeholder='Top Padding'><br>
            <input type='text' name='blocks[<?php echo $index; ?>][style5]' value='<?php echo $bottomPadding; ?>' placeholder='Bottom Padding'><br>
        </div>
        <?php
        break;

    case 'image_text':
        $title = isset($block['title']) ? htmlspecialchars($block['title']) : '';
        $titleFontSize = isset($block['style6']) ? htmlspecialchars($block['style6']) : '';
        $titleColor = isset($block['style7']) ? htmlspecialchars($block['style7']) : '';
        $titleAlignment = isset($block['style8']) ? htmlspecialchars($block['style8']) : '';
        $content = isset($block['content']) ? htmlspecialchars($block['content']) : '';
        $layoutToggle = isset($block['layout1']) ? $block['layout1'] : 'image-text';
        $textSize = isset($block['style1']) ? htmlspecialchars($block['style1']) : '';
        $textColor = isset($block['style2']) ? htmlspecialchars($block['style2']) : '';
        $backgroundColor = isset($block['background_color']) ? htmlspecialchars($block['background_color']) : '';
        $topPadding = isset($block['style4']) ? htmlspecialchars($block['style4']) : '';
        $bottomPadding = isset($block['style5']) ? htmlspecialchars($block['style5']) : '';

        ?>
        <div class="block-wrapper">
            <select name='blocks[<?php echo $index; ?>][layout_toggle]' onchange='updateLayout(this, <?php echo $index; ?>)'>
                <option value='image-text' <?php echo ($layoutToggle == 'image-text' ? 'selected' : ''); ?>>Image-Text</option>
                <option value='text-image' <?php echo ($layoutToggle == 'text-image' ? 'selected' : ''); ?>>Text-Image</option>
            </select><br>

            <input type='text' name='blocks[<?php echo $index; ?>][title]' value='<?php echo $title; ?>' placeholder='Title'><br>
            <input type='text' name='blocks[<?php echo $index; ?>][style6]' value='<?php echo $titleFontSize; ?>' placeholder='Title Font Size'><br>
            <input type='text' name='blocks[<?php echo $index; ?>][style7]' value='<?php echo $titleColor; ?>' placeholder='Title Color'><br>
            <title>Text Alignment: </title>
            <select name='blocks[<?php echo $index; ?>][style8]'>
                <option value='left' <?php echo ($titleAlignment == 'left' ? 'selected' : ''); ?>>Left</option>
                <option value='center' <?php echo ($titleAlignment == 'center' ? 'selected' : ''); ?>>Center</option>
                <option value='right' <?php echo ($titleAlignment == 'right' ? 'selected' : ''); ?>>Right</option>
            </select><br>
            <textarea name='blocks[<?php echo $index; ?>][content]' placeholder="CTA Text Area"><?php echo $content; ?></textarea><br>
            <input type='text' name='blocks[<?php echo $index; ?>][style1]' value='<?php echo $textSize; ?>' placeholder='Text Size'><br>
            <input type='text' name='blocks[<?php echo $index; ?>][style2]' value='<?php echo $textColor; ?>' placeholder='Text Color'><br>
            <input type='text' name='blocks[<?php echo $index; ?>][style3]' value='<?php echo $backgroundColor; ?>' placeholder='Background Color'><br>
            <input type='text' name='blocks[<?php echo $index; ?>][style4]' value='<?php echo $topPadding; ?>' placeholder='Top Padding'><br>
            <input type='text' name='blocks[<?php echo $index; ?>][style5]' value='<?php echo $bottomPadding; ?>' placeholder='Bottom Padding'><br>
            <select name='blocks[<?php echo $index; ?>][image_path]'>
                <?php
                foreach ($uploads as $uploadFile) {
                    $selected = (isset($block['image_path']) && $uploadFile['url'] == $block['image_path']) ? 'selected' : '';
                    echo "<option value='{$uploadFile['url']}' {$selected}>{$uploadFile['url']}</option>";
                }
                ?>
            </select>
        </div>
        <?php
        break;

    case 'image':
        $title = isset($block['title']) ? htmlspecialchars($block['title']) : '';
        $titleFontSize = isset($block['style6']) ? htmlspecialchars($block['style6']) : '';
        $titleColor = isset($block['style7']) ? htmlspecialchars($block['style7']) : '';
        $titleAlignment = isset($block['style8']) ? htmlspecialchars($block['style8']) : '';
        $backgroundColor = isset($block['background_color']) ? htmlspecialchars($block['background_color']) : '';
        $topPadding = isset($block['style4']) ? htmlspecialchars($block['style4']) : '';
        $bottomPadding = isset($block['style5']) ? htmlspecialchars($block['style5']) : '';

        ?>
        <div class="block-wrapper">
            <input type='text' name='blocks[<?php echo $index; ?>][title]' value='<?php echo $title; ?>' placeholder='Title'><br>
            <input type='text' name='blocks[<?php echo $index; ?>][style6]' value='<?php echo $titleFontSize; ?>' placeholder='Title Font Size'><br>
            <input type='text' name='blocks[<?php echo $index; ?>][style7]' value='<?php echo $titleColor; ?>' placeholder='Title Color'><br>
            <title>Text Alignment: </title>
            <select name='blocks[<?php echo $index; ?>][style8]'>
                <option value='left' <?php echo ($titleAlignment == 'left' ? 'selected' : ''); ?>>Left</option>
                <option value='center' <?php echo ($titleAlignment == 'center' ? 'selected' : ''); ?>>Center</option>
                <option value='right' <?php echo ($titleAlignment == 'right' ? 'selected' : ''); ?>>Right</option>
            </select><br>
            <select name='blocks[<?php echo $index; ?>][image_path]'>
                <?php
                foreach ($uploads as $uploadFile) {
                    $selected = (isset($block['image_path']) && $uploadFile['url'] == $block['image_path']) ? 'selected' : '';
                    echo "<option value='{$uploadFile['url']}' {$selected}>{$uploadFile['url']}</option>";
                }
                ?>
            </select>
            <input type='text' name='blocks[<?php echo $index; ?>][style3]' value='<?php echo $backgroundColor; ?>' placeholder='Background Color'><br>
            <input type='text' name='blocks[<?php echo $index; ?>][style4]' value='<?php echo $topPadding; ?>' placeholder='Top Padding'><br>
            <input type='text' name='blocks[<?php echo $index; ?>][style5]' value='<?php echo $bottomPadding; ?>' placeholder='Bottom Padding'><br>
        </div>
        <?php
        break;

    case 'cta':
        $title = isset($block['title']) ? htmlspecialchars($block['title']) : '';
        $titleFontSize = isset($block['style6']) ? htmlspecialchars($block['style6']) : '';
        $titleColor = isset($block['style7']) ? htmlspecialchars($block['style7']) : '';
        $titleAlignment = isset($block['style8']) ? htmlspecialchars($block['style8']) : '';
        $content = isset($block['content']) ? htmlspecialchars($block['content']) : '';
        $textSize = isset($block['style1']) ? htmlspecialchars($block['style1']) : '';
        $textColor = isset($block['style2']) ? htmlspecialchars($block['style2']) : '';
        $backgroundColor = isset($block['background_color']) ? htmlspecialchars($block['background_color']) : '';
        $topPadding = isset($block['style4']) ? htmlspecialchars($block['style4']) : '';
        $bottomPadding = isset($block['style5']) ? htmlspecialchars($block['style5']) : '';
        $ctaText1 = isset($block['cta_text1']) ? htmlspecialchars($block['cta_text1']) : '';
        $url1 = isset($block['url1']) ? htmlspecialchars($block['url1']) : '';
        $ctaText2 = isset($block['cta_text2']) ? htmlspecialchars($block['cta_text2']) : '';
        $url2 = isset($block['url2']) ? htmlspecialchars($block['url2']) : '';

        ?>
        <div class="block-wrapper">
            <input type='text' name='blocks[<?php echo $index; ?>][title]' value='<?php echo $title; ?>' placeholder='Title'><br>
            <input type='text' name='blocks[<?php echo $index; ?>][style6]' value='<?php echo $titleFontSize; ?>' placeholder='Title Font Size'><br>
            <input type='text' name='blocks[<?php echo $index; ?>][style7]' value='<?php echo $titleColor; ?>' placeholder='Title Color'><br>
            <title>Text Alignment: </title>
            <select name='blocks[<?php echo $index; ?>][style8]'>
                <option value='left' <?php echo ($titleAlignment == 'left' ? 'selected' : ''); ?>>Left</option>
                <option value='center' <?php echo ($titleAlignment == 'center' ? 'selected' : ''); ?>>Center</option>
                <option value='right' <?php echo ($titleAlignment == 'right' ? 'selected' : ''); ?>>Right</option>
            </select><br>
            <input type='text' name='blocks[<?php echo $index; ?>][cta_text1]' value='<?php echo $ctaText1; ?>' placeholder="CTA Text 1"><br>
            <input type='url' name='blocks[<?php echo $index; ?>][url1]' value='<?php echo $url1; ?>' placeholder="URL 1"><br>
            <input type='text' name='blocks[<?php echo $index; ?>][cta_text2]' value='<?php echo $ctaText2; ?>' placeholder="CTA Text 2"><br>
            <input type='url' name='blocks[<?php echo $index; ?>][url2]' value='<?php echo $url2; ?>' placeholder="URL 2"><br>
            <textarea name='blocks[<?php echo $index; ?>][content]' placeholder="CTA Text Area"><?php echo $content; ?></textarea><br>
            <input type='text' name='blocks[<?php echo $index; ?>][style1]' value='<?php echo $textSize; ?>' placeholder='Text Size'><br>
            <input type='text' name='blocks[<?php echo $index; ?>][style2]' value='<?php echo $textColor; ?>' placeholder='Text Color'><br>
            <input type='text' name='blocks[<?php echo $index; ?>][style3]' value='<?php echo $backgroundColor; ?>' placeholder='Background Color'><br>
            <input type='text' name='blocks[<?php echo $index; ?>][style4]' value='<?php echo $topPadding; ?>' placeholder='Top Padding'><br>
            <input type='text' name='blocks[<?php echo $index; ?>][style5]' value='<?php echo $bottomPadding; ?>' placeholder='Bottom Padding'><br>
        </div>
        <?php
        break;

    default:
        echo "Unknown block type";
}

?>

<style>
    /* !!! REMOVE/MOVE */
    .block-wrapper {
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
    }

    title {
        display: inline-block;
    }
</style>
<figure>
    <img src="<?= isset($block['image_path']) ? htmlspecialchars($block['image_path'], ENT_QUOTES, 'UTF-8') : '' ?>" alt="Image Text Block">
    <figcaption><?= isset($blockContent) ? nl2br(htmlspecialchars($blockContent, ENT_QUOTES, 'UTF-8')) : '' ?></figcaption>
</figure>
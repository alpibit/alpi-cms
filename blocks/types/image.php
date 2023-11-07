<?php if (isset($block['image_path'])) : ?>
    <img src="<?= htmlspecialchars($block['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Image Block">
<?php endif; ?>
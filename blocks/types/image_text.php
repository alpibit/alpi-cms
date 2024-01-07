<?php if (!empty($blockImagePath) || !empty($blockContent)) : ?>
    <figure class="image-text-block">
        <?php if (!empty($blockImagePath)) : ?>
            <img src="<?= htmlspecialchars($blockImagePath, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($blockAltText ?? 'Image') ?>">
        <?php endif; ?>
        <?php if (!empty($blockContent)) : ?>
            <figcaption><?= nl2br(htmlspecialchars($blockContent, ENT_QUOTES, 'UTF-8')) ?></figcaption>
        <?php endif; ?>
    </figure>
    <style>
        .image-text-block {
            margin-bottom: 20px;
            text-align: center;
        }

        .image-text-block img {
            max-width: 100%;
            height: auto;
        }

        .image-text-block figcaption {
            margin-top: 10px;
        }
    </style>
<?php endif; ?>
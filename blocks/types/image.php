<?php if (!empty($blockImagePath)) : ?>
    <?php
    ?>
    <div class="image-block" style="<?= !empty($blockBackgroundColor) ? "background-color: " . htmlspecialchars($blockBackgroundColor) . ";" : "" ?>">
        <img src="<?= htmlspecialchars($blockImagePath, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($blockAltText ?? 'Image') ?>">
    </div>

    <style>
        .image-block {
            margin-bottom: 20px;
            text-align: center;
            padding: 10px;
        }

        .image-block img {
            max-width: 100%;
            height: auto;
        }
    </style>
<?php endif; ?>
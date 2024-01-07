<?php if (!empty($blockContent) || !empty($blockTitle)) : ?>
    <div class="text-block" style=" <?= !empty($blockTextSize) ? "font-size: " . htmlspecialchars($blockTextSize) . ";" : "" ?>
        <?= !empty($blockTextColor) ? "color: " . htmlspecialchars($blockTextColor) . ";" : "" ?>
        <?= !empty($blockBackgroundColor) ? "background-color: " . htmlspecialchars($blockBackgroundColor) . ";" : "" ?>
        <?= !empty($blockTopPadding) ? "padding-top: " . htmlspecialchars($blockTopPadding) . ";" : "" ?>
        <?= !empty($blockBottomPadding) ? "padding-bottom: " . htmlspecialchars($blockBottomPadding) . ";" : "" ?>
        <?= !empty($blockTitleAlignment) ? "text-align: " . htmlspecialchars($blockTitleAlignment) . ";" : "" ?>
    ">
        <?php if (!empty($blockTitle)) : ?>
            <h2 style=" <?= !empty($blockTitleFontSize) ? "font-size: " . htmlspecialchars($blockTitleFontSize) . ";" : "" ?>
                <?= !empty($blockTitleColor) ? "color: " . htmlspecialchars($blockTitleColor) . ";" : "" ?>
            ">
                <?= htmlspecialchars($blockTitle, ENT_QUOTES, 'UTF-8') ?>
            </h2>
        <?php endif; ?>
        <p><?= nl2br(htmlspecialchars($blockContent, ENT_QUOTES, 'UTF-8')) ?></p>
    </div>

    <style>
        .text-block {
            margin-bottom: 20px;
            padding: 10px;
        }

        .text-block h2 {
            margin-bottom: 10px;
        }

        .text-block p {
            margin-top: 0;
        }
    </style>
<?php endif; ?>
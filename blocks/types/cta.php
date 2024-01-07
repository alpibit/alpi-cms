<div class="cta-block" style="<?= !empty($blockBackgroundColor) ? "background-color: " . htmlspecialchars($blockBackgroundColor) . ";" : "" ?>
    <?= !empty($blockTopPadding) ? "padding-top: " . htmlspecialchars($blockTopPadding) . ";" : "" ?>
    <?= !empty($blockBottomPadding) ? "padding-bottom: " . htmlspecialchars($blockBottomPadding) . ";" : "" ?>
">
    <?php if (!empty($blockUrl1) && !empty($blockCtaText1)) : ?>
        <a href="<?= htmlspecialchars($blockUrl1, ENT_QUOTES, 'UTF-8') ?>" class="cta-button">
            <?= htmlspecialchars($blockCtaText1, ENT_QUOTES, 'UTF-8') ?>
        </a>
    <?php endif; ?>
    <?php if (!empty($blockUrl2) && !empty($blockCtaText2)) : ?>
        <a href="<?= htmlspecialchars($blockUrl2, ENT_QUOTES, 'UTF-8') ?>" class="cta-button">
            <?= htmlspecialchars($blockCtaText2, ENT_QUOTES, 'UTF-8') ?>
        </a>
    <?php endif; ?>
</div>


<style>
    .cta-block {
        text-align: center;
        margin-bottom: 20px;
        padding: 10px;
    }

    .cta-block .cta-button {
        display: inline-block;
        margin: 5px;
        padding: 10px 20px;
        background-color: #007bff;
        color: #fff;
        text-decoration: none;
        border-radius: 5px;
        border: 1px solid #007bff;
    }
</style>
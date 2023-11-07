<div class="cta-block">
    <a href="<?= isset($block['link']) ? htmlspecialchars($block['link'], ENT_QUOTES, 'UTF-8') : '#' ?>" class="cta-button">
        <?= isset($blockContent) ? htmlspecialchars($blockContent, ENT_QUOTES, 'UTF-8') : 'Click Here' ?>
    </a>
</div>
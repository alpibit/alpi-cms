<div class="cta-block">
    <a href="<?= isset($block['url']) ? htmlspecialchars($block['url'], ENT_QUOTES, 'UTF-8') : '#' ?>" class="cta-button">
        <?= isset($block['cta_text']) ? htmlspecialchars($block['cta_text'], ENT_QUOTES, 'UTF-8') : 'Click Here' ?>
    </a>
</div>

<!-- !!! REMOVE IT LATER -->
<style>
    .cta-block {
        text-align: center;
    }

    .cta-button {
        display: inline-block;
        padding: 10px 20px;
        background-color: #fff;
        color: #000;
        text-decoration: none;
        border-radius: 5px;
        border: 1px solid #000;
    }
</style>
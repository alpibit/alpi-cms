<?php
$footer_text = htmlspecialchars($settings->getSetting('footer_text'), ENT_QUOTES, 'UTF-8');
?>

<footer class="footer-wrap">
    <p><?= $footer_text ?></p>
</footer>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>

</html>

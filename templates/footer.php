<?php
$settings = new Settings($dbInstance->connect());
$footer_text = $settings->getSetting('footer_text');
?>

<footer class="footer-wrap">
    <p><?= htmlspecialchars($footer_text, ENT_QUOTES, 'UTF-8') ?></p>
</footer>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>

</html>
<?php

// Admin Footer

$settingsAdminFooter = new Settings($conn);
$adminFooterText = $settingsAdminFooter->getSetting('footer_text');
?>

<footer class="admin-footer">
    <p><?= htmlspecialchars($adminFooterText, ENT_QUOTES, 'UTF-8') ?></p>
</footer>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>

</html>
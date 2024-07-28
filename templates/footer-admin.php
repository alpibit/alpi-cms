<?php

// Admin Footer

$settingsAdminFooter = new Settings($conn);
$adminFooterText = $settingsAdminFooter->getSetting('footer_text');
?>

</div>
</main>
<footer class="alpi-admin-footer">
    <div class="alpi-container">
        <p><?= htmlspecialchars($adminFooterText, ENT_QUOTES, 'UTF-8') ?></p>
    </div>
</footer>

<script src="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/assets/js/main.js"></script>
</body>

</html>
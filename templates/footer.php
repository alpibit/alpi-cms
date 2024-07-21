<footer class="footer-wrap">
    <p><?= $footerText ?></p>
</footer>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<!-- Dynamically injected JS files -->
<?php echo $assetManager->getJsLinks(); ?>
<?= $footerScripts ?>
</body>

</html>
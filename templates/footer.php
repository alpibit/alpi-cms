<footer class="footer-wrap">
    <p><?= $footerText ?></p>
</footer>

<script>
    const BASE_URL = "<?= BASE_URL ?>";
</script>
<script src="<?= BASE_URL ?>/assets/js/live-search.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<!-- Dynamically injected JS files -->
<?php echo $assetManager->getJsLinks(); ?>
<?= $footerScripts ?>
</body>

</html>
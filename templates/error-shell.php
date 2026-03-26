<?php
$alpiErrorPageTitle = htmlspecialchars($pageTitle ?? 'Temporary issue', ENT_QUOTES, 'UTF-8');
$alpiErrorTitle = htmlspecialchars($title ?? 'We could not load this page right now.', ENT_QUOTES, 'UTF-8');
$alpiErrorMessage = htmlspecialchars($message ?? 'Please try again in a moment.', ENT_QUOTES, 'UTF-8');
$alpiErrorEyebrow = htmlspecialchars($eyebrow ?? 'Temporary issue', ENT_QUOTES, 'UTF-8');
$alpiErrorCode = isset($errorCode) && is_string($errorCode) && trim($errorCode) !== ''
    ? htmlspecialchars($errorCode, ENT_QUOTES, 'UTF-8')
    : '';
$alpiBaseUrl = defined('BASE_URL') ? BASE_URL : '/';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $alpiErrorPageTitle ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars($alpiBaseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/css/error-page.css">
</head>

<body>
    <main class="alpi-error-page">
        <div class="alpi-error-orb alpi-error-orb-one" aria-hidden="true"></div>
        <div class="alpi-error-orb alpi-error-orb-two" aria-hidden="true"></div>

        <section class="alpi-error-card" role="alert" aria-live="polite">
            <p class="alpi-error-eyebrow"><?= $alpiErrorEyebrow ?></p>
            <h1 class="alpi-error-title"><?= $alpiErrorTitle ?></h1>
            <p class="alpi-error-text"><?= $alpiErrorMessage ?></p>

            <?php if ($alpiErrorCode !== '') : ?>
                <div class="alpi-error-meta">
                    <span class="alpi-error-code"><?= $alpiErrorCode ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($actions)) : ?>
                <div class="alpi-error-actions">
                    <?php foreach ($actions as $action) : ?>
                        <?php
                        $alpiActionLabel = htmlspecialchars($action['label'], ENT_QUOTES, 'UTF-8');
                        $alpiActionVariant = $action['variant'] === 'secondary' ? 'secondary' : 'primary';
                        $alpiActionClass = 'alpi-error-button alpi-error-button-' . $alpiActionVariant;
                        ?>
                        <?php if (!empty($action['isButton'])) : ?>
                            <button type="button" class="<?= $alpiActionClass ?>" onclick="window.location.reload()">
                                <?= $alpiActionLabel ?>
                            </button>
                        <?php else : ?>
                            <a class="<?= $alpiActionClass ?>" href="<?= htmlspecialchars($action['href'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= $alpiActionLabel ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>

</html>
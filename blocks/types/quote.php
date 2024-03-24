<div class="quotes-block">
    <?php
    $blockQuotesData = json_decode($block['quotes_data'] ?? '[]', true);
    ?>
    <?php foreach ($blockQuotesData as $quoteIndex => $quote): ?>
        <div class="quote-item">
            <p class="quote-content">"<?= htmlspecialchars($quote['content'], ENT_QUOTES, 'UTF-8') ?>"</p>
            <p class="quote-author">- <?= htmlspecialchars($quote['author'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    <?php endforeach; ?>
</div>

<style>
    .quotes-block {
        margin-bottom: 20px;
        font-family: Arial, sans-serif;
    }

    .quote-item {
        margin-bottom: 15px;
        padding: 10px;
        border-left: 3px solid #007bff;
        background-color: #f8f9fa;
    }

    .quote-content {
        font-style: italic;
        color: #333;
    }

    .quote-author {
        text-align: right;
        margin-top: 10px;
        font-weight: bold;
        color: #007bff;
    }
</style>

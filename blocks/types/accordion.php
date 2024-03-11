<div class="accordion-block">
    <?php
    $blockAccordionData = json_decode($block['accordion_data'] ?? '[]', true);
    $accordionId = uniqid("accordion_");
    ?>
    <?php foreach ($blockAccordionData as $sectionIndex => $section): ?>
        <div class="accordion-item">
            <button class="accordion-toggle" id="accordion-toggle-<?= $accordionId ?>-<?= $sectionIndex ?>" onclick="toggleAccordion('<?= $accordionId ?>', <?= $sectionIndex ?>)">
                <?= htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8') ?>
            </button>
            <div class="accordion-content" id="accordion-content-<?= $accordionId ?>-<?= $sectionIndex ?>" style="display: none;">
                <p><?= htmlspecialchars($section['content'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
    function toggleAccordion(accordionId, index) {
        var content = document.getElementById("accordion-content-" + accordionId + "-" + index);
        if (content.style.display === "block") {
            content.style.display = "none";
        } else {
            content.style.display = "block";
        }
    }
</script>

<style>
    .accordion-block {
        margin-bottom: 20px;
    }

    .accordion-item {
        margin-bottom: 10px;
    }

    .accordion-toggle {
        background-color: #007bff;
        color: white;
        cursor: pointer;
        padding: 18px;
        width: 100%;
        border: none;
        text-align: left;
        outline: none;
        font-size: 15px;
        transition: 0.4s;
    }

    .accordion-toggle:hover {
        background-color: #0056b3;
    }

    .accordion-content {
        padding: 0 18px;
        background-color: white;
        display: none;
        overflow: hidden;
        transition: max-height 0.2s ease-out;
        border: 1px solid #ccc;
    }

    .accordion-content p {
        margin: 10px 0;
    }
</style>

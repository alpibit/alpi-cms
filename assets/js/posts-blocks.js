function addBlock() {
    const index = document.getElementById('contentBlocks').childElementCount;
    const blockHTML = `
        <div class="block">
            <label>Type:</label>
            <select name="blocks[${index}][type]" onchange="loadBlockContent(this, ${index})">
                <option value="text">Text</option>
                <option value="image_text">Image Text</option>
                <option value="image">Image</option>
                <option value="cta">CTA</option>
            </select><br>
            <div class="block-content"></div>
            <div class="buttons">
                <button type="button" onclick="moveUp(this)">Move Up</button>
                <button type="button" onclick="moveDown(this)">Move Down</button>
                <button type="button" onclick="deleteBlock(this)">Delete</button>
            </div>
            <br>
        </div>
    `;
    document.getElementById('contentBlocks').insertAdjacentHTML('beforeend', blockHTML);
    updateButtons();
}

function deleteBlock(button) {
    const block = button.closest('div.block');
    block.remove();
    updateButtons();
}

function moveUp(button) {
    const block = button.closest('div.block');
    const prevBlock = block.previousElementSibling;
    if (prevBlock) {
        block.parentNode.insertBefore(block, prevBlock);
        updateButtons();
    }
}

function moveDown(button) {
    const block = button.closest('div.block');
    const nextBlock = block.nextElementSibling;
    if (nextBlock) {
        block.parentNode.insertBefore(nextBlock, block);
        updateButtons();
    }
}

function updateButtons() {
    const blocks = document.querySelectorAll('#contentBlocks .block');
    blocks.forEach((block, index) => {
        const buttonsDiv = block.querySelector('.buttons');
        buttonsDiv.innerHTML = '';

        if (index > 0) {
            const moveUpButton = createButton('Move Up', () => moveUp(block));
            buttonsDiv.appendChild(moveUpButton);
        }

        if (index < blocks.length - 1) {
            const moveDownButton = createButton('Move Down', () => moveDown(block));
            buttonsDiv.appendChild(moveDownButton);
        }

        const deleteButton = createButton('Delete', () => deleteBlock(block));
        buttonsDiv.appendChild(deleteButton);
    });
}

function loadBlockContent(selectElement, index) {
    const type = selectElement.value;
    const contentDiv = selectElement.closest('.block').querySelector('.block-content');

    fetch(`../../../blocks/render-block-content.php?type=${type}&index=${index}`)
        .then(response => response.text())
        .then(html => contentDiv.innerHTML = html)
        .catch(error => console.error('Error:', error));
}

function createButton(text, onClick) {
    const button = document.createElement('button');
    button.type = 'button';
    button.textContent = text;
    button.onclick = onClick;
    return button;
}

// Initialize blocks on page load
window.onload = function () {
    updateButtons();
    document.querySelectorAll('#contentBlocks .block select').forEach(select => {
        loadBlockContent(select, parseInt(select.name.match(/\d+/)[0], 10));
    });
};
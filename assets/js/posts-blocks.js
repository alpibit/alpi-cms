function addBlock() {
    var index = document.getElementById('contentBlocks').childElementCount;
    var blockHTML = `
        <div class="block">
            <label>Type:</label>
            <select name="blocks[${index}][type]">
                <option value="text">Text</option>
                <option value="image_text">Image Text</option>
                <option value="image">Image</option>
                <option value="cta">CTA</option>
            </select><br>
            <textarea name="blocks[${index}][content]"></textarea><br>
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
    var block = button.closest('div.block');
    block.remove();
    updateButtons();
}

function moveUp(button) {
    var block = button.closest('div.block');
    var prevBlock = block.previousElementSibling;
    if (prevBlock) {
        block.parentNode.insertBefore(block, prevBlock);
        updateButtons();
    }
}

function moveDown(button) {
    var block = button.closest('div.block');
    var nextBlock = block.nextElementSibling;
    if (nextBlock) {
        block.parentNode.insertBefore(nextBlock, block);
        updateButtons();
    }
}

function updateButtons() {
    var blocks = document.querySelectorAll('#contentBlocks .block');
    blocks.forEach(function (block, index) {
        var buttonsDiv = block.querySelector('.buttons');
        buttonsDiv.innerHTML = ''; // Clear the current buttons

        if (index > 0) {
            var moveUpButton = document.createElement('button');
            moveUpButton.type = 'button';
            moveUpButton.textContent = 'Move Up';
            moveUpButton.onclick = function () {
                moveUp(this);
            };
            buttonsDiv.appendChild(moveUpButton);
        }

        if (index < blocks.length - 1) {
            var moveDownButton = document.createElement('button');
            moveDownButton.type = 'button';
            moveDownButton.textContent = 'Move Down';
            moveDownButton.onclick = function () {
                moveDown(this);
            };
            buttonsDiv.appendChild(moveDownButton);
        }

        var deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.textContent = 'Delete';
        deleteButton.onclick = function () {
            deleteBlock(this);
        };
        buttonsDiv.appendChild(deleteButton);
    });
}

window.onload = updateButtons;
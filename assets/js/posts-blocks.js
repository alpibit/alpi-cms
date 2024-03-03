function addBlock() {
    const index = document.getElementById('contentBlocks').childElementCount;
    const blockHTML = `
        <div class="block">
            <label>Type:</label>
            <select name="blocks[${index}][type]" onchange="loadBlockContent(this, ${index})">
                <option value="text">Text</option>
                <option value="image_text">Image + Text</option>
                <option value="image">Image</option>
                <option value="cta">Call to Action (CTA)</option>
                <option value="post_picker">Post Picker</option>
                <option value="video">Video</option>
                <option value="slider_gallery">Slider Gallery</option>
                <option value="quote">Quote</option>
                <option value="accordion">Accordion</option>
                <option value="audio">Audio</option>
                <option value="free_code">Free Code</option>
                <option value="map">Map</option>
                <option value="form">Form</option>
                <option value="hero">Hero</option>
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
    const blockData = JSON.parse(contentDiv.getAttribute('data-value') || '{}');
    const blockDataString = JSON.stringify(blockData);
    console.log(blockDataString);

    fetch(`../../../blocks/render-block-content.php?type=${type}&index=${index}&blockData=${encodeURIComponent(blockDataString)}`)
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





function toggleSourceField(selector, type) {
    const block = selector.closest('.block');
    const urlField = block.querySelector('.' + type + '-url-field');
    const uploadField = block.querySelector('.' + type + '-upload-field');
    const value = selector.value;

    // const blockData = JSON.parse(block.querySelector('.block-content').getAttribute('data-value') || '{}');
    // const uploadValue = blockData[type + '_url'] || '';
    // console.log("UPLOAD VALUE:" + uploadValue);
    // if (uploadField) {
    //     const select = uploadField.querySelector('select');
    //     console.log("SELECTOR:" + select);
    //     if (select) {
    //         select.value = uploadValue;
    //     }
    // }


    // console.log(blockData);

    if (urlField && uploadField) {
        if (value === 'url') {
            urlField.style.display = 'block';
            uploadField.style.display = 'none';
        } else if (value === 'upload') {
            urlField.style.display = 'none';
            uploadField.style.display = 'block';
        }
    }
}

function initializeSourceFields() {
    document.querySelectorAll('.block').forEach(block => {
        const videoSelector = block.querySelector('.video-source-selector');
        const audioSelector = block.querySelector('.audio-source-selector');

        if (videoSelector) {
            toggleSourceField(videoSelector, 'video');
            videoSelector.addEventListener('change', () => toggleSourceField(videoSelector, 'video'));
        }

        if (audioSelector) {
            toggleSourceField(audioSelector, 'audio');
            audioSelector.addEventListener('change', () => toggleSourceField(audioSelector, 'audio'));
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    setTimeout(initializeSourceFields, 500);
});
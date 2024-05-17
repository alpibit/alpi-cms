document.addEventListener('DOMContentLoaded', function () {
    initializeBlocks();
    initializeMediaSourceSelectors();
});

function initializeBlocks() {
    updateButtonsBlock();
    document.querySelectorAll('#contentBlocks .block select').forEach(select => {
        loadSelectedBlockContent(select, parseInt(select.name.match(/\d+/)[0], 10));
    });
}

function addBlock() {
    const index = document.getElementById('contentBlocks').childElementCount;
    const blockHTML = getBlockHTML(index);
    document.getElementById('contentBlocks').insertAdjacentHTML('beforeend', blockHTML);
    updateButtonsBlock();
}

function getBlockHTML(index) {
    return `
        <div class="block" data-index="${index}">
            <label>Type:</label>
            <select name="blocks[${index}][type]" onchange="loadSelectedBlockContent(this, ${index})">
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
            <div class="block-content" data-value="{}"></div>
            <div class="buttons">
                ${getBlockControlButtons(index)}
            </div>
            <br>
        </div>
    `;
}

function getBlockControlButtons(index) {
    return `
        <button type="button" onclick="shiftBlockUpward(this)">Move Up</button>
        <button type="button" onclick="shiftBlockDownward(this)">Move Down</button>
        <button type="button" onclick="removeContentBlock(this)">Delete</button>
    `;
}

function removeContentBlock(button) {
    const block = button.closest('.block');
    block.remove();
    updateButtonsBlock();
}

function shiftBlockUpward(button) {
    const block = button.closest('.block');
    const prevBlock = block.previousElementSibling;
    if (prevBlock) {
        block.parentNode.insertBefore(block, prevBlock);
        updateButtonsBlock();
    }
}

function shiftBlockDownward(button) {
    const block = button.closest('.block');
    const nextBlock = block.nextElementSibling;
    if (nextBlock) {
        block.parentNode.insertBefore(nextBlock, block);
        updateButtonsBlock();
    }
}

function updateButtonsBlock() {
    const blocks = document.querySelectorAll('#contentBlocks .block');
    blocks.forEach((block, index) => {
        const buttonsDiv = block.querySelector('.buttons');
        buttonsDiv.innerHTML = '';

        if (index > 0) {
            const moveUpButton = generateControlButton('Move Up', () => shiftBlockUpward(block));
            buttonsDiv.appendChild(moveUpButton);
        }

        if (index < blocks.length - 1) {
            const moveDownButton = generateControlButton('Move Down', () => shiftBlockDownward(block));
            buttonsDiv.appendChild(moveDownButton);
        }

        const deleteButton = generateControlButton('Delete', () => removeContentBlock(block));
        buttonsDiv.appendChild(deleteButton);
    });
}

function generateControlButton(text, onClick) {
    const button = document.createElement('button');
    button.type = 'button';
    button.textContent = text;
    button.onclick = onClick;
    return button;
}

function loadSelectedBlockContent(selectElement, index) {
    const type = selectElement.value;
    const contentDiv = selectElement.closest('.block').querySelector('.block-content');
    const blockData = JSON.parse(contentDiv.getAttribute('data-value') || '{}');
    const blockDataString = JSON.stringify(blockData);

    fetch(`../../../blocks/render-block-content.php?type=${type}&index=${index}&blockData=${encodeURIComponent(blockDataString)}`)
        .then(response => response.text())
        .then(html => contentDiv.innerHTML = html)
        .catch(error => console.error('Error:', error));
}

function toggleSourceField(selector, type) {
    const block = selector.closest('.block');
    const urlField = block.querySelector(`.${type}-url-field`);
    const uploadField = block.querySelector(`.${type}-upload-field`);
    const value = selector.value;

    if (urlField && uploadField) {
        urlField.style.display = value === 'url' ? 'block' : 'none';
        uploadField.style.display = value === 'upload' ? 'block' : 'none';
    }
}

function initializeMediaSourceSelectors() {
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

function addGalleryImage(blockIndex) {
    fetch('../../../utils/get-uploads.php')
        .then(response => response.json())
        .then(data => {
            if (data && data.uploads) {
                const block = document.querySelector(`.block[data-index="${blockIndex}"] .slider-gallery`);
                if (!block) {
                    console.error('Block not found');
                    return;
                }
                const newIndex = block.querySelectorAll('.gallery-image').length;

                const newImageHtml = getGalleryImageHTML(blockIndex, newIndex, data.uploads);
                block.insertAdjacentHTML('beforeend', newImageHtml);
                updateButtonsImage();
            } else {
                console.error('No upload data found');
            }
        })
        .catch(error => console.error('Error loading uploads:', error));
}

function getGalleryImageHTML(blockIndex, newIndex, uploads) {
    return `
        <div class='gallery-image' data-index='${newIndex}'>
            <label>Image: <select name='blocks[${blockIndex}][gallery_data][${newIndex}][url]'>
                ${uploads.map(upload => `<option value='${upload.url}'>${upload.url}</option>`).join('')}
            </select></label><br>
            <label>Alt Text: <input type='text' name='blocks[${blockIndex}][gallery_data][${newIndex}][alt_text]' placeholder='Alt Text'></label><br>
            <label>Caption: <input type='text' name='blocks[${blockIndex}][gallery_data][${newIndex}][caption]' placeholder='Caption'></label><br>
            <div class="buttons">
                <button type='button' onclick='shiftImageUpward(this)'>Move Up</button>
                <button type='button' onclick='shiftImageDownward(this)'>Move Down</button>
                <button type='button' onclick='removeGalleryImage(${blockIndex}, ${newIndex})'>Delete Image</button>
            </div>
        </div>
    `;
}

function removeGalleryImage(blockIndex, imageIndex) {
    const block = document.querySelector(`.block[data-index="${blockIndex}"] .slider-gallery`);
    const image = block.querySelector(`.gallery-image[data-index="${imageIndex}"]`);
    if (image) {
        image.remove();
    }
}

function shiftImageUpward(button) {
    const image = button.closest('.gallery-image');
    const prevImage = image.previousElementSibling;
    if (prevImage) {
        image.parentNode.insertBefore(image, prevImage);
        updateButtonsImage();
    }
}

function shiftImageDownward(button) {
    const image = button.closest('.gallery-image');
    const nextImage = image.nextElementSibling;
    if (nextImage) {
        image.parentNode.insertBefore(nextImage, image);
        updateButtonsImage();
    }
}

function updateButtonsImage() {
    const images = document.querySelectorAll('.gallery-image');
    images.forEach((image, index) => {
        const buttonsDiv = image.querySelector('.buttons');
        buttonsDiv.innerHTML = '';

        if (index > 0) {
            const moveUpButton = generateControlButton('Move Up', () => shiftImageUpward(image));
            buttonsDiv.appendChild(moveUpButton);
        }

        if (index < images.length - 1) {
            const moveDownButton = generateControlButton('Move Down', () => shiftImageDownward(image));
            buttonsDiv.appendChild(moveDownButton);
        }

        const deleteButton = generateControlButton('Delete', () => removeGalleryImage(image));
        buttonsDiv.appendChild(deleteButton);
    });
}

function insertAccordionSection(blockIndex) {
    const block = getBlockByIndex(blockIndex);
    const blockContent = block.querySelector('.block-content');

    const newIndex = blockContent.querySelectorAll('.accordion-section').length;
    const newSectionHtml = getAccordionSectionHTML(blockIndex, newIndex);
    blockContent.insertAdjacentHTML('beforeend', newSectionHtml);
    updateButtonsAccordionSection();
}

function getBlockByIndex(blockIndex) {
    return document.querySelector(`#contentBlocks .block[data-index="${blockIndex}"]`);
}

function getAccordionSectionHTML(blockIndex, newIndex) {
    return `
        <div class='accordion-section' data-index='${newIndex}'>
            <label>Section Title: <input type='text' name='blocks[${blockIndex}][accordion_data][${newIndex}][title]' placeholder='Section Title'></label><br>
            <label>Section Content: <textarea name='blocks[${blockIndex}][accordion_data][${newIndex}][content]' placeholder='Section Content'></textarea></label><br>
            <div class="buttons">
                <button type="button" onclick="shiftAccordionSectionUp(this)">Move Up</button>
                <button type="button" onclick="shiftAccordionSectionDown(this)">Move Down</button>
                <button type="button" onclick="removeAccordionSection(this)">Delete</button>
            </div>
        </div>
    `;
}

function removeAccordionSection(button) {
    const section = button.closest('.accordion-section');
    section.remove();
    updateButtonsAccordionSection();
}

function shiftAccordionSectionUp(button) {
    const section = button.closest('.accordion-section');
    const prevSection = section.previousElementSibling;
    if (prevSection) {
        section.parentNode.insertBefore(section, prevSection);
        updateButtonsAccordionSection();
    }
}

function shiftAccordionSectionDown(button) {
    const section = button.closest('.accordion-section');
    const nextSection = section.nextElementSibling;
    if (nextSection) {
        section.parentNode.insertBefore(nextSection, section);
        updateButtonsAccordionSection();
    }
}

function updateButtonsAccordionSection() {
    const sections = document.querySelectorAll('.accordion-section');
    sections.forEach((section, index) => {
        const buttonsDiv = section.querySelector('.buttons');
        buttonsDiv.innerHTML = '';

        if (index > 0) {
            const moveUpButton = generateControlButton('Move Up', () => shiftAccordionSectionUp(section));
            buttonsDiv.appendChild(moveUpButton);
        }

        if (index < sections.length - 1) {
            const moveDownButton = generateControlButton('Move Down', () => shiftAccordionSectionDown(section));
            buttonsDiv.appendChild(moveDownButton);
        }

        const deleteButton = generateControlButton('Delete', () => removeAccordionSection(section));
        buttonsDiv.appendChild(deleteButton);
    });
}

function addQuote(blockIndex) {
    const block = getBlockByIndex(blockIndex);
    const blockContent = block.querySelector('.block-content');

    const newIndex = blockContent.querySelectorAll('.quote').length;
    const newQuoteHtml = getQuoteHTML(blockIndex, newIndex);
    blockContent.insertAdjacentHTML('beforeend', newQuoteHtml);
    updateButtonsQuote(blockIndex);
}

function getQuoteHTML(blockIndex, newIndex) {
    return `
        <div class='quote' data-index='${newIndex}'>
            <label>Quote Content: <textarea name='blocks[${blockIndex}][quotes_data][${newIndex}][content]' placeholder='Quote Content'></textarea></label><br>
            <label>Author: <input type='text' name='blocks[${blockIndex}][quotes_data][${newIndex}][author]' placeholder='Author'></label><br>
            <div class="buttons">
                <button type="button" onclick="shiftQuoteUpward(${blockIndex}, ${newIndex})">Move Up</button>
                <button type="button" onclick="shiftQuoteDownward(${blockIndex}, ${newIndex})">Move Down</button>
                <button type="button" onclick="removeQuote(${blockIndex}, ${newIndex})">Delete Quote</button>
            </div>
        </div>
    `;
}

function shiftQuoteUpward(blockIndex, quoteIndex) {
    const block = getBlockByIndex(blockIndex);
    const quotes = block.querySelectorAll('.quote');
    const quote = quotes[quoteIndex];
    const prevQuote = quote.previousElementSibling;
    if (prevQuote) {
        quote.parentNode.insertBefore(quote, prevQuote);
        updateButtonsQuote(blockIndex);
    }
}

function shiftQuoteDownward(blockIndex, quoteIndex) {
    const block = getBlockByIndex(blockIndex);
    const quotes = block.querySelectorAll('.quote');
    const quote = quotes[quoteIndex];
    const nextQuote = quote.nextElementSibling;
    if (nextQuote) {
        quote.parentNode.insertBefore(nextQuote, quote);
        updateButtonsQuote(blockIndex);
    }
}

function removeQuote(blockIndex, quoteIndex) {
    const block = getBlockByIndex(blockIndex);
    const quote = block.querySelector(`.quote[data-index="${quoteIndex}"]`);
    if (quote) {
        quote.remove();
    }
    updateButtonsQuote(blockIndex);
}

function updateButtonsQuote(blockIndex) {
    const block = getBlockByIndex(blockIndex);
    const quotes = block.querySelectorAll('.quote');
    quotes.forEach((quote, index) => {
        const buttonsDiv = quote.querySelector('.buttons');
        buttonsDiv.innerHTML = '';

        if (index > 0) {
            const moveUpButton = generateControlButton('Move Up', () => shiftQuoteUpward(blockIndex, index));
            buttonsDiv.appendChild(moveUpButton);
        }

        if (index < quotes.length - 1) {
            const moveDownButton = generateControlButton('Move Down', () => shiftQuoteDownward(blockIndex, index));
            buttonsDiv.appendChild(moveDownButton);
        }

        const deleteButton = generateControlButton('Delete', () => removeQuote(blockIndex, index));
        buttonsDiv.appendChild(deleteButton);
    });
}

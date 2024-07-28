// Main initialization
document.addEventListener('DOMContentLoaded', function () {
    initializeBlocks();
    initializeMediaSourceSelectors();
});

// Block management functions
function initializeBlocks() {
    updateButtonsBlock();
    document.querySelectorAll('#contentBlocks .alpi-block select').forEach(select => {
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
        <div class="alpi-block" data-index="${index}">
            <div class="alpi-form-group">
                <label class="alpi-form-label">Type:</label>
                <select class="alpi-form-input" name="blocks[${index}][type]" onchange="loadSelectedBlockContent(this, ${index})">
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
                </select>
            </div>
            <div class="alpi-block-content" data-value="{}"></div>
            <div class="alpi-btn-group">
                ${getBlockControlButtons(index)}
            </div>
        </div>
    `;
}

function getBlockControlButtons(index) {
    return `
        <button type="button" class="alpi-btn alpi-btn-secondary" onclick="shiftBlockUpward(this)">Move Up</button>
        <button type="button" class="alpi-btn alpi-btn-secondary" onclick="shiftBlockDownward(this)">Move Down</button>
        <button type="button" class="alpi-btn alpi-btn-danger" onclick="removeContentBlock(this)">Delete</button>
    `;
}

function removeContentBlock(button) {
    const block = button.closest('.alpi-block');
    block.remove();
    updateButtonsBlock();
}

function shiftBlockUpward(button) {
    const block = button.closest('.alpi-block');
    const prevBlock = block.previousElementSibling;
    if (prevBlock) {
        block.parentNode.insertBefore(block, prevBlock);
        updateButtonsBlock();
    }
}

function shiftBlockDownward(button) {
    const block = button.closest('.alpi-block');
    const nextBlock = block.nextElementSibling;
    if (nextBlock) {
        block.parentNode.insertBefore(nextBlock, block);
        updateButtonsBlock();
    }
}

function updateButtonsBlock() {
    const blocks = document.querySelectorAll('#contentBlocks .alpi-block');
    blocks.forEach((block, index) => {
        const buttonsDiv = block.querySelector('.alpi-btn-group');
        buttonsDiv.innerHTML = '';

        if (index > 0) {
            const moveUpButton = generateControlButton('Move Up', () => shiftBlockUpward(block), 'alpi-btn alpi-btn-secondary');
            buttonsDiv.appendChild(moveUpButton);
        }

        if (index < blocks.length - 1) {
            const moveDownButton = generateControlButton('Move Down', () => shiftBlockDownward(block), 'alpi-btn alpi-btn-secondary');
            buttonsDiv.appendChild(moveDownButton);
        }

        const deleteButton = generateControlButton('Delete', () => removeContentBlock(block), 'alpi-btn alpi-btn-danger');
        buttonsDiv.appendChild(deleteButton);
    });
}

function generateControlButton(text, onClick, className) {
    const button = document.createElement('button');
    button.type = 'button';
    button.textContent = text;
    button.className = className;
    button.onclick = onClick;
    return button;
}

// Content loading and population
function loadSelectedBlockContent(selectElement, index) {
    const type = selectElement.value;
    const contentDiv = selectElement.closest('.alpi-block').querySelector('.alpi-block-content');
    const blockData = JSON.parse(contentDiv.getAttribute('data-value') || '{}');
    console.log('Block Data:', blockData);

    fetch(`../../../blocks/render-block-content.php?type=${type}&index=${index}`)
        .then(response => response.text())
        .then(html => {
            contentDiv.innerHTML = html;
            populateBlockFields(contentDiv, blockData.block_data || {}, index);
        })
        .catch(error => console.error('Error:', error));
}

function populateBlockFields(container, data, index) {
    console.log('Populating fields with data:', data);
    Object.keys(data).forEach(key => {
        try {
            const input = container.querySelector(`[name="blocks[${index}][${key}]"]`);
            if (input) {
                console.log(`Setting ${key} to ${data[key]}`);
                if (input.type === 'checkbox') {
                    input.checked = !!data[key];
                } else if (input.type === 'select-multiple') {
                    const selectedValues = data[key].split(',');
                    Array.from(input.options).forEach(option => {
                        option.selected = selectedValues.includes(option.value);
                    });
                } else {
                    input.value = data[key];
                }
            } else if (key === 'accordion_data') {
                const accordionData = JSON.parse(data[key]);
                Object.values(accordionData).forEach((section, sectionIndex) => {
                    insertAccordionSection(index, section);
                });
            } else if (key === 'quotes_data') {
                const quotesData = JSON.parse(data[key]);
                quotesData.forEach((quote, quoteIndex) => {
                    addQuote(index, quote);
                });
            } else if (key === 'selected_post_ids') {
                const select = container.querySelector(`[name="blocks[${index}][${key}][]"]`);
                if (select) {
                    const selectedValues = data[key].split(',');
                    Array.from(select.options).forEach(option => {
                        option.selected = selectedValues.includes(option.value);
                    });
                }
            }
        } catch (error) {
            console.error(`Error populating field ${key}:`, error);
        }
    });
}

// Media source selectors
function toggleSourceField(selector, type) {
    const block = selector.closest('.alpi-block');
    const urlField = block.querySelector(`.${type}-url-field`);
    const uploadField = block.querySelector(`.${type}-upload-field`);
    const value = selector.value;

    if (urlField && uploadField) {
        urlField.style.display = value === 'url' ? 'block' : 'none';
        uploadField.style.display = value === 'upload' ? 'block' : 'none';
    }
}

function initializeMediaSourceSelectors() {
    document.querySelectorAll('.alpi-block').forEach(block => {
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

// Gallery functions
function addGalleryImage(blockIndex) {
    fetch('../../../utils/get-uploads.php')
        .then(response => response.json())
        .then(data => {
            if (data && data.uploads) {
                const block = document.querySelector(`.alpi-block[data-index="${blockIndex}"] .alpi-slider-gallery`);
                if (!block) {
                    console.error('Block not found');
                    return;
                }
                const newIndex = block.querySelectorAll('.alpi-gallery-image').length;

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
        <div class='alpi-gallery-image alpi-card alpi-mb-md' data-index='${newIndex}'>
            <div class="alpi-form-group">
                <label class="alpi-form-label">Image:</label>
                <select class="alpi-form-input" name='blocks[${blockIndex}][gallery_data][${newIndex}][url]'>
                    ${uploads.map(upload => `<option value='${upload.url}'>${upload.url}</option>`).join('')}
                </select>
            </div>
            <div class="alpi-form-group">
                <label class="alpi-form-label">Alt Text:</label>
                <input class="alpi-form-input" type='text' name='blocks[${blockIndex}][gallery_data][${newIndex}][alt_text]' placeholder='Alt Text'>
            </div>
            <div class="alpi-form-group">
                <label class="alpi-form-label">Caption:</label>
                <input class="alpi-form-input" type='text' name='blocks[${blockIndex}][gallery_data][${newIndex}][caption]' placeholder='Caption'>
            </div>
            <div class="alpi-btn-group">
                <button type='button' class="alpi-btn alpi-btn-secondary" onclick='shiftImageUpward(this)'>Move Up</button>
                <button type='button' class="alpi-btn alpi-btn-secondary" onclick='shiftImageDownward(this)'>Move Down</button>
                <button type='button' class="alpi-btn alpi-btn-danger" onclick='removeGalleryImage(${blockIndex}, ${newIndex})'>Delete Image</button>
            </div>
        </div>
    `;
}

function removeGalleryImage(blockIndex, imageIndex) {
    const block = document.querySelector(`.alpi-block[data-index="${blockIndex}"] .alpi-slider-gallery`);
    const image = block.querySelector(`.alpi-gallery-image[data-index="${imageIndex}"]`);
    if (image) {
        image.remove();
    }
}

function shiftImageUpward(button) {
    const image = button.closest('.alpi-gallery-image');
    const prevImage = image.previousElementSibling;
    if (prevImage) {
        image.parentNode.insertBefore(image, prevImage);
        updateButtonsImage();
    }
}

function shiftImageDownward(button) {
    const image = button.closest('.alpi-gallery-image');
    const nextImage = image.nextElementSibling;
    if (nextImage) {
        image.parentNode.insertBefore(nextImage, image);
        updateButtonsImage();
    }
}

function updateButtonsImage() {
    const images = document.querySelectorAll('.alpi-gallery-image');
    images.forEach((image, index) => {
        const buttonsDiv = image.querySelector('.alpi-btn-group');
        buttonsDiv.innerHTML = '';

        if (index > 0) {
            const moveUpButton = generateControlButton('Move Up', () => shiftImageUpward(image), 'alpi-btn alpi-btn-secondary');
            buttonsDiv.appendChild(moveUpButton);
        }

        if (index < images.length - 1) {
            const moveDownButton = generateControlButton('Move Down', () => shiftImageDownward(image), 'alpi-btn alpi-btn-secondary');
            buttonsDiv.appendChild(moveDownButton);
        }

        const deleteButton = generateControlButton('Delete', () => removeGalleryImage(image), 'alpi-btn alpi-btn-danger');
        buttonsDiv.appendChild(deleteButton);
    });
}

// Accordion functions
function insertAccordionSection(blockIndex, initialData = null) {
    const block = getBlockByIndex(blockIndex);
    const blockContent = block.querySelector('.alpi-block-content');

    const newIndex = blockContent.querySelectorAll('.alpi-accordion-section').length;
    const newSectionHtml = getAccordionSectionHTML(blockIndex, newIndex, initialData);
    blockContent.insertAdjacentHTML('beforeend', newSectionHtml);
    updateButtonsAccordionSection();
}

function getBlockByIndex(blockIndex) {
    return document.querySelector(`#contentBlocks .alpi-block[data-index="${blockIndex}"]`);
}

function getAccordionSectionHTML(blockIndex, newIndex, initialData = null) {
    const title = initialData ? initialData.title : '';
    const content = initialData ? initialData.content : '';
    return `
        <div class='alpi-accordion-section alpi-card alpi-mb-md' data-index='${newIndex}'>
            <div class="alpi-form-group">
                <label class="alpi-form-label">Section Title:</label>
                <input class="alpi-form-input" type='text' name='blocks[${blockIndex}][accordion_data][${newIndex}][title]' value="${title}" placeholder='Section Title'>
            </div>
            <div class="alpi-form-group">
                <label class="alpi-form-label">Section Content:</label>
                <textarea class="alpi-form-input" name='blocks[${blockIndex}][accordion_data][${newIndex}][content]' placeholder='Section Content'>${content}</textarea>
            </div>
            <div class="alpi-btn-group">
                <button type="button" class="alpi-btn alpi-btn-secondary" onclick="shiftAccordionSectionUp(this)">Move Up</button>
                <button type="button" class="alpi-btn alpi-btn-secondary" onclick="shiftAccordionSectionDown(this)">Move Down</button>
                <button type="button" class="alpi-btn alpi-btn-danger" onclick="removeAccordionSection(this)">Delete</button>
            </div>
        </div>
    `;
}

function removeAccordionSection(button) {
    const section = button.closest('.alpi-accordion-section');
    section.remove();
    updateButtonsAccordionSection();
}

function shiftAccordionSectionUp(button) {
    const section = button.closest('.alpi-accordion-section');
    const prevSection = section.previousElementSibling;
    if (prevSection) {
        section.parentNode.insertBefore(section, prevSection);
        updateButtonsAccordionSection();
    }
}

function shiftAccordionSectionDown(button) {
    const section = button.closest('.alpi-accordion-section');
    const nextSection = section.nextElementSibling;
    if (nextSection) {
        section.parentNode.insertBefore(nextSection, section);
        updateButtonsAccordionSection();
    }
}

function updateButtonsAccordionSection() {
    const sections = document.querySelectorAll('.alpi-accordion-section');
    sections.forEach((section, index) => {
        const buttonsDiv = section.querySelector('.alpi-btn-group');
        buttonsDiv.innerHTML = '';

        if (index > 0) {
            const moveUpButton = generateControlButton('Move Up', () => shiftAccordionSectionUp(section), 'alpi-btn alpi-btn-secondary');
            buttonsDiv.appendChild(moveUpButton);
        }

        if (index < sections.length - 1) {
            const moveDownButton = generateControlButton('Move Down', () => shiftAccordionSectionDown(section), 'alpi-btn alpi-btn-secondary');
            buttonsDiv.appendChild(moveDownButton);
        }

        const deleteButton = generateControlButton('Delete', () => removeAccordionSection(section), 'alpi-btn alpi-btn-danger');
        buttonsDiv.appendChild(deleteButton);
    });
}

// Quote functions
function addQuote(blockIndex, initialData = null) {
    const block = getBlockByIndex(blockIndex);
    const blockContent = block.querySelector('.alpi-block-content');

    const newIndex = blockContent.querySelectorAll('.alpi-quote').length;
    const newQuoteHtml = getQuoteHTML(blockIndex, newIndex, initialData);
    blockContent.insertAdjacentHTML('beforeend', newQuoteHtml);
    updateButtonsQuote(blockIndex);
}

function getQuoteHTML(blockIndex, newIndex, initialData = null) {
    const content = initialData ? initialData.content : '';
    const author = initialData ? initialData.author : '';
    return `
        <div class='alpi-quote alpi-card alpi-mb-md' data-index='${newIndex}'>
            <div class="alpi-form-group">
                <label class="alpi-form-label">Quote Content:</label>
                <textarea class="alpi-form-input" name='blocks[${blockIndex}][quotes_data][${newIndex}][content]' placeholder='Quote Content'>${content}</textarea>
            </div>
            <div class="alpi-form-group">
                <label class="alpi-form-label">Author:</label>
                <input class="alpi-form-input" type='text' name='blocks[${blockIndex}][quotes_data][${newIndex}][author]' value="${author}" placeholder='Author'>
            </div>
            <div class="alpi-btn-group">
                <button type="button" class="alpi-btn alpi-btn-secondary" onclick="shiftQuoteUpward(${blockIndex}, ${newIndex})">Move Up</button>
                <button type="button" class="alpi-btn alpi-btn-secondary" onclick="shiftQuoteDownward(${blockIndex}, ${newIndex})">Move Down</button>
                <button type="button" class="alpi-btn alpi-btn-danger" onclick="removeQuote(${blockIndex}, ${newIndex})">Delete Quote</button>
            </div>
        </div>
    `;
}

function shiftQuoteUpward(blockIndex, quoteIndex) {
    const block = getBlockByIndex(blockIndex);
    const quotes = block.querySelectorAll('.alpi-quote');
    const quote = quotes[quoteIndex];
    const prevQuote = quote.previousElementSibling;
    if (prevQuote) {
        quote.parentNode.insertBefore(quote, prevQuote);
        updateButtonsQuote(blockIndex);
    }
}

function shiftQuoteDownward(blockIndex, quoteIndex) {
    const block = getBlockByIndex(blockIndex);
    const quotes = block.querySelectorAll('.alpi-quote');
    const quote = quotes[quoteIndex];
    const nextQuote = quote.nextElementSibling;
    if (nextQuote) {
        quote.parentNode.insertBefore(nextQuote, quote);
        updateButtonsQuote(blockIndex);
    }
}

function removeQuote(blockIndex, quoteIndex) {
    const block = getBlockByIndex(blockIndex);
    const quote = block.querySelector(`.alpi-quote[data-index="${quoteIndex}"]`);
    if (quote) {
        quote.remove();
    }
    updateButtonsQuote(blockIndex);
}

function updateButtonsQuote(blockIndex) {
    const block = getBlockByIndex(blockIndex);
    const quotes = block.querySelectorAll('.alpi-quote');
    quotes.forEach((quote, index) => {
        const buttonsDiv = quote.querySelector('.alpi-btn-group');
        buttonsDiv.innerHTML = '';

        if (index > 0) {
            const moveUpButton = generateControlButton('Move Up', () => shiftQuoteUpward(blockIndex, index), 'alpi-btn alpi-btn-secondary');
            buttonsDiv.appendChild(moveUpButton);
        }

        if (index < quotes.length - 1) {
            const moveDownButton = generateControlButton('Move Down', () => shiftQuoteDownward(blockIndex, index), 'alpi-btn alpi-btn-secondary');
            buttonsDiv.appendChild(moveDownButton);
        }

        const deleteButton = generateControlButton('Delete', () => removeQuote(blockIndex, index), 'alpi-btn alpi-btn-danger');
        buttonsDiv.appendChild(deleteButton);
    });
}

// Tab management
function openTab(evt, tabName, containerId) {
    evt.preventDefault();

    const container = document.getElementById(containerId);

    if (!container) {
        console.error('Container not found for ID:', containerId);
        return;
    }

    let i;
    const tabcontent = container.getElementsByClassName("alpi-tab-content");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    const tablinks = container.getElementsByClassName("alpi-tab");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].classList.remove("active");
    }

    const tabContentElement = document.getElementById(tabName);
    if (tabContentElement) {
        tabContentElement.style.display = "block";
        evt.currentTarget.classList.add("active");
    } else {
        console.error('Tab content not found for tab name:', tabName);
    }
}

// Initialize tabs
document.addEventListener("DOMContentLoaded", function () {
    setTimeout(function () {
        const tabContainers = document.querySelectorAll(".alpi-tabs-container");
        tabContainers.forEach(function (container) {
            const tablinks = container.getElementsByClassName("alpi-tab");
            if (tablinks.length > 0) {
                tablinks[0].click();
            }
        });
    }, 2000);
});
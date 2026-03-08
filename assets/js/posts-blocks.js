// Main initialization
document.addEventListener('DOMContentLoaded', function () {
    initializeBlocks();
    initializeMediaSourceSelectors();
});

// Block management functions
function initializeBlocks() {
    reindexBlocks();
    document.querySelectorAll('#contentBlocks .alpi-block select[name$="[type]"]').forEach(select => {
        loadSelectedBlockContent(select);
    });
}

function addBlock() {
    const contentBlocks = document.getElementById('contentBlocks');
    const index = contentBlocks.childElementCount;
    const blockHTML = getBlockHTML(index);
    contentBlocks.insertAdjacentHTML('beforeend', blockHTML);
    const block = contentBlocks.lastElementChild;
    attachBlockStateListeners(block);
    reindexBlocks();
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

function removeContentBlock(reference) {
    const block = getBlockElement(reference);
    if (!block) {
        return;
    }

    block.remove();
    reindexBlocks();
}

function shiftBlockUpward(reference) {
    const block = getBlockElement(reference);
    if (!block) {
        return;
    }

    const prevBlock = block.previousElementSibling;
    if (prevBlock) {
        block.parentNode.insertBefore(block, prevBlock);
        reindexBlocks();
    }
}

function shiftBlockDownward(reference) {
    const block = getBlockElement(reference);
    if (!block) {
        return;
    }

    const nextBlock = block.nextElementSibling;
    if (nextBlock) {
        block.parentNode.insertBefore(nextBlock, block);
        reindexBlocks();
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

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function getBlockElement(reference) {
    if (!reference) {
        return null;
    }

    if (reference.classList && reference.classList.contains('alpi-block')) {
        return reference;
    }

    if (typeof reference === 'number' || /^\d+$/.test(String(reference))) {
        return document.querySelector(`#contentBlocks .alpi-block[data-index="${reference}"]`);
    }

    return reference.closest ? reference.closest('.alpi-block') : null;
}

function getBlockIndex(block) {
    return parseInt(block.dataset.index, 10);
}

function attachBlockStateListeners(block) {
    if (!block || block.dataset.stateListenersAttached === 'true') {
        return;
    }

    block.addEventListener('input', handleBlockStateChange);
    block.addEventListener('change', handleBlockStateChange);
    block.dataset.stateListenersAttached = 'true';
}

function handleBlockStateChange(event) {
    const block = getBlockElement(event.target);
    if (!block) {
        return;
    }

    syncBlockDataValue(block);
}

function getFieldValue(field) {
    if (field.type === 'checkbox') {
        return field.checked;
    }

    if (field.type === 'select-multiple') {
        return Array.from(field.selectedOptions).map(option => option.value).join(',');
    }

    return field.value;
}

function collectCollectionData(block, itemSelector, baseKey, fieldNames) {
    const blockIndex = getBlockIndex(block);

    return Array.from(block.querySelectorAll(itemSelector)).map((item, itemIndex) => {
        const itemData = {};

        fieldNames.forEach(fieldName => {
            const exactSelector = `[name="blocks[${blockIndex}][${baseKey}][${itemIndex}][${fieldName}]"]`;
            const fallbackSelector = `[name$="[${fieldName}]"]`;
            const field = item.querySelector(exactSelector) || item.querySelector(fallbackSelector);

            if (field) {
                itemData[fieldName] = getFieldValue(field);
            }
        });

        return itemData;
    }).filter(itemData => Object.keys(itemData).length > 0);
}

function syncBlockDataValue(block) {
    const blockContent = block.querySelector('.alpi-block-content');
    if (!blockContent) {
        return;
    }

    // Preserve the server-provided block payload until the block form has been rendered.
    if (!blockContent.querySelector('input[name], select[name], textarea[name]')) {
        return;
    }

    const blockIndex = getBlockIndex(block);
    const blockData = {};

    block.querySelectorAll('input[name], select[name], textarea[name]').forEach(field => {
        if (field.closest('.alpi-block') !== block) {
            return;
        }

        const match = field.name.match(new RegExp(`^blocks\\[${blockIndex}\\]\\[([^\\]]+)\\](\\[\\])?$`));
        if (!match) {
            return;
        }

        const key = match[1];
        if (['gallery_data', 'accordion_data', 'quotes_data'].includes(key)) {
            return;
        }

        blockData[key] = getFieldValue(field);
    });

    if (block.querySelector('.alpi-slider-gallery')) {
        const galleryData = collectCollectionData(block, '.alpi-gallery-image', 'gallery_data', ['url', 'alt_text', 'caption']);
        blockData.gallery_data = galleryData.length > 0 ? JSON.stringify(galleryData) : '';
    }

    if (block.querySelector('.alpi-accordion-section') || block.querySelector('.alpi-accordion-wrapper')) {
        const accordionData = collectCollectionData(block, '.alpi-accordion-section', 'accordion_data', [
            'title',
            'content',
            'text_color',
            'background_color',
            'title_size_desktop',
            'title_size_tablet',
            'title_size_mobile',
            'content_size_desktop',
            'content_size_tablet',
            'content_size_mobile'
        ]);
        blockData.accordion_data = accordionData.length > 0 ? JSON.stringify(accordionData) : '';
    }

    if (block.querySelector('.alpi-quote') || block.querySelector('.alpi-quote-wrapper')) {
        const quotesData = collectCollectionData(block, '.alpi-quote', 'quotes_data', [
            'content',
            'author',
            'text_color',
            'background_color',
            'text_size_desktop',
            'text_size_tablet',
            'text_size_mobile'
        ]);
        blockData.quotes_data = quotesData.length > 0 ? JSON.stringify(quotesData) : '';
    }

    blockContent.dataset.value = JSON.stringify(blockData);
}

function getBackgroundSizeFromName(name) {
    const match = name.match(/\[background_type_(desktop|tablet|mobile)\]$/);
    return match ? match[1] : null;
}

function updateBackgroundTypeFields(selectorOrReference, size) {
    let selector = selectorOrReference;

    if (!selector || !selector.name) {
        const block = getBlockElement(selectorOrReference);
        if (!block) {
            return;
        }

        selector = block.querySelector(`[name="blocks[${getBlockIndex(block)}][background_type_${size}]"]`);
        if (!selector) {
            return;
        }
    }

    const block = getBlockElement(selector);
    if (!block) {
        return;
    }

    const blockIndex = getBlockIndex(block);
    const imageInput = block.querySelector(`[name="blocks[${blockIndex}][background_image_${size}]"]`);
    const colorInput = block.querySelector(`[name="blocks[${blockIndex}][background_color]"]`);
    const imageField = imageInput ? imageInput.closest('.alpi-form-group') : null;
    const colorField = colorInput ? colorInput.closest('.alpi-form-group') : null;
    const backgroundSelectors = block.querySelectorAll(`[name^="blocks[${blockIndex}][background_type_"]`);
    const shouldShowColor = Array.from(backgroundSelectors).some(typeSelector => typeSelector.value === 'color');

    if (imageField) {
        imageField.style.display = selector.value === 'image' ? '' : 'none';
    }

    if (colorField) {
        colorField.style.display = shouldShowColor ? '' : 'none';
    }
}

function initializeBackgroundTypeControls(scope = document) {
    const blocks = scope.classList && scope.classList.contains('alpi-block') ? [scope] : Array.from(scope.querySelectorAll('#contentBlocks .alpi-block'));

    blocks.forEach(block => {
        block.querySelectorAll('.alpi-background-type-selector').forEach(selector => {
            if (selector.dataset.backgroundTypeBound !== 'true') {
                selector.addEventListener('change', event => {
                    const size = event.target.dataset.backgroundSize || getBackgroundSizeFromName(event.target.name);
                    updateBackgroundTypeFields(event.target, size);
                });
                selector.dataset.backgroundTypeBound = 'true';
            }

            const size = selector.dataset.backgroundSize || getBackgroundSizeFromName(selector.name);
            updateBackgroundTypeFields(selector, size);
        });
    });
}

function reindexBlockFieldNames(block, blockIndex) {
    block.querySelectorAll('[name]').forEach(field => {
        field.name = field.name.replace(/^blocks\[\d+\]/, `blocks[${blockIndex}]`);
    });
}

function reindexBlocks() {
    const blocks = document.querySelectorAll('#contentBlocks .alpi-block');

    blocks.forEach((block, index) => {
        block.dataset.index = index;
        attachBlockStateListeners(block);
        reindexBlockFieldNames(block, index);

        const gallery = block.querySelector('.alpi-slider-gallery');
        if (gallery) {
            reindexGalleryItems(gallery);
        }

        const blockContent = block.querySelector('.alpi-block-content');
        if (blockContent) {
            if (blockContent.querySelector('.alpi-accordion-wrapper, .alpi-accordion-section')) {
                updateAccordionOrder(blockContent);
            }

            if (blockContent.querySelector('.alpi-quote-wrapper, .alpi-quote')) {
                updateQuoteOrder(blockContent);
            }
        }

        syncBlockDataValue(block);
    });

    updateButtonsBlock();
    initializeMediaSourceSelectors();
    initializeBackgroundTypeControls();
}

// Content loading and population
function loadSelectedBlockContent(selectElement) {
    const block = getBlockElement(selectElement);
    if (!block) {
        return;
    }

    syncBlockDataValue(block);

    const type = selectElement.value;
    const index = getBlockIndex(block);
    const contentDiv = block.querySelector('.alpi-block-content');
    const blockData = JSON.parse(contentDiv.getAttribute('data-value') || '{}');

    fetch(`../../../blocks/render-block-content.php?type=${type}&index=${index}`)
        .then(response => response.text())
        .then(html => {
            contentDiv.innerHTML = html;
            populateBlockFields(contentDiv, blockData, index);
            attachBlockStateListeners(block);
            initializeMediaSourceSelectors(block);
            initializeBackgroundTypeControls(block);
            syncBlockDataValue(block);
        })
        .catch(error => console.error('Error:', error));
}

function populateBlockFields(container, data, index) {
    const blockData = data.block_data || data;
    
    Object.keys(blockData).forEach(key => {
        try {
            const input = container.querySelector(`[name="blocks[${index}][${key}]"]`);
            if (input) {
                if (input.type === 'checkbox') {
                    input.checked = !!blockData[key];
                } else if (input.type === 'select-multiple') {
                    const selectedValues = blockData[key].split(',');
                    Array.from(input.options).forEach(option => {
                        option.selected = selectedValues.includes(option.value);
                    });
                } else {
                    input.value = blockData[key];
                }
            } else if (key === 'gallery_data' && blockData[key]) {
                try {
                    const galleryData = JSON.parse(blockData[key]);
                    let galleryArray = [];
                    
                    if (Array.isArray(galleryData)) {
                        galleryArray = galleryData;
                    } else if (typeof galleryData === 'object' && galleryData !== null) {
                        galleryArray = Object.values(galleryData);
                    }
                    
                    if (galleryArray.length > 0) {
                        populateGalleryData(container, galleryArray, index);
                    }
                } catch (e) {
                    console.error('Error parsing gallery_data:', e);
                }
            } else if (key === 'accordion_data' && blockData[key]) {
                const accordionData = JSON.parse(blockData[key]);
                const block = container.closest('.alpi-block');
                Object.values(accordionData).forEach(section => {
                    insertAccordionSection(block || index, section);
                });
                updateAccordionOrder(container);
            } else if (key === 'quotes_data' && blockData[key]) {
                try {
                    const quotesData = JSON.parse(blockData[key]);
                    let quotesArray = [];
                    
                    if (Array.isArray(quotesData)) {
                        quotesArray = quotesData;
                    } else if (typeof quotesData === 'object' && quotesData !== null) {
                        quotesArray = Object.values(quotesData);
                    }
                    
                    if (quotesArray.length > 0) {
                        const block = container.closest('.alpi-block');
                        quotesArray.forEach(quote => {
                            addQuote(block || index, quote);
                        });
                    }
                } catch (e) {
                    console.error('Error parsing quotes_data:', e);
                }
            } else if (key === 'selected_post_ids') {
                const select = container.querySelector(`[name="blocks[${index}][${key}][]"]`);
                if (select) {
                    const selectedValues = blockData[key].split(',');
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

function initializeMediaSourceSelectors(scope = document) {
    const blocks = scope.classList && scope.classList.contains('alpi-block') ? [scope] : Array.from(scope.querySelectorAll('.alpi-block'));

    blocks.forEach(block => {
        const videoSelector = block.querySelector('.video-source-selector');
        const audioSelector = block.querySelector('.audio-source-selector');

        if (videoSelector) {
            toggleSourceField(videoSelector, 'video');
            if (videoSelector.dataset.mediaSourceBound !== 'true') {
                videoSelector.addEventListener('change', () => toggleSourceField(videoSelector, 'video'));
                videoSelector.dataset.mediaSourceBound = 'true';
            }
        }

        if (audioSelector) {
            toggleSourceField(audioSelector, 'audio');
            if (audioSelector.dataset.mediaSourceBound !== 'true') {
                audioSelector.addEventListener('change', () => toggleSourceField(audioSelector, 'audio'));
                audioSelector.dataset.mediaSourceBound = 'true';
            }
        }
    });
}

// Gallery functions
function populateGalleryData(container, galleryData, blockIndex) {
    const galleryContainer = container.querySelector('.alpi-slider-gallery');
    if (!galleryContainer) {
        return;
    }
    
    const existingImages = galleryContainer.querySelectorAll('.alpi-gallery-image');
    existingImages.forEach(img => img.remove());
    
    let loadedImages = 0;
    galleryData.forEach((imageData, imageIndex) => {
        addGalleryImageWithData(blockIndex, imageData, imageIndex, () => {
            loadedImages++;
            if (loadedImages === galleryData.length) {
                updateButtonsImage();
            }
        });
    });
}

function addGalleryImageWithData(blockIndex, imageData, imageIndex, callback) {
    fetch('../../../utils/get-uploads.php')
        .then(response => response.json())
        .then(data => {
            if (data && data.uploads) {
                const block = getBlockElement(blockIndex);
                const gallery = block ? block.querySelector('.alpi-slider-gallery') : null;
                if (!gallery) {
                    console.error('Gallery block not found for index:', blockIndex);
                    return;
                }

                const newImageHtml = getGalleryImageHTMLWithData(blockIndex, imageIndex, data.uploads, imageData);
                const addButton = gallery.querySelector('.alpi-btn-primary');
                if (addButton) {
                    addButton.insertAdjacentHTML('beforebegin', newImageHtml);
                } else {
                    gallery.insertAdjacentHTML('beforeend', newImageHtml);
                }
                
                if (callback) callback();
            } else {
                console.error('No upload data found');
            }
        })
        .catch(error => console.error('Error loading uploads:', error));
}

function getGalleryImageHTMLWithData(blockIndex, imageIndex, uploads, imageData) {
    const selectedUrl = imageData.url || '';
    const altText = imageData.alt_text || '';
    const caption = imageData.caption || '';
    
    return `
        <div class='alpi-gallery-image alpi-card alpi-mb-md' data-index='${imageIndex}'>
            <div class="alpi-form-group">
                <label class="alpi-form-label">Image:</label>
                <select class="alpi-form-input" name='blocks[${blockIndex}][gallery_data][${imageIndex}][url]'>
                    ${uploads.map(upload => {
                        const selected = upload.url === selectedUrl ? 'selected' : '';
                        return `<option value='${escapeHtml(upload.url)}' ${selected}>${escapeHtml(upload.url)}</option>`;
                    }).join('')}
                </select>
            </div>
            <div class="alpi-form-group">
                <label class="alpi-form-label">Alt Text:</label>
                <input class="alpi-form-input" type='text' name='blocks[${blockIndex}][gallery_data][${imageIndex}][alt_text]' placeholder='Alt Text' value='${escapeHtml(altText)}'>
            </div>
            <div class="alpi-form-group">
                <label class="alpi-form-label">Caption:</label>
                <input class="alpi-form-input" type='text' name='blocks[${blockIndex}][gallery_data][${imageIndex}][caption]' placeholder='Caption' value='${escapeHtml(caption)}'>
            </div>
            <div class="alpi-btn-group">
                <button type='button' class="alpi-btn alpi-btn-secondary" onclick='shiftImageUpward(this)'>Move Up</button>
                <button type='button' class="alpi-btn alpi-btn-secondary" onclick='shiftImageDownward(this)'>Move Down</button>
                <button type='button' class="alpi-btn alpi-btn-danger" onclick='removeGalleryImage(this)'>Delete Image</button>
            </div>
        </div>
    `;
}

function addGalleryImage(reference) {
    const block = getBlockElement(reference);
    if (!block) {
        return;
    }

    const blockIndex = getBlockIndex(block);

    fetch('../../../utils/get-uploads.php')
        .then(response => response.json())
        .then(data => {
            if (data && data.uploads) {
                const gallery = block.querySelector('.alpi-slider-gallery');
                if (!gallery) {
                    console.error('Block not found');
                    return;
                }
                const newIndex = gallery.querySelectorAll('.alpi-gallery-image').length;

                const newImageHtml = getGalleryImageHTML(blockIndex, newIndex, data.uploads);
                const addButton = gallery.querySelector('.alpi-btn-primary');
                if (addButton) {
                    addButton.insertAdjacentHTML('beforebegin', newImageHtml);
                } else {
                    gallery.insertAdjacentHTML('beforeend', newImageHtml);
                }

                reindexGalleryItems(gallery);
                syncBlockDataValue(block);
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
                    ${uploads.map(upload => `<option value='${escapeHtml(upload.url)}'>${escapeHtml(upload.url)}</option>`).join('')}
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
                <button type='button' class="alpi-btn alpi-btn-danger" onclick='removeGalleryImage(this)'>Delete Image</button>
            </div>
        </div>
    `;
}

function removeGalleryImage(reference) {
    const image = reference.classList && reference.classList.contains('alpi-gallery-image') ? reference : reference.closest('.alpi-gallery-image');
    if (!image) {
        return;
    }

    const gallery = image.closest('.alpi-slider-gallery');
    const block = getBlockElement(gallery);
    image.remove();

    if (gallery) {
        reindexGalleryItems(gallery);
    }

    if (block) {
        syncBlockDataValue(block);
    }
}

function shiftImageUpward(button) {
    const image = button.closest('.alpi-gallery-image');
    const prevImage = image.previousElementSibling;
    if (prevImage) {
        image.parentNode.insertBefore(image, prevImage);
        const gallery = image.closest('.alpi-slider-gallery');
        reindexGalleryItems(gallery);
        syncBlockDataValue(getBlockElement(gallery));
    }
}

function shiftImageDownward(button) {
    const image = button.closest('.alpi-gallery-image');
    const nextImage = image.nextElementSibling;
    if (nextImage) {
        image.parentNode.insertBefore(nextImage, image);
        const gallery = image.closest('.alpi-slider-gallery');
        reindexGalleryItems(gallery);
        syncBlockDataValue(getBlockElement(gallery));
    }
}

function reindexGalleryItems(gallery) {
    if (!gallery) {
        return;
    }

    const block = getBlockElement(gallery);
    if (!block) {
        return;
    }

    const blockIndex = getBlockIndex(block);
    gallery.dataset.index = blockIndex;

    const images = gallery.querySelectorAll('.alpi-gallery-image');
    images.forEach((image, index) => {
        image.dataset.index = index;
        image.querySelectorAll('[name]').forEach(field => {
            field.name = field.name.replace(/^blocks\[\d+\]\[gallery_data\]\[\d+\]/, `blocks[${blockIndex}][gallery_data][${index}]`);
        });

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

        const deleteButton = generateControlButton('Delete Image', () => removeGalleryImage(image), 'alpi-btn alpi-btn-danger');
        buttonsDiv.appendChild(deleteButton);
    });
}

function updateButtonsImage() {
    document.querySelectorAll('.alpi-slider-gallery').forEach(gallery => reindexGalleryItems(gallery));
}

// Accordion functions
function insertAccordionSection(reference, initialData = null) {
    let block = getBlockElement(reference);
    if (!block && (typeof reference === 'number' || /^\d+$/.test(String(reference)))) {
        block = getBlockByIndex(parseInt(reference, 10));
    }

    if (!block && reference && reference.closest) {
        const blockContent = reference.closest('.alpi-block-content');
        block = blockContent ? blockContent.closest('.alpi-block') : null;
    }

    if (!block) {
        console.error('Unable to find accordion block for reference:', reference);
        return;
    }

    const blockContent = block.querySelector('.alpi-block-content');
    const accordionWrapper = blockContent.querySelector('.alpi-accordion-wrapper');
    const addButton = accordionWrapper ? accordionWrapper.querySelector('.alpi-btn-primary') : null;
    const blockIndex = getBlockIndex(block);

    const newIndex = accordionWrapper ? accordionWrapper.querySelectorAll('.alpi-accordion-section').length : 0;
    const newSectionHtml = getAccordionSectionHTML(blockIndex, newIndex, initialData);
    if (addButton) {
        addButton.insertAdjacentHTML('beforebegin', newSectionHtml);
    } else if (accordionWrapper) {
        accordionWrapper.insertAdjacentHTML('beforeend', newSectionHtml);
    }

    updateAccordionOrder(blockContent);
    syncBlockDataValue(block);
}

function getBlockByIndex(blockIndex) {
    return document.querySelector(`#contentBlocks .alpi-block[data-index="${blockIndex}"]`);
}

function getAccordionSectionHTML(blockIndex, newIndex, initialData = null) {
    const title = escapeHtml(initialData ? initialData.title : '');
    const content = escapeHtml(initialData ? initialData.content : '');
    const textColor = initialData ? (initialData.text_color || '#000000') : '#000000';
    const backgroundColor = initialData ? (initialData.background_color || '#ffffff') : '#ffffff';
    const titleSizeDesktop = escapeHtml(initialData ? initialData.title_size_desktop || '' : '');
    const titleSizeTablet = escapeHtml(initialData ? initialData.title_size_tablet || '' : '');
    const titleSizeMobile = escapeHtml(initialData ? initialData.title_size_mobile || '' : '');
    const contentSizeDesktop = escapeHtml(initialData ? initialData.content_size_desktop || '' : '');
    const contentSizeTablet = escapeHtml(initialData ? initialData.content_size_tablet || '' : '');
    const contentSizeMobile = escapeHtml(initialData ? initialData.content_size_mobile || '' : '');

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
            <div class="alpi-form-group">
                <label class="alpi-form-label">Text Color:</label>
                <input class="alpi-form-input" type='color' name='blocks[${blockIndex}][accordion_data][${newIndex}][text_color]' value='${escapeHtml(textColor)}'>
            </div>
            <div class="alpi-form-group">
                <label class="alpi-form-label">Background Color:</label>
                <input class="alpi-form-input" type='color' name='blocks[${blockIndex}][accordion_data][${newIndex}][background_color]' value='${escapeHtml(backgroundColor)}'>
            </div>
            <div class="alpi-form-group">
                <label class="alpi-form-label">Title Size (Desktop):</label>
                <input class="alpi-form-input" type='number' name='blocks[${blockIndex}][accordion_data][${newIndex}][title_size_desktop]' value='${titleSizeDesktop}'>
            </div>
            <div class="alpi-form-group">
                <label class="alpi-form-label">Title Size (Tablet):</label>
                <input class="alpi-form-input" type='number' name='blocks[${blockIndex}][accordion_data][${newIndex}][title_size_tablet]' value='${titleSizeTablet}'>
            </div>
            <div class="alpi-form-group">
                <label class="alpi-form-label">Title Size (Mobile):</label>
                <input class="alpi-form-input" type='number' name='blocks[${blockIndex}][accordion_data][${newIndex}][title_size_mobile]' value='${titleSizeMobile}'>
            </div>
            <div class="alpi-form-group">
                <label class="alpi-form-label">Content Size (Desktop):</label>
                <input class="alpi-form-input" type='number' name='blocks[${blockIndex}][accordion_data][${newIndex}][content_size_desktop]' value='${contentSizeDesktop}'>
            </div>
            <div class="alpi-form-group">
                <label class="alpi-form-label">Content Size (Tablet):</label>
                <input class="alpi-form-input" type='number' name='blocks[${blockIndex}][accordion_data][${newIndex}][content_size_tablet]' value='${contentSizeTablet}'>
            </div>
            <div class="alpi-form-group">
                <label class="alpi-form-label">Content Size (Mobile):</label>
                <input class="alpi-form-input" type='number' name='blocks[${blockIndex}][accordion_data][${newIndex}][content_size_mobile]' value='${contentSizeMobile}'>
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
    const blockContent = section.closest('.alpi-block-content');
    section.remove();
    updateAccordionOrder(blockContent);
}

function shiftAccordionSectionUp(button) {
    const section = button.closest('.alpi-accordion-section');
    const prevSection = section.previousElementSibling;
    if (prevSection && prevSection.classList.contains('alpi-accordion-section')) {
        section.parentNode.insertBefore(section, prevSection);
        updateAccordionOrder(section.closest('.alpi-block-content'));
    }
}

function shiftAccordionSectionDown(button) {
    const section = button.closest('.alpi-accordion-section');
    const nextSection = section.nextElementSibling;
    if (nextSection && nextSection.classList.contains('alpi-accordion-section')) {
        section.parentNode.insertBefore(nextSection, section);
        updateAccordionOrder(section.closest('.alpi-block-content'));
    }
}

function updateAccordionOrder(blockContent) {
    if (!blockContent.querySelector('.alpi-accordion-wrapper, .alpi-accordion-section')) {
        return;
    }

    const sections = blockContent.querySelectorAll('.alpi-accordion-section');
    const blockIndex = blockContent.closest('.alpi-block').dataset.index;
    const accordionData = [];

    sections.forEach((section, index) => {
        section.querySelectorAll('[name]').forEach(field => {
            field.name = field.name.replace(/^blocks\[\d+\]\[accordion_data\]\[\d+\]/, `blocks[${blockIndex}][accordion_data][${index}]`);
        });

        section.dataset.index = index;

        accordionData[index] = {
            title: section.querySelector(`[name="blocks[${blockIndex}][accordion_data][${index}][title]"]`)?.value || '',
            content: section.querySelector(`[name="blocks[${blockIndex}][accordion_data][${index}][content]"]`)?.value || '',
            text_color: section.querySelector(`[name="blocks[${blockIndex}][accordion_data][${index}][text_color]"]`)?.value || '',
            background_color: section.querySelector(`[name="blocks[${blockIndex}][accordion_data][${index}][background_color]"]`)?.value || '',
            title_size_desktop: section.querySelector(`[name="blocks[${blockIndex}][accordion_data][${index}][title_size_desktop]"]`)?.value || '',
            title_size_tablet: section.querySelector(`[name="blocks[${blockIndex}][accordion_data][${index}][title_size_tablet]"]`)?.value || '',
            title_size_mobile: section.querySelector(`[name="blocks[${blockIndex}][accordion_data][${index}][title_size_mobile]"]`)?.value || '',
            content_size_desktop: section.querySelector(`[name="blocks[${blockIndex}][accordion_data][${index}][content_size_desktop]"]`)?.value || '',
            content_size_tablet: section.querySelector(`[name="blocks[${blockIndex}][accordion_data][${index}][content_size_tablet]"]`)?.value || '',
            content_size_mobile: section.querySelector(`[name="blocks[${blockIndex}][accordion_data][${index}][content_size_mobile]"]`)?.value || ''
        };
    });

    const blockContentDiv = blockContent.closest('.alpi-block').querySelector('.alpi-block-content');
    const dataValue = JSON.parse(blockContentDiv.dataset.value || '{}');
    dataValue.accordion_data = JSON.stringify(accordionData);
    blockContentDiv.dataset.value = JSON.stringify(dataValue);

    updateButtonsAccordionSection(blockContent);
}

function updateButtonsAccordionSection(blockContent) {
    const sections = blockContent.querySelectorAll('.alpi-accordion-section');
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
function addQuote(reference, initialData = null) {
    let block = getBlockElement(reference);
    if (!block && (typeof reference === 'number' || /^\d+$/.test(String(reference)))) {
        block = getBlockByIndex(parseInt(reference, 10));
    }

    if (!block && reference && reference.closest) {
        const blockContent = reference.closest('.alpi-block-content');
        block = blockContent ? blockContent.closest('.alpi-block') : null;
    }

    if (!block) {
        console.error('Unable to find quote block for reference:', reference);
        return;
    }

    const blockContent = block.querySelector('.alpi-block-content');
    const quoteWrapper = blockContent.querySelector('.alpi-quote-wrapper');
    const addButton = quoteWrapper ? quoteWrapper.querySelector('.alpi-btn-primary') : null;
    const blockIndex = getBlockIndex(block);
    const newIndex = quoteWrapper ? quoteWrapper.querySelectorAll('.alpi-quote').length : 0;
    const newQuoteHtml = getQuoteHTML(blockIndex, newIndex, initialData);
    if (addButton) {
        addButton.insertAdjacentHTML('beforebegin', newQuoteHtml);
    } else if (quoteWrapper) {
        quoteWrapper.insertAdjacentHTML('beforeend', newQuoteHtml);
    }

    updateQuoteOrder(blockContent);
    syncBlockDataValue(block);
}

function getQuoteHTML(blockIndex, newIndex, initialData = null) {
    const content = escapeHtml(initialData ? initialData.content : '');
    const author = escapeHtml(initialData ? initialData.author : '');
    const textColor = initialData ? (initialData.text_color || '#000000') : '#000000';
    const backgroundColor = initialData ? (initialData.background_color || '#ffffff') : '#ffffff';
    const textSizeDesktop = escapeHtml(initialData ? initialData.text_size_desktop || '' : '');
    const textSizeTablet = escapeHtml(initialData ? initialData.text_size_tablet || '' : '');
    const textSizeMobile = escapeHtml(initialData ? initialData.text_size_mobile || '' : '');

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
            <div class="alpi-form-group">
                <label class="alpi-form-label">Text Color:</label>
                <input class="alpi-form-input" type='color' name='blocks[${blockIndex}][quotes_data][${newIndex}][text_color]' value='${escapeHtml(textColor)}'>
            </div>
            <div class="alpi-form-group">
                <label class="alpi-form-label">Background Color:</label>
                <input class="alpi-form-input" type='color' name='blocks[${blockIndex}][quotes_data][${newIndex}][background_color]' value='${escapeHtml(backgroundColor)}'>
            </div>
            <div class="alpi-form-group">
                <label class="alpi-form-label">Text Size (Desktop):</label>
                <input class="alpi-form-input" type='number' name='blocks[${blockIndex}][quotes_data][${newIndex}][text_size_desktop]' value='${textSizeDesktop}'>
            </div>
            <div class="alpi-form-group">
                <label class="alpi-form-label">Text Size (Tablet):</label>
                <input class="alpi-form-input" type='number' name='blocks[${blockIndex}][quotes_data][${newIndex}][text_size_tablet]' value='${textSizeTablet}'>
            </div>
            <div class="alpi-form-group">
                <label class="alpi-form-label">Text Size (Mobile):</label>
                <input class="alpi-form-input" type='number' name='blocks[${blockIndex}][quotes_data][${newIndex}][text_size_mobile]' value='${textSizeMobile}'>
            </div>
            <div class="alpi-btn-group">
                <button type="button" class="alpi-btn alpi-btn-secondary" onclick="shiftQuoteUpward(this)">Move Up</button>
                <button type="button" class="alpi-btn alpi-btn-secondary" onclick="shiftQuoteDownward(this)">Move Down</button>
                <button type="button" class="alpi-btn alpi-btn-danger" onclick="removeQuote(this)">Delete Quote</button>
            </div>
        </div>
    `;
}

function shiftQuoteUpward(button) {
    const quote = button.closest ? button.closest('.alpi-quote') : button.parentElement.closest('.alpi-quote');
    const prevQuote = quote.previousElementSibling;
    if (prevQuote && prevQuote.classList.contains('alpi-quote')) {
        quote.parentNode.insertBefore(quote, prevQuote);
        updateQuoteOrder(quote.closest('.alpi-block-content'));
    }
}

function shiftQuoteDownward(button) {
    const quote = button.closest ? button.closest('.alpi-quote') : button.parentElement.closest('.alpi-quote');
    const nextQuote = quote.nextElementSibling;
    if (nextQuote && nextQuote.classList.contains('alpi-quote')) {
        quote.parentNode.insertBefore(nextQuote, quote);
        updateQuoteOrder(quote.closest('.alpi-block-content'));
    }
}

function removeQuote(button) {
    const quote = button.closest ? button.closest('.alpi-quote') : button.parentElement.closest('.alpi-quote');
    const blockContent = quote.closest('.alpi-block-content');
    quote.remove();
    updateQuoteOrder(blockContent);
    syncBlockDataValue(getBlockElement(blockContent));
}

function updateQuoteOrder(blockContent) {
    if (!blockContent.querySelector('.alpi-quote-wrapper, .alpi-quote')) {
        return;
    }

    const quotes = blockContent.querySelectorAll('.alpi-quote');
    const blockIndex = blockContent.closest('.alpi-block').dataset.index;
    const quotesData = [];
    
    quotes.forEach((quote, index) => {
        quote.querySelectorAll('[name]').forEach(field => {
            field.name = field.name.replace(/^blocks\[\d+\]\[quotes_data\]\[\d+\]/, `blocks[${blockIndex}][quotes_data][${index}]`);
        });

        quote.dataset.index = index;

        quotesData[index] = {
            content: quote.querySelector(`[name="blocks[${blockIndex}][quotes_data][${index}][content]"]`)?.value || '',
            author: quote.querySelector(`[name="blocks[${blockIndex}][quotes_data][${index}][author]"]`)?.value || '',
            text_color: quote.querySelector(`[name="blocks[${blockIndex}][quotes_data][${index}][text_color]"]`)?.value || '',
            background_color: quote.querySelector(`[name="blocks[${blockIndex}][quotes_data][${index}][background_color]"]`)?.value || '',
            text_size_desktop: quote.querySelector(`[name="blocks[${blockIndex}][quotes_data][${index}][text_size_desktop]"]`)?.value || '',
            text_size_tablet: quote.querySelector(`[name="blocks[${blockIndex}][quotes_data][${index}][text_size_tablet]"]`)?.value || '',
            text_size_mobile: quote.querySelector(`[name="blocks[${blockIndex}][quotes_data][${index}][text_size_mobile]"]`)?.value || ''
        };
    });

    const blockContentDiv = blockContent.closest('.alpi-block').querySelector('.alpi-block-content');
    const dataValue = JSON.parse(blockContentDiv.dataset.value || '{}');
    dataValue.quotes_data = JSON.stringify(quotesData);
    blockContentDiv.dataset.value = JSON.stringify(dataValue);
    
    updateButtonsQuote(blockContent.closest('.alpi-block'));
}

function updateButtonsQuote(blockReference) {
    const block = getBlockElement(blockReference);
    if (!block) {
        return;
    }

    const quotes = block.querySelectorAll('.alpi-quote');
    quotes.forEach((quote, index) => {
        const buttonsDiv = quote.querySelector('.alpi-btn-group');
        buttonsDiv.innerHTML = '';

        if (index > 0) {
            const moveUpButton = generateControlButton('Move Up', () => shiftQuoteUpward(quote), 'alpi-btn alpi-btn-secondary');
            buttonsDiv.appendChild(moveUpButton);
        }

        if (index < quotes.length - 1) {
            const moveDownButton = generateControlButton('Move Down', () => shiftQuoteDownward(quote), 'alpi-btn alpi-btn-secondary');
            buttonsDiv.appendChild(moveDownButton);
        }

        const deleteButton = generateControlButton('Delete', () => removeQuote(quote), 'alpi-btn alpi-btn-danger');
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
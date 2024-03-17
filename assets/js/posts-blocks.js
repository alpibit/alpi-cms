function addBlock() {
    const index = document.getElementById('contentBlocks').childElementCount;
    const blockHTML = `
        <div class="block">
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
            <div class="block-content"></div>
            <div class="buttons">
                <button type="button" onclick="shiftBlockUpward(this)">Move Up</button>
                <button type="button" onclick="shiftBlockDownward(this)">Move Down</button>
                <button type="button" onclick="removeContentBlock(this)">Delete</button>
            </div>
            <br>
        </div>
    `;
    document.getElementById('contentBlocks').insertAdjacentHTML('beforeend', blockHTML);
    updateButtonsBlock();
}


function removeContentBlock(button) {
    const block = button.closest('div.block');
    block.remove();
    updateButtonsBlock();
}

function shiftBlockUpward(button) {
    const block = button.closest('div.block');
    const prevBlock = block.previousElementSibling;
    if (prevBlock) {
        block.parentNode.insertBefore(block, prevBlock);
        updateButtonsBlock();
    }
}

function shiftBlockDownward(button) {
    const block = button.closest('div.block');
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
            const moveUpButton = generateBlockControlButton('Move Up', () => shiftBlockUpward(block));
            buttonsDiv.appendChild(moveUpButton);
        }

        if (index < blocks.length - 1) {
            const moveDownButton = generateBlockControlButton('Move Down', () => shiftBlockDownward(block));
            buttonsDiv.appendChild(moveDownButton);
        }

        const deleteButton = generateBlockControlButton('Delete', () => removeContentBlock(block));
        buttonsDiv.appendChild(deleteButton);
    });
}

function loadSelectedBlockContent(selectElement, index) {
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

function generateBlockControlButton(text, onClick) {
    const button = document.createElement('button');
    button.type = 'button';
    button.textContent = text;
    button.onclick = onClick;
    return button;
}

// Initialize blocks on page load
window.onload = function () {
    updateButtonsBlock();
    document.querySelectorAll('#contentBlocks .block select').forEach(select => {
        loadSelectedBlockContent(select, parseInt(select.name.match(/\d+/)[0], 10));
    });
};

function toggleSourceField(selector, type) {
    const block = selector.closest('.block');
    const urlField = block.querySelector('.' + type + '-url-field');
    const uploadField = block.querySelector('.' + type + '-upload-field');
    const value = selector.value;

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

document.addEventListener('DOMContentLoaded', () => {
    setTimeout(initializeMediaSourceSelectors, 500);
});


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
                const newIndex = block.querySelectorAll('.gallery-image').length + 1;

                let newImageHtml = `<div class='gallery-image' data-index='${newIndex}'>`;
                newImageHtml += "<label>Image: <select name='blocks[" + blockIndex + "][gallery_data][" + newIndex + "][url]'>";
                data.uploads.forEach(upload => {
                    newImageHtml += `<option value='${upload.url}'>${upload.url}</option>`;
                });
                newImageHtml += "</select></label><br>";
                newImageHtml += `<label>Alt Text: <input type='text' name='blocks[${blockIndex}][gallery_data][${newIndex}][alt_text]' placeholder='Alt Text'></label><br>`;
                newImageHtml += `<label>Caption: <input type='text' name='blocks[${blockIndex}][gallery_data][${newIndex}][caption]' placeholder='Caption'></label><br>`;
                newImageHtml += `<div class="buttons">`;
                newImageHtml += `<button type='button' onclick='shiftImageUpward(this)'>Move Up</button>`;
                newImageHtml += `<button type='button' onclick='shiftImageDownward(this)'>Move Down</button>`;
                newImageHtml += `<button type='button' onclick='removeGalleryImage(${blockIndex}, ${newIndex})'>Delete Image</button>`;
                newImageHtml += `</div>`;
                newImageHtml += "</div>";

                block.insertAdjacentHTML('beforeend', newImageHtml);
                updateButtonsImage();
            } else {
                console.error('No upload data found');
            }
        })
        .catch(error => console.error('Error loading uploads:', error));
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
            const moveUpButton = generateImageControlButton('Move Up', () => shiftImageUpward(image));
            buttonsDiv.appendChild(moveUpButton);
        }
        if (index < images.length - 1) {
            const moveDownButton = generateImageControlButton('Move Down', () => shiftImageDownward(image));
            buttonsDiv.appendChild(moveDownButton);
        }
        const deleteButton = generateImageControlButton('Delete', () => deleteImage(image));
        buttonsDiv.appendChild(deleteButton);
    });
}

function generateImageControlButton(text, onClick) {
    const button = document.createElement('button');
    button.type = 'button';
    button.textContent = text;
    button.onclick = onClick;
    return button;
}

function insertAccordionSection(blockIndex) {
    const contentBlocks = document.getElementById('contentBlocks');
    let block = contentBlocks.querySelector(`.block[data-index="${blockIndex}"]`);

    if (!block) {
        console.error('Block not found!');
        return;
    }

    const blockContent = block.querySelector('.block-content');
    if (!blockContent) {
        console.error('Block content area not found!');
        return;
    }

    const sections = blockContent.querySelectorAll('.accordion-section');
    const newIndex = sections.length;

    let newSectionHtml = `<div class='accordion-section' data-index='${newIndex}'>`;
    newSectionHtml += `<label>Section Title: <input type='text' name='blocks[${blockIndex}][accordion_data][${newIndex}][title]' placeholder='Section Title'></label><br>`;
    newSectionHtml += `<label>Section Content: <textarea name='blocks[${blockIndex}][accordion_data][${newIndex}][content]' placeholder='Section Content'></textarea></label><br>`;
    newSectionHtml += `<div class="buttons">`;
    newSectionHtml += `<button type="button" onclick="shiftAccordionSectionUp(this)">Move Up</button>`;
    newSectionHtml += `<button type="button" onclick="shiftAccordionSectionDown(this)">Move Down</button>`;
    newSectionHtml += `<button type="button" onclick="removeAccordionSection(this)">Delete</button>`;
    newSectionHtml += `</div>`;
    newSectionHtml += `</div>`;

    blockContent.insertAdjacentHTML('beforeend', newSectionHtml);
    updateButtonsAccordionSection();
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
            const moveUpButton = generateSectionControlButton('Move Up', () => shiftAccordionSectionUp(section));
            buttonsDiv.appendChild(moveUpButton);
        }
        if (index < sections.length - 1) {
            const moveDownButton = generateSectionControlButton('Move Down', () => shiftAccordionSectionDown(section));
            buttonsDiv.appendChild(moveDownButton);
        }
        const deleteButton = generateSectionControlButton('Delete', () => removeAccordionSection(section));
        buttonsDiv.appendChild(deleteButton);
    });
}

function generateSectionControlButton(text, onClick) {
    const button = document.createElement('button');
    button.type = 'button';
    button.textContent = text;
    button.onclick = onClick;
    return button;
}

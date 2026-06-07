
document.addEventListener('DOMContentLoaded', () => {
	const replaceTemplateTokens = (template, values) => {
		return Object.entries(values).reduce((message, [key, value]) => {
			return message.replaceAll(`{${key}}`, value);
		}, template);
	};

	const copyTextToClipboard = async (text) => {
		if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
			await navigator.clipboard.writeText(text);
			return;
		}

		const textarea = document.createElement('textarea');
		textarea.value = text;
		textarea.setAttribute('readonly', 'readonly');
		textarea.style.position = 'absolute';
		textarea.style.left = '-9999px';
		document.body.appendChild(textarea);
		textarea.select();

		try {
			document.execCommand('copy');
		} finally {
			document.body.removeChild(textarea);
		}
	};

	document.addEventListener('submit', (event) => {
		const form = event.target;
		if (!(form instanceof HTMLFormElement)) {
			return;
		}

		if (form.dataset.submitting === 'true') {
			event.preventDefault();
			return;
		}

		const submitter = event.submitter instanceof HTMLElement ? event.submitter : null;
		const confirmTemplate = submitter?.dataset.confirmMessage || form.dataset.confirmMessage || '';

		if (confirmTemplate !== '') {
			const confirmName = submitter?.dataset.confirmName || form.dataset.confirmName || '';
			const confirmed = window.confirm(replaceTemplateTokens(confirmTemplate, {
				name: confirmName,
			}));

			if (!confirmed) {
				event.preventDefault();
				return;
			}
		}

		if (!submitter) {
			return;
		}

		const loadingLabel = submitter.dataset.loadingLabel || '';
		if (loadingLabel !== '') {
			if (submitter instanceof HTMLInputElement) {
				submitter.dataset.originalLabel = submitter.value;
				submitter.value = loadingLabel;
			} else {
				submitter.dataset.originalLabel = submitter.textContent || '';
				submitter.textContent = loadingLabel;
			}
		}

		submitter.classList.add('is-loading');
		submitter.setAttribute('aria-busy', 'true');
		submitter.setAttribute('disabled', 'disabled');
		form.dataset.submitting = 'true';
	});

	document.querySelectorAll('[data-copy-text]').forEach((button) => {
		button.addEventListener('click', async () => {
			if (!(button instanceof HTMLButtonElement)) {
				return;
			}

			const copyText = button.dataset.copyText || '';
			if (copyText === '') {
				return;
			}

			const defaultLabel = button.dataset.defaultLabel || button.textContent || 'Copy URL';
			const successLabel = button.dataset.successLabel || 'Copied';
			const errorLabel = button.dataset.errorLabel || 'Copy failed';

			button.disabled = true;

			try {
				await copyTextToClipboard(copyText);
				button.textContent = successLabel;
				button.classList.add('is-success-feedback');
			} catch (error) {
				button.textContent = errorLabel;
				button.classList.add('is-error-feedback');
			}

			window.setTimeout(() => {
				button.textContent = defaultLabel;
				button.disabled = false;
				button.classList.remove('is-success-feedback', 'is-error-feedback');
			}, 1600);
		});
	});

	const uploadFilterButtons = Array.from(document.querySelectorAll('[data-upload-filter]'));
	const uploadCards = Array.from(document.querySelectorAll('[data-upload-groups]'));
	const uploadsSummary = document.getElementById('alpiUploadsSummary');
	const uploadsCountBadge = document.getElementById('alpiUploadsCount');
	const uploadsFilteredEmpty = document.getElementById('alpiUploadsFilteredEmpty');
	const uploadsFilteredEmptyTitle = document.getElementById('alpiUploadsFilteredEmptyTitle');
	const uploadsFilteredEmptyCopy = document.getElementById('alpiUploadsFilteredEmptyCopy');

	const updateUploadsFilterUi = (filterButton) => {
		if (!filterButton || uploadCards.length === 0) {
			return;
		}

		const selectedFilter = filterButton.dataset.uploadFilter || 'all';
		const singularLabel = filterButton.dataset.summarySingular || 'file';
		const pluralLabel = filterButton.dataset.summaryPlural || 'files';
		let visibleCount = 0;

		uploadFilterButtons.forEach((button) => {
			const isActive = button === filterButton;
			button.classList.toggle('active', isActive);
			button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
		});

		uploadCards.forEach((card) => {
			const groups = (card.dataset.uploadGroups || '')
				.split(',')
				.map((group) => group.trim())
				.filter(Boolean);
			const shouldShow = selectedFilter === 'all' || groups.includes(selectedFilter);

			card.hidden = !shouldShow;
			if (shouldShow) {
				visibleCount += 1;
			}
		});

		if (uploadsSummary) {
			if (selectedFilter === 'all') {
				uploadsSummary.textContent = `${uploadCards.length} ${uploadCards.length === 1 ? 'file' : 'files'} in the library. Newest uploads appear first.`;
			} else if (visibleCount === 0) {
				uploadsSummary.textContent = `No ${pluralLabel} are in the library right now.`;
			} else {
				uploadsSummary.textContent = `${visibleCount} ${visibleCount === 1 ? singularLabel : pluralLabel} shown from ${uploadCards.length} files.`;
			}
		}

		if (uploadsCountBadge) {
			uploadsCountBadge.textContent = selectedFilter === 'all'
				? `${uploadCards.length} files`
				: `${visibleCount} shown`;
		}

		if (uploadsFilteredEmpty) {
			const shouldShowEmpty = selectedFilter !== 'all' && visibleCount === 0;
			uploadsFilteredEmpty.hidden = !shouldShowEmpty;

			if (shouldShowEmpty) {
				if (uploadsFilteredEmptyTitle) {
					uploadsFilteredEmptyTitle.textContent = `No ${pluralLabel} yet`;
				}

				if (uploadsFilteredEmptyCopy) {
					uploadsFilteredEmptyCopy.textContent = `Try another filter or upload a new ${singularLabel}.`;
				}
			}
		}
	};

	if (uploadFilterButtons.length > 0 && uploadCards.length > 0) {
		uploadFilterButtons.forEach((button) => {
			button.addEventListener('click', () => {
				updateUploadsFilterUi(button);
			});
		});

		updateUploadsFilterUi(uploadFilterButtons[0]);
	}
});


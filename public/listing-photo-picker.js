const picker = document.querySelector('[data-photo-picker]');

if (picker) {
    const input = picker.querySelector('input[type="file"]');
    const preview = picker.querySelector('[data-photo-preview]');
    const placeholder = picker.querySelector('[data-photo-placeholder]');
    const clear = picker.querySelector('[data-photo-clear]');
    const status = picker.querySelector('#photo-status');
    const originalSrc = preview.getAttribute('src');
    const originalAlt = preview.alt;
    let previewUrl = null;

    function restorePreview() {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }

        if (originalSrc) {
            preview.src = originalSrc;
        } else {
            preview.removeAttribute('src');
        }

        preview.alt = originalAlt;
        preview.hidden = !originalSrc;
        placeholder.hidden = Boolean(originalSrc);
    }

    function showError() {
        input.setCustomValidity(picker.dataset.previewError);
        status.textContent = picker.dataset.previewError;
    }

    async function updatePreview() {
        restorePreview();
        input.setCustomValidity('');
        status.textContent = '';

        const file = input.files[0];
        clear.hidden = !file;
        if (!file) {
            return;
        }

        if (file.size > 5 * 1024 * 1024 || (file.type && !input.accept.split(',').includes(file.type))) {
            showError();
            return;
        }

        status.textContent = file.name;
        const url = URL.createObjectURL(file);
        previewUrl = url;
        const image = new Image();
        image.src = url;

        try {
            await image.decode();
        } catch {
            if (previewUrl === url) {
                restorePreview();
                showError();
            }
            return;
        }

        if (previewUrl === url) {
            preview.src = url;
            preview.alt = picker.dataset.previewAlt;
            preview.hidden = false;
            placeholder.hidden = true;
        }
    }

    input.addEventListener('change', updatePreview);
    clear.addEventListener('click', () => {
        input.value = '';
        updatePreview();
        input.focus();
    });

    picker.classList.add('photo-picker-enhanced');
    updatePreview();
}

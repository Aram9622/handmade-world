const gallery = document.querySelector('[data-listing-gallery]');

if (gallery) {
    const main = gallery.querySelector('[data-gallery-main]');
    const image = main.querySelector('img');
    const dialog = gallery.querySelector('[data-gallery-dialog]');
    const dialogImage = dialog.querySelector('img');

    gallery.addEventListener('click', event => {
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;

        const thumb = event.target.closest('[data-gallery-thumb]');
        if (thumb) {
            event.preventDefault();
            gallery.querySelector('[data-gallery-thumb][aria-current]')?.removeAttribute('aria-current');
            thumb.setAttribute('aria-current', 'true');
            main.href = thumb.href;
            image.src = thumb.href;
            return;
        }

        if (event.target.closest('[data-gallery-main]') && typeof dialog.showModal === 'function') {
            event.preventDefault();
            dialogImage.src = main.href;
            dialog.showModal();
        }
    });

    dialog.addEventListener('click', event => {
        if (event.target === dialog) dialog.close();
    });
    dialog.addEventListener('close', () => main.focus());
}

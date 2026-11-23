const gallery = document.querySelector('[data-listing-gallery]');

if (gallery) {
    const main = gallery.querySelector('[data-gallery-main]');
    const image = main.querySelector('img');
    gallery.addEventListener('click', event => {
        const thumb = event.target.closest('[data-gallery-thumb]');
        if (!thumb || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;

        event.preventDefault();
        gallery.querySelector('[data-gallery-thumb][aria-current]')?.removeAttribute('aria-current');
        thumb.setAttribute('aria-current', 'true');
        main.href = thumb.href;
        image.src = thumb.href;
    });
}

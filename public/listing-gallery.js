const gallery = document.querySelector('[data-listing-gallery]');

if (gallery) {
    const main = gallery.querySelector('[data-gallery-main]');
    const image = main.querySelector('img');
    const dialog = gallery.querySelector('[data-gallery-dialog]');
    const dialogImage = dialog.querySelector('img');
    const thumbs = [...gallery.querySelectorAll('[data-gallery-thumb]')];
    const counters = gallery.querySelectorAll('[data-gallery-counter]');
    const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
    let selected = 0;
    let transition = null;

    function select(index) {
        const next = (index + thumbs.length) % thumbs.length;
        if (next === selected) return;

        thumbs[selected].removeAttribute('aria-current');
        selected = next;
        const thumb = thumbs[selected];
        thumb.setAttribute('aria-current', 'true');
        main.href = thumb.href;
        image.src = thumb.href;
        if (dialog.open) dialogImage.src = thumb.href;

        for (const counter of counters) {
            counter.textContent = (selected + 1) + ' / ' + thumbs.length;
        }

        const activeImage = dialog.open ? dialogImage : image;
        transition?.cancel();
        if (!motion.matches) {
            transition = activeImage.animate([{opacity: .55}, {opacity: 1}], {duration: 180, easing: 'ease-out'});
        }
    }

    gallery.addEventListener('click', event => {
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;

        const thumb = event.target.closest('[data-gallery-thumb]');
        const arrow = event.target.closest('[data-gallery-step]');
        if (thumb) {
            event.preventDefault();
            select(thumbs.indexOf(thumb));
        } else if (arrow) {
            select(selected + Number(arrow.dataset.galleryStep));
        } else if (event.target.closest('[data-gallery-main]') && typeof dialog.showModal === 'function') {
            event.preventDefault();
            dialogImage.src = main.href;
            dialog.showModal();
        }
    });

    if (thumbs.length > 1) {
        for (const controls of gallery.querySelectorAll('[data-gallery-controls]')) {
            controls.hidden = false;
        }

        gallery.addEventListener('keydown', event => {
            if (event.altKey || event.ctrlKey || event.metaKey || event.shiftKey) return;
            if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;

            event.preventDefault();
            select(selected + (event.key === 'ArrowLeft' ? -1 : 1));
        });

        for (const surface of gallery.querySelectorAll('[data-gallery-swipe]')) {
            let start = null;
            surface.addEventListener('touchstart', event => {
                start = event.touches.length === 1 ? event.touches[0] : null;
            }, {passive: true});
            surface.addEventListener('touchcancel', () => { start = null; });
            surface.addEventListener('touchend', event => {
                if (!start) return;
                const end = event.changedTouches[0];
                const horizontal = end.clientX - start.clientX;
                const vertical = end.clientY - start.clientY;
                start = null;
                if (Math.abs(horizontal) < 45 || Math.abs(horizontal) <= Math.abs(vertical)) return;

                if (event.cancelable) event.preventDefault();
                select(selected + (horizontal < 0 ? 1 : -1));
            }, {passive: false});
        }
    }

    dialog.addEventListener('click', event => {
        if (event.target === dialog) dialog.close();
    });
    dialog.addEventListener('close', () => main.focus());
}

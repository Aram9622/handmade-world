const sharePanel = document.querySelector('[data-listing-share]');
const link = sharePanel.querySelector('[name="share_url"]');
const feedback = sharePanel.querySelector('[role="status"]');
const nativeShare = sharePanel.querySelector('[data-native-share]');
const copyLink = sharePanel.querySelector('[data-copy-link]');

if (typeof navigator.share === 'function') {
    nativeShare.hidden = false;
    nativeShare.addEventListener('click', async () => {
        feedback.textContent = '';
        try {
            await navigator.share({ text: sharePanel.dataset.message, url: link.value });
        } catch (error) {
            if (error.name !== 'AbortError') {
                feedback.textContent = sharePanel.dataset.shareError;
            }
        }
    });
}

copyLink.hidden = false;
copyLink.addEventListener('click', async () => {
    try {
        await navigator.clipboard.writeText(link.value);
        feedback.textContent = sharePanel.dataset.copied;
    } catch {
        link.focus();
        link.select();
        feedback.textContent = sharePanel.dataset.copyFallback;
    }
});

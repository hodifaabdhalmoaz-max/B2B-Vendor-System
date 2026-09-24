document.querySelector('[data-copy-description]')?.addEventListener('click', async (event) => {
    const button = event.currentTarget;
    const text = document.getElementById('marketing-description').textContent.trim();
    const feedback = document.getElementById('copy-feedback');
    const fallback = document.getElementById('copy-fallback');
    try {
        await navigator.clipboard.writeText(text);
        feedback.textContent = button.dataset.success;
        fallback.hidden = true;
    } catch {
        fallback.value = text;
        fallback.hidden = false;
        fallback.focus();
        fallback.select();
        feedback.textContent = button.dataset.fallback;
    }
});

document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('submit', (event) => {
        const form = event.target.closest('[data-confirm]');
        if (!form) {
            return;
        }

        const message = form.getAttribute('data-confirm');
        if (message && !window.confirm(message)) {
            event.preventDefault();
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const toggleButton = document.getElementById('forgot-password-btn');
    const resetForm = document.getElementById('reset-password-form');

    if (!toggleButton || !resetForm) {
        return;
    }

    toggleButton.addEventListener('click', () => {
        const isHidden = resetForm.style.display === 'none' || resetForm.style.display === '';
        resetForm.style.display = isHidden ? 'block' : 'none';
    });
});

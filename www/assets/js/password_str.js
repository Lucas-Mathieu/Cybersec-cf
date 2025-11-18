document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('password').addEventListener('input', function() {
        const password = this.value;
        const strengthBar = document.getElementById('strength-bar');
        const strengthText = document.getElementById('strength-text');
        
        let strength = 0;
        
        // Check password criteria
        if (password.length >= 8) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/\d/.test(password)) strength++;
        if (/[@$!%*?&]/.test(password)) strength++;
        
        // Update strength bar and text
        strengthBar.className = 'strength-bar';
        strengthText.textContent = '';
        
        if (password.length === 0) {
            strengthBar.className = 'strength-bar';
            strengthText.textContent = '';
        } else if (strength <= 2) {
            strengthBar.className = 'strength-bar weak';
            strengthText.textContent = 'Faible';
        } else if (strength <= 4) {
            strengthBar.className = 'strength-bar medium';
            strengthText.textContent = 'Moyen';
        } else {
            strengthBar.className = 'strength-bar strong';
            strengthText.textContent = 'Fort';
        }
    });
});
document.addEventListener('DOMContentLoaded', function () {
    const toggleMenuButton = document.querySelector('.toggle-menu');
    const mobileMenu = document.querySelector('.mobile-menu');

    toggleMenuButton.addEventListener('click', function (e) {
        e.preventDefault();
        mobileMenu.classList.toggle('active');
    });
});

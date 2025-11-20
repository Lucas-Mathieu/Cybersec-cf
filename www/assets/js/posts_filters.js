document.addEventListener('DOMContentLoaded', () => {
    const filterButtons = document.querySelectorAll('.toggle-filter-btn');
    const filterContainers = document.querySelectorAll('.filter-options');

    filterButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const targetId = button.getAttribute('data-target');
            const target = document.getElementById(targetId);
            if (!target) {
                return;
            }

            const isHidden = target.style.display === 'none' || !target.style.display;

            filterContainers.forEach((container) => {
                container.style.display = 'none';
            });

            target.style.display = isHidden ? 'flex' : 'none';
        });
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.filter-group') && !event.target.closest('.toggle-filter-btn')) {
            filterContainers.forEach((container) => {
                container.style.display = 'none';
            });
        }
    });

    document.querySelectorAll('.post-text').forEach((textBlock) => {
        const isOverflowing = textBlock.scrollHeight > textBlock.clientHeight;
        if (isOverflowing) {
            textBlock.classList.add('overflow');
        }
    });
});

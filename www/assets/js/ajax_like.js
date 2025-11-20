const UNAUTHORIZED_MESSAGE = 'Non autoris\u00e9';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.post-card').forEach((card) => {
        card.addEventListener('click', function (event) {
            if (!event.target.closest('.like-btn')) {
                window.location.href = this.dataset.href;
            }
        });
    });

    document.querySelectorAll('.like-btn').forEach((button) => {
        button.addEventListener('click', async function (event) {
            event.stopPropagation();

            const postId = button.dataset.postId;

            const response = await fetch('/ajax/toggle-like', {
                method: 'POST',
                body: new URLSearchParams({ post_id: postId }),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
            });

            const result = await response.json();

            if (result.success) {
                const icon = button.querySelector('.like-icon');
                const countSpan = button.querySelector('.like-count');

                icon.classList.toggle('is-liked', Boolean(result.liked));

                countSpan.textContent = result.like_count;
            } else if (result.error === UNAUTHORIZED_MESSAGE) {
                alert('Veuillez vous connecter \u00e0 un compte v\u00e9rifi\u00e9 pour cela.');
            } else {
                alert(result.error);
            }
        });
    });
});

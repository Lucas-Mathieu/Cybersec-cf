// Post click management
document.querySelectorAll('.post-card').forEach(card => {
    card.addEventListener('click', function(event) {
        // Check if the click was on the like button
        if (!event.target.closest('.like-btn')) {
            window.location.href = this.dataset.href;
        }
    });
});

// Like / Unlike functionality
document.querySelectorAll('.like-btn').forEach(button => {
    button.addEventListener('click', async function(event) {
        event.stopPropagation(); // Avoids triggering the card click event

        const postId = button.dataset.postId;

        const response = await fetch('/ajax/toggle-like', {
            method: 'POST',
            body: new URLSearchParams({ post_id: postId }),
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        });

        const result = await response.json();

        if (result.success) {
            const icon = button.querySelector('i');
            const countSpan = button.querySelector('.like-count');

            if (result.liked) {
                icon.classList.remove('fa-heart-o');
                icon.classList.add('fa-heart');
                icon.style.color = 'red';
            } else {
                icon.classList.remove('fa-heart');
                icon.classList.add('fa-heart-o');
                icon.style.color = 'gray';
            }

            countSpan.textContent = result.like_count;
        } else if (result.error === 'Non autorisé') {
            alert('Veuillez vous connecter à un compte vérifié pour cela.');
        } else {
            alert(result.error);
        }
    });
});
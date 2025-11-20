document.addEventListener('DOMContentLoaded', () => {
    const commentsList = document.querySelector('.comments-list');
    const toggleCommentBtn = document.getElementById('toggle-comment-btn');
    const commentForm = document.getElementById('comment-form');

    if (toggleCommentBtn && commentForm) {
        toggleCommentBtn.addEventListener('click', () => {
            const isHidden = commentForm.style.display === 'none' || commentForm.style.display === '';
            commentForm.style.display = isHidden ? 'block' : 'none';
        });
    }

    if (!commentsList) {
        return;
    }

    commentsList.addEventListener('click', (event) => {
        if (event.target.classList.contains('reply-btn')) {
            const button = event.target;
            const id = button.dataset.commentId;
            const replyForm = commentsList.querySelector(`.reply-form[data-comment-id="${id}"]`);
            if (replyForm) {
                const isHidden = replyForm.style.display === 'none' || replyForm.style.display === '';
                replyForm.style.display = isHidden ? 'block' : 'none';
            }
        }
    });

    commentsList.addEventListener('submit', async (event) => {
        if (event.target.classList.contains('reply-form')) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);

            try {
                const response = await fetch('/ajax/add-reply', {
                    method: 'POST',
                    body: formData,
                });
                const result = await response.json();
                if (result.success) {
                    const replyList = form.closest('.comment').querySelector('.comment-replies');
                    if (replyList) {
                        replyList.insertAdjacentHTML('beforeend', result.html);
                    } else {
                        const newList = document.createElement('ul');
                        newList.className = 'comment-replies';
                        newList.innerHTML = result.html;
                        form.closest('.comment').appendChild(newList);
                    }
                    form.reset();
                    form.style.display = 'none';
                } else {
                    alert(result.error);
                }
            } catch (error) {
                console.error('Error while adding reply:', error);
            }
        }
    });

    if (commentForm) {
        commentForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const formData = new FormData(commentForm);

            try {
                const response = await fetch('/ajax/add-comment', {
                    method: 'POST',
                    body: formData,
                });
                const result = await response.json();
                if (result.success) {
                    const noCommentMsg = document.querySelector('.no-comments');
                    if (noCommentMsg) {
                        noCommentMsg.remove();
                    }
                    commentsList.insertAdjacentHTML('beforeend', result.html);
                    commentForm.reset();
                    commentForm.style.display = 'none';
                } else {
                    alert(result.error);
                }
            } catch (error) {
                console.error('Error while adding comment:', error);
            }
        });
    }
});

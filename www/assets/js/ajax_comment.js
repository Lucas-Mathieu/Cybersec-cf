// Select the comments list (parent element for event delegation)
const commentsList = document.querySelector('.comments-list');

commentsList.addEventListener('click', (e) => {
    if (e.target.classList.contains('reply-btn')) {
        const btn = e.target;
        const id = btn.dataset.commentId;
        const replyForm = commentsList.querySelector(`.reply-form[data-comment-id="${id}"]`);
        if (replyForm) {
            replyForm.style.display = replyForm.style.display === 'none' ? 'block' : 'none';
            console.log(`Reply form for comment ${id} toggled.`);
        }
    }
});

// Event delegation for reply forms
commentsList.addEventListener('submit', async function(e) {
    if (e.target.classList.contains('reply-form')) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);

        try {
            const response = await fetch('/ajax/add-reply', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (result.success) {
                const replyList = form.closest('.comment').querySelector('.comment-replies');
                if (replyList) {
                    replyList.innerHTML += result.html;
                } else {
                    const newList = document.createElement('ul');
                    newList.className = 'comment-replies';
                    newList.innerHTML = result.html;
                    form.closest('.comment').appendChild(newList);
                }
                form.reset();
                form.style.display = 'none'; // Hide after submission
            } else {
                alert(result.error);
            }
        } catch (error) {
            console.error('Error while adding reply:', error);
        }
    }
});

// Handle the toggle button for the main comment form
const toggleCommentBtn = document.getElementById('toggle-comment-btn');
const commentForm = document.getElementById('comment-form');

if (toggleCommentBtn && commentForm) {
    toggleCommentBtn.addEventListener('click', () => {
        commentForm.style.display = commentForm.style.display === 'none' ? 'block' : 'none';
    });
}

// Handle submission of the main comment form
if (commentForm) {
    commentForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);

        try {
            const response = await fetch('/ajax/add-comment', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (result.success) {
                const commentList = document.querySelector('.comments-list');
                const noCommentMsg = document.querySelector('.no-comments');
                if (noCommentMsg) {
                    noCommentMsg.remove();
                }
                commentList.insertAdjacentHTML('beforeend', result.html); // Append the new comment
                form.reset();
                form.style.display = 'none'; // Hide after submission
            } else {
                alert(result.error);
            }
        } catch (error) {
            console.error('Error while adding comment:', error);
        }
    });
}
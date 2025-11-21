<li class="reply">
    <div class="comment-meta">
        <img src="<?= htmlspecialchars($lastReply['commenter_pfp']) ?>" alt="Auteur de la réponse" class="commenter-pfp" />
        <strong><?= htmlspecialchars($lastReply['replier_name']) ?></strong>
        <span><?= date('d M Y H:i', strtotime($lastReply['date'])) ?></span>
    </div>
    <p><?= nl2br(htmlspecialchars($lastReply['text'])) ?></p>
    <?php if (!empty($_SESSION['user']) && $_SESSION['user']['is_admin']): ?>
        <div class="reply-actions">
            <form action="/delete-reply/<?= $lastReply['id'] ?>" method="POST" class="inline-form" data-confirm="<?= htmlspecialchars('Êtes-vous sûr de vouloir supprimer cette réponse ?', ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="reply_id" value="<?= $lastReply['id'] ?>">
                <button type="submit" class="btn btn-red">Supprimer</button>
            </form>
        </div>
    <?php endif; ?>
</li>

<?php require_once '../app/views/partials/header.php'; ?>

<main class="post-detail-container">
    <article class="post-detail">
        <div class="post-author">
            <img src="<?= htmlspecialchars($post['author_pfp']) ?>" alt="Auteur" class="author-pfp" />
            <p class="post-meta">
                <strong><?= htmlspecialchars($post['author_name']) ?></strong> - 
                <?= date('d M Y H:i', strtotime($post['date_created'])) ?>
            </p>

            <?php if (!empty($_SESSION['user']) && ($_SESSION['user']['id'] === $post['id_user'] || $_SESSION['user']['is_admin'])): ?>
                <div class="post-actions">
                    <?php if ($_SESSION['user']['id'] === $post['id_user']): ?>
                        <a href="/edit-post/<?= $post['id'] ?>" class="btn btn-primary">Modifier</a>
                    <?php endif; ?>

                    <?php if (!$_SESSION['user']['is_admin'] && $_SESSION['user']['id'] === $post['id_user']): ?>
                        <form action="/delete-post/<?= $post['id'] ?>" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir archiver ce post ?');" style="display: inline;">
                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                            <button type="submit" class="btn btn-red">Archiver</button>
                        </form>
                    <?php endif; ?>

                    <?php if ($_SESSION['user']['is_admin']): ?>
                        <a href="/post-history/<?= $post['id'] ?>" class="btn btn-primary">Historique</a>
                        <?php if ($post['is_deleted']): ?>
                            <form action="/restore-post/<?= $post['id'] ?>" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir restaurer ce post ?');" style="display: inline;">
                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                <button type="submit" class="btn btn-primary">Restaurer</button>
                            </form>

                            <form action="/nuke-post/<?= $post['id'] ?>" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce post ?');" style="display: inline;">
                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                <button type="submit" class="btn btn-red">Supprimer</button>
                            </form>
                        <?php else: ?>
                            <form action="/delete-post/<?= $post['id'] ?>" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir archiver ce post ?');" style="display: inline;">
                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                <button type="submit" class="btn btn-red">Archiver</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <h1><?= htmlspecialchars($post['title']) ?></h1>

        <div class="post-tags-techs">
            <?php if (!empty($post['tags'])): ?>
                <div class="tags">
                    <?php foreach ($post['tags'] as $tag): ?>
                        <span class="tag-badge"><?= htmlspecialchars($tag['name']) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($post['techs'])): ?>
                <div class="techs">
                    <?php foreach ($post['techs'] as $tech): ?>
                        <span class="tech-badge"><?= htmlspecialchars($tech['name']) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($post['image'])) : ?>
            <div class="post-image-wrapper">
                <img src="<?= htmlspecialchars($post['image']) ?>" alt="Image du post" class="post-image-full" />
            </div>
        <?php endif; ?>

        <div class="post-text, post-text-detail">
            <?= nl2br(htmlspecialchars($post['text'])) ?>
        </div>

        <button class="like-btn" data-post-id="<?= $post['id'] ?>" aria-label="Like post">
            <i class="fa <?= $post['liked'] ? 'fa-heart' : 'fa-heart-o' ?>" style="color: <?= $post['liked'] ? 'red' : 'gray' ?>"></i>
            <span class="like-count"><?= $post['like_count'] ?? 0 ?></span>
        </button>
    </article>

    <section class="comments-section">
        <h2>Commentaires</h2>

        <?php if (!empty($_SESSION['user']) && $_SESSION['user']['is_verified']) : ?>
            <button id="toggle-comment-btn" class="btn">Commenter</button>
            <form id="comment-form" class="comment-form" style="display: none;">
                <textarea name="text" placeholder="Écrire un commentaire..." required></textarea>
                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                <button type="submit" id="submit-comment-btn">Envoyer</button>
            </form>
        <?php endif; ?>

        <ul class="comments-list">
            <?php if (!empty($comments)) : ?>
                <?php foreach ($comments as $comment) : ?>
                    <li class="comment">
                        <div class="comment-meta">
                            <img src="<?= htmlspecialchars($comment['commenter_pfp']) ?>" alt="Auteur du commentaire" class="commenter-pfp" />
                            <strong><?= htmlspecialchars($comment['commenter_name']) ?></strong>
                            <span><?= date('d M Y H:i', strtotime($comment['date'])) ?></span>
                        </div>
                        <p><?= nl2br(htmlspecialchars($comment['text'])) ?></p>

                        <div class="comment-actions">
                            <?php if (!empty($_SESSION['user']) && $_SESSION['user']['is_verified']) : ?>
                                <button class="reply-btn btn" data-comment-id="<?= $comment['id'] ?>">Répondre</button>
                                <form class="reply-form" data-comment-id="<?= $comment['id'] ?>" style="display: none;">
                                    <textarea name="text" placeholder="Votre réponse..." required></textarea>
                                    <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    <button type="submit" class="submit-reply-btn">Envoyer</button>
                                </form>
                            <?php endif; ?>
                            <?php if (!empty($_SESSION['user']) && $_SESSION['user']['is_admin']): ?>
                                <form action="/delete-comment/<?= $comment['id'] ?>" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?');" style="display: inline;">
                                    <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                    <button type="submit" class="btn btn-red">Supprimer</button>
                                </form>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($comment['replies'])) : ?>
                            <ul class="comment-replies">
                                <?php foreach ($comment['replies'] as $reply) : ?>
                                    <li class="reply">
                                        <div class="comment-meta">
                                            <img src="<?= htmlspecialchars($reply['commenter_pfp']) ?>" alt="Auteur de la réponse" class="commenter-pfp" />
                                            <strong><?= htmlspecialchars($reply['replier_name']) ?></strong>
                                            <span><?= date('d M Y H:i', strtotime($reply['date'])) ?></span>
                                        </div>
                                        <p><?= nl2br(htmlspecialchars($reply['text'])) ?></p>
                                        <?php if (!empty($_SESSION['user']) && $_SESSION['user']['is_admin']): ?>
                                            <div class="reply-actions">
                                                <form action="/delete-reply/<?= $reply['id'] ?>" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette réponse ?');" style="display: inline;">
                                                    <input type="hidden" name="reply_id" value="<?= $reply['id'] ?>">
                                                    <button type="submit" class="btn btn-red">Supprimer</button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            <?php else : ?>
                <li class="no-comments">Aucun commentaire pour l’instant.</li>
            <?php endif; ?>
        </ul>
    </section>
</main>

<script src='/assets/js/ajax_comment.js'></script>
<script src='/assets/js/ajax_like.js'></script>

<?php require_once '../app/views/partials/footer.php'; ?>
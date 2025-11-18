<?php require_once '../app/views/partials/header.php'; ?>

<main class="posts-container">
    <?php if ($archive): ?>
        <h1 class="posts-title">Archive</h1>
    <?php else : ?>
        <h1 class="posts-title">Tous les posts</h1>
    <?php endif; ?>

    <?php if (isset($_SESSION['user']) && $_SESSION['user']['is_verified'] && !$archive) : ?>
        <a href="/create-post" class="create-post-btn">Créer un post</a>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <p class="error-msg"><?= $_SESSION['error'] ?></p>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!$archive): ?>
    <!-- Formulaire de recherche et filtrage -->
    <form method="GET" action="/posts" class="search-filter-form">
        <div class="search-bar">
            <label for="search">Rechercher :</label>
            <input type="text" id="search" name="search" placeholder="Auteur ou contenu de post" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>

        <div class="filters">
            <div class="filter-group">
                <button type="button" class="toggle-filter-btn" data-target="tags-filter">Tags</button>
                <div id="tags-filter" class="filter-options option-group-search" style="display: none;">
                    <?php foreach ($tags as $tag): ?>
                        <input type="checkbox" name="tags[]" id="tag-<?= $tag['id'] ?>" value="<?= $tag['id'] ?>" class="hidden-checkbox" <?= in_array($tag['id'], $_GET['tags'] ?? []) ? 'checked' : '' ?>>
                        <label for="tag-<?= $tag['id'] ?>" class="option-label"><?= htmlspecialchars($tag['name']) ?></label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="filter-group">
                <button type="button" class="toggle-filter-btn" data-target="techs-filter">Technologies</button>
                <div id="techs-filter" class="filter-options option-group-search" style="display: none;">
                    <?php foreach ($techs as $tech): ?>
                        <input type="checkbox" name="techs[]" id="tech-<?= $tech['id'] ?>" value="<?= $tech['id'] ?>" class="hidden-checkbox" <?= in_array($tech['id'], $_GET['techs'] ?? []) ? 'checked' : '' ?>>
                        <label for="tech-<?= $tech['id'] ?>" class="option-label"><?= htmlspecialchars($tech['name']) ?></label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="sort-group">
                <label for="sort">Trier par :</label>
                <select id="sort" name="sort">
                    <option value="likes_desc" <?= ($_GET['sort'] ?? '') === 'likes_desc' ? 'selected' : '' ?>>Likes ↓</option>
                    <option value="likes_asc" <?= ($_GET['sort'] ?? '') === 'likes_asc' ? 'selected' : '' ?>>Likes ↑</option>
                    <option value="created_desc" <?= ($_GET['sort'] ?? '') === 'created_desc' ? 'selected' : '' ?>>Date création ↓</option>
                    <option value="created_asc" <?= ($_GET['sort'] ?? '') === 'created_asc' ? 'selected' : '' ?>>Date création ↑</option>
                    <option value="modified_desc" <?= ($_GET['sort'] ?? '') === 'modified_desc' ? 'selected' : '' ?>>Date modification ↓</option>
                    <option value="modified_asc" <?= ($_GET['sort'] ?? '') === 'modified_asc' ? 'selected' : '' ?>>Date modification ↑</option>
                </select>
            </div>
        </div>

        <button type="submit" class="filter-btn">Appliquer</button>
    </form>
    <?php endif; ?>

    <?php if (!empty($posts)) : ?>
        <div class="posts-grid">
            <?php foreach ($posts as $post) : ?>
                <div class="post-card" data-href="/post/<?= $post['id'] ?>">
                    <div class="post-author">
                        <img src="<?= htmlspecialchars($post['author_pfp']) ?>" alt="Auteur" class="author-pfp" />
                        <p class="post-meta">
                            <strong><?= htmlspecialchars($post['author_name']) ?></strong>
                            <?= date('d M Y H:i', strtotime($post['date_created'])) ?>
                        </p>
                    </div>

                    <h2><?= htmlspecialchars($post['title']) ?></h2>

                    <?php if (!empty($post['tags'])): ?>
                        <div class="post-tags">
                            <?php foreach ($post['tags'] as $tag): ?>
                                <span class="tag"><?= htmlspecialchars($tag['name']) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($post['image'])) : ?>
                        <div class="post-image-wrapper">
                            <img src="<?= htmlspecialchars($post['image']) ?>" alt="Image du post" class="post-image" />
                        </div>
                    <?php endif; ?>

                    <div class="post-text"><?= nl2br(htmlspecialchars($post['text'])) ?></div>

                    <button class="like-btn" data-post-id="<?= $post['id'] ?>" aria-label="Like post">
                        <i class="fa <?= $post['liked'] ? 'fa-heart' : 'fa-heart-o' ?>" style="color: <?= $post['liked'] ? 'red' : 'gray' ?>"></i>
                        <span class="like-count"><?= $post['like_count'] ?? 0 ?></span>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <p>Aucun post ne correspond aux critères.</p>
    <?php endif; ?>
</main>

<script src='/assets/js/ajax_like.js'></script>
<script>
// Gestion des boutons de bascule pour les filtres
document.querySelectorAll('.toggle-filter-btn').forEach(button => {
    button.addEventListener('click', () => {
        const targetId = button.getAttribute('data-target');
        const target = document.getElementById(targetId);
        const isHidden = target.style.display === 'none' || !target.style.display;

        // Masquer tous les autres filtres
        document.querySelectorAll('.filter-options').forEach(opt => {
            opt.style.display = 'none';
        });

        // Afficher ou masquer le filtre ciblé
        target.style.display = isHidden ? 'flex' : 'none';
    });
});

document.addEventListener('click', (e) => {
    if (!e.target.closest('.filter-group') && !e.target.closest('.toggle-filter-btn')) {
        document.querySelectorAll('.filter-options').forEach(opt => {
            opt.style.display = 'none';
        });
    }
});

// Detect text overflow and add 'overflow' class
document.querySelectorAll('.post-text').forEach(textDiv => {
    const isOverflowing = textDiv.scrollHeight > textDiv.clientHeight;
    if (isOverflowing) {
        textDiv.classList.add('overflow');
    }
});

// Make post cards clickable
document.querySelectorAll('.post-card').forEach(card => {
    card.addEventListener('click', (e) => {
        // Avoid triggering navigation when clicking the like button
        if (!e.target.closest('.like-btn')) {
            window.location.href = card.getAttribute('data-href');
        }
    });
});
</script>

<?php require_once '../app/views/partials/footer.php'; ?>
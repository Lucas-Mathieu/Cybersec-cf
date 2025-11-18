<?php require_once '../app/views/partials/header.php'; ?>

<main class="post-detail-container">
    <section class="post-detail history-section">
        <h1 class="history-title">Historique du post</h1>
        <?php if (empty($versions)): ?>
            <p class="no-versions">Aucune version archivée pour ce post.</p>
        <?php else: ?>
            <?php foreach ($versions as $version): ?>
                <article class="version-card">
                    <h2 class="version-title">
                        <?= htmlspecialchars($version['title']) ?>
                        <?= $version['id'] === null ? '<span class="current-version">(Version actuelle)</span>' : '' ?>
                    </h2>
                    <p class="version-meta"><strong>Date de modification :</strong> <?= date('d M Y H:i', strtotime($version['date_modified'])) ?></p>
                    <?php if ($version['image_path']): ?>
                        <div class="post-image-wrapper">
                            <img src="/<?= htmlspecialchars($version['image_path']) ?>" alt="Image de la version" class="post-image-full" />
                        </div>
                    <?php endif; ?>
                    <div class="post-text, post-text-detail"><?= nl2br(htmlspecialchars($version['text'])) ?></div>
                </article>
                <hr class="version-divider">
            <?php endforeach; ?>
        <?php endif; ?>
        <a href="/post/<?= $post['id'] ?>" class="btn btn-primary">Retour au post</a>
    </section>
</main>

<?php require_once '../app/views/partials/footer.php'; ?>
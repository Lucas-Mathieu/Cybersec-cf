<?php require_once '../app/views/partials/header.php'; ?>

<div class="auth-container">
    <h2 class="auth-title">Modifier le post</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <p class="error-msg" style="color: red"><?= $_SESSION['error'] ?></p>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form action="/update-post/<?php echo $post['id']; ?>" method="POST" enctype="multipart/form-data" class="auth-form">
        <input type="text" lang="fr" name="title" id="title" maxlength="100" placeholder="Titre du post" class="form-input" value="<?php echo htmlspecialchars($post['title']); ?>" required>

        <textarea name="content" lang="fr" id="content" placeholder="Contenu du post" class="form-input" rows="5" required><?php echo htmlspecialchars($post['text']); ?></textarea>

        <input type="file" name="image" id="image" accept="image/*" class="form-input">

        <div>
            <p class="tag"><strong>Tags :</strong></p>
            <div class="option-group">
                <?php foreach ($tags as $tag): ?>
                    <input type="checkbox" name="tags[]" id="tag-<?= $tag['id'] ?>" value="<?= $tag['id'] ?>" class="hidden-checkbox" <?php echo in_array($tag['id'], $post['tags']) ? 'checked' : ''; ?>>
                    <label for="tag-<?= $tag['id'] ?>" class="option-label"><?= htmlspecialchars($tag['name']) ?></label>
                <?php endforeach; ?>
            </div>
        </div>

        <div>
            <p class="tag"><strong>Technologies :</strong></p>
            <div class="option-group">
                <?php foreach ($techs as $tech): ?>
                    <input type="checkbox" name="techs[]" id="tech-<?= $tech['id'] ?>" value="<?= $tech['id'] ?>" class="hidden-checkbox" <?php echo in_array($tech['id'], $post['techs']) ? 'checked' : ''; ?>>
                    <label for="tech-<?= $tech['id'] ?>" class="option-label"><?= htmlspecialchars($tech['name']) ?></label>
                <?php endforeach; ?>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>
</div>

<?php require_once '../app/views/partials/footer.php'; ?>
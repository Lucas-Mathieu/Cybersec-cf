    <?php require_once '../app/views/partials/header.php'; ?>

    <main class="account-container">
        <h1 class="account-title">My Account</h1>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <p class="user-email">Email: <?= htmlspecialchars($user['email']) ?></p>

        <form action="/account" method="POST" class="account-form">
            <label for="name">Nom:</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required class="form-input">
            <input type="submit" value="Mettre à jour" class="btn btn-primary">
        </form>

        <div class="pfp">
            <img src="<?= htmlspecialchars($_SESSION['user']['pfp_path'])?>" alt="Image de profil" class="profile-image">
        </div>

        <form action="/upload-pfp" method="POST" enctype="multipart/form-data" class="upload-pfp-form">
            <input type="file" name="avatar" accept="image/*" required class="form-input">
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>

        <?php if (!$_SESSION['user']['is_verified']): ?>
            <form action="/send-verification-email" method="POST" class="verification-form">
                <button type="submit" class="btn btn-primary">Envoyer le code de vérification</button>
            </form>

            <form action="/verify-email" method="POST" class="verify-code-form">
                <label for="code">Code de vérification:</label>
                <input type="text" id="code" name="code" required class="form-input">
                <button type="submit" class="btn btn-primary">Vérifier</button>
            </form>
        <?php else: ?>
            <p class="verified-message">Votre compte est vérifié.</p>
        <?php endif; ?>

        <div class="account-actions">
            <a href="/logout" class="btn btn-red">Déconnexion</a>
            <a href="/delete-account" class="btn btn-red">Supprimer compte</a>
        </div>
    </main>

    <?php require_once '../app/views/partials/footer.php'; ?>
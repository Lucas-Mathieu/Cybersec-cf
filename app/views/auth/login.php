    <?php require_once '../app/views/partials/header.php'; ?>

    <main class="auth-container">
        <h1 class="auth-title">Login</h1>

        <?php if (!empty($_SESSION['error'])): ?>
            <p class="error-msg"><?= htmlspecialchars($_SESSION['error']) ?></p>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['success'])): ?>
            <p class="success-msg"><?= htmlspecialchars($_SESSION['success']) ?></p>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <form action="/login" method="POST" class="auth-form">
            <input type="email" name="email" placeholder="Email" required class="form-input">
            <input type="password" name="password" placeholder="Password" required class="form-input">
            <button type="submit" class="btn btn-primary">Connexion</button>
        </form>

        <p class="auth-alt-link">
            Vous n'avez pas de compte ? <a href="/register">Créez en un</a>
        </p>

        <button id="forgot-password-btn" class="btn btn-secondary">Mot de passe oublié ?</button>

        <form action="/reset-password" method="POST" id="reset-password-form" class="auth-form" style="display: none;">
            <input type="email" name="email" placeholder="Email" required class="form-input">
            <button type="submit" name="send_code" class="btn btn-primary">Envoyer le code</button>
            <input type="text" name="code" placeholder="Code de réinitialisation" class="form-input">
            <input type="password" name="password" placeholder="Nouveau mot de passe" class="form-input">
            <input type="password" name="password_confirm" placeholder="Confirmer le mot de passe" class="form-input">
            <button type="submit" name="reset_password" class="btn btn-primary">Réinitialiser le mot de passe</button>
        </form>
    </main>

    <script>
        document.getElementById('forgot-password-btn').addEventListener('click', function() {
            const form = document.getElementById('reset-password-form');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        });
    </script>

    <?php require_once '../app/views/partials/footer.php'; ?>
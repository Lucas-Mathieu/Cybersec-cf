    <?php require_once '../app/views/partials/header.php'; ?>

    <main class="auth-container">
        <h1 class="auth-title">Register</h1>
        
        <?php if (!empty($_SESSION['error'])): ?>
            <p class="error-msg"><?= $_SESSION['error'] ?></p>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form action="/register" method="POST" class="auth-form">
            <input type="text" name="name" placeholder="Name" required class="form-input">
            <input type="email" name="email" placeholder="Email (esiee-it.fr)" required class="form-input">
            <input type="password" name="password" id="password" placeholder="Password" required class="form-input">
            <div class="password-strength">
                <div id="strength-bar" class="strength-bar"></div>
                <p id="strength-text" class="strength-text"></p>
            </div>
            <input type="password" name="password_confirm" placeholder="Confirm Password" required class="form-input">
            <button type="submit" class="btn btn-primary">Créer un compte</button>
        </form>

        <p class="auth-alt-link">
            Vous avez déjà un compte ? <a href="/login">Connectez vous</a>
        </p>
    </main>

    <link rel="stylesheet" href="/assets/css/password.css">

    <script src=assets/js/password_str.js></script>

    <?php require_once '../app/views/partials/footer.php'; ?>
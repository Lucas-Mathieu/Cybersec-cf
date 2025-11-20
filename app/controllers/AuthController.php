<?php

require_once __DIR__ . '/../../core/Logger.php';
require_once __DIR__ . '/../../core/Validator.php';

class AuthController
{
    private $userModel;

    public function __construct($userModel)
    {
        $this->userModel = $userModel;
    }

    // Show login form
    public function showLoginForm()
    {
        require '../app/views/auth/login.php';
    }

    // Show register form
    public function showRegisterForm()
    {
        require '../app/views/auth/register.php';
    }

    // Handle login logic
    public function login()
    {
        try {
            $email = Validator::email($_POST['email'] ?? '');
            $password = Validator::password($_POST['password'] ?? '', 'Mot de passe', 4, 128);
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /login');
            exit;
        }

        $user = $this->userModel->getUserByEmail($email);
        $maxAttempts = 5;
        $lockDurationMinutes = 10;

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "Erreur de sécurité (CSRF). Veuillez réessayer.";
            header('Location: /login');
            exit;
        }

        if ($user && !empty($user['is_blocked_until'])) {
            $blockedUntilTs = strtotime($user['is_blocked_until']);
            if ($blockedUntilTs && $blockedUntilTs > time()) {
                Logger::log('login', $email, 'blocked', ['blocked_until' => $user['is_blocked_until']]);
                $_SESSION['error'] = "Compte verrouillé jusqu'au " . date('d/m/Y H:i', $blockedUntilTs) . ".";
                header('Location: /login');
                exit;
            }
            // Block expired, ensure counters are reset
            $this->userModel->updateLoginAttempts($user['id'], 0, null);
            $user['attempts'] = 0;
            $user['is_blocked_until'] = null;
        }

        $passwordMatches = false;
        if ($user) {
            $passwordMatches = password_verify($password, $user['password']);
            if (
                !$passwordMatches &&
                password_get_info($user['password'])['algo'] === 0 &&
                hash_equals($user['password'], $password)
            ) {
                // Legacy plain-text password detected, upgrade it to a hash
                $this->userModel->changeUserPassword($user['id'], $password);
                $passwordMatches = true;
            }
        }

        if ($user && $passwordMatches) {
            $this->userModel->updateLoginAttempts($user['id'], 0, null);
            // Regenerate the session identifier post-authentication to prevent fixation
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_regenerate_id(true);
            }
            $pfpPath = "uploads/pfps/{$user['id']}/avatar.jpg";
            if (!file_exists($pfpPath)) {
                $pfpPath = "uploads/pfps/0/avatar.jpg";
            }

            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'is_verified' => $user['is_verified'],
                'is_admin' => $user['is_admin'],
                'pfp_path' => "/$pfpPath"
            ];

            Logger::log('login', $user['email'], 'success');
            header('Location: /');
            exit;
        } else {
            if ($user) {
                $attempts = (int)($user['attempts'] ?? 0);
                $attempts++;
                $blockedUntil = null;
                if ($attempts >= $maxAttempts) {
                    $blockedUntil = date('Y-m-d H:i:s', time() + ($lockDurationMinutes * 60));
                    $attempts = 0;
                }
                $this->userModel->updateLoginAttempts($user['id'], $attempts, $blockedUntil);
                $context = ['attempts' => $attempts];
                if ($blockedUntil) {
                    $context['blocked_until'] = $blockedUntil;
                }
                Logger::log('login', $user['email'], $blockedUntil ? 'locked' : 'failure', $context);
            } else {
                Logger::log('login', $email, 'failure', ['reason' => 'unknown_email']);
            }
            $_SESSION['error'] = "Email ou mot de passe invalide.";
            header('Location: /login');
            exit;
        }
    }

    // Handle registration logic
    public function register()
    {
        try {
            $name = Validator::string(
                $_POST['name'] ?? '',
                'Nom',
                2,
                80,
                [
                    'pattern' => '/^[\p{L}\s\'-]+$/u',
                    'patternMessage' => "Le nom ne peut contenir que des lettres, espaces, apostrophes ou tirets."
                ]
            );
            $email = Validator::email($_POST['email'] ?? '', ['esiee-it.fr', 'edu.esiee-it.fr']);
            $password = Validator::password($_POST['password'] ?? '', 'Mot de passe', 8, 128, true);
            $passwordConfirm = Validator::password($_POST['password_confirm'] ?? '', 'Confirmation du mot de passe', 8, 128);
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /register');
            exit;
        }

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "Erreur de sécurité (CSRF). Veuillez réessayer.";
            header('Location: /register');
            exit;
                }

        // Check if email already exists
        if ($this->userModel->getUserByEmail($email)) {
            $_SESSION['error'] = "Email déjà utilisée.";
            header('Location: /register');
            exit;
        }

        // Check if passwords match
        if ($password !== $passwordConfirm) {
            $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
            header('Location: /register');
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $this->userModel->createUser($name, $email, $hashedPassword);

        $user = $this->userModel->getUserByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_regenerate_id(true);
            }
            $pfpPath = "uploads/pfps/0/avatar.jpg";

            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'is_verified' => $user['is_verified'],
                'is_admin' => $user['is_admin'],
                'pfp_path' => "/$pfpPath"
            ];
            $_SESSION['success'] = "Account created.";
            header('Location: /account');
            exit;
        } else {
            $_SESSION['error'] = "Une erreur est survenue.";
            header('Location: /register');
            exit;
        }
    }

    // Logout the user
    public function logout()
    {
        $currentUser = $_SESSION['user']['email'] ?? null;
        session_destroy();
        Logger::log('logout', $currentUser, 'success');
        header('Location: /');
        exit;
    }

    // Delete user account
    public function deleteAccount()
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Vous devez être connecté pour supprimer votre compte.";
            header('Location: /login');
            exit;
        }

        $currentUser = $_SESSION['user']['email'] ?? null;
        $userId = $_SESSION['user']['id'];
        $this->userModel->deleteUser($userId);

        Logger::log('delete_account', $currentUser, 'success', ['user_id' => $userId]);
        session_destroy();
        header('Location: /');
        exit;
    }

    // Send verification email
    public function sendVerificationEmail()
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Vous devez être connecté.";
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $user = $this->userModel->getUserById($userId);

        if ($user['is_verified']) {
            $_SESSION['error'] = "Votre compte est déjà vérifié.";
            header('Location: /account');
            exit;
        }

        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->userModel->storeVerificationCode($userId, $code);

        if (EmailUtil::sendVerificationEmail($user['email'], $user['name'], $code)) {
            $_SESSION['success'] = "Un code de vérification a été envoyé à votre email.";
        } else {
            $_SESSION['error'] = "Échec de l'envoi de l'email de vérification.";
        }

        header('Location: /account');
        exit;
    }

    // Verify email code
    public function verifyEmailCode()
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Vous devez être connecté.";
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        try {
            $code = Validator::numericCode($_POST['code'] ?? '', 'Code de vérification', 6);
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /account');
            exit;
        }

        if ($this->userModel->verifyCode($userId, $code)) {
            $this->userModel->verifyUser($userId);
            $_SESSION['user']['is_verified'] = 1;
            $_SESSION['success'] = "Votre email a été vérifié avec succès.";
        } else {
            $_SESSION['error'] = "Code de vérification invalide.";
        }

        header('Location: /account');
        exit;
    }

    // Send password reset email
    public function sendPasswordResetEmail()
    {
        try {
            $email = Validator::email($_POST['email'] ?? '');
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /login');
            exit;
        }

        $user = $this->userModel->getUserByEmail($email);

        if (!$user) {
            $_SESSION['error'] = "Aucun utilisateur trouvé avec cet email.";
            header('Location: /login');
            exit;
        }



        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->userModel->storeResetCode($user['id'], $code);

        if (EmailUtil::sendPasswordResetEmail($user['email'], $user['name'], $code)) {
            $_SESSION['success'] = "Un code de réinitialisation a été envoyé à votre email, veuillez vérifier vos spams.";
        } else {
            $_SESSION['error'] = "Échec de l'envoi de l'email de réinitialisation.";
        }

        header('Location: /login');
        exit;
    }

    // Reset password
    public function resetPassword()
    {
        try {
            $email = Validator::email($_POST['email'] ?? '');
            $code = Validator::numericCode($_POST['code'] ?? '', 'Code de réinitialisation', 6);
            $password = Validator::password($_POST['password'] ?? '', 'Mot de passe', 8, 128, true);
            $passwordConfirm = Validator::password($_POST['password_confirm'] ?? '', 'Confirmation du mot de passe', 8, 128);
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /login');
            exit;
        }

        if ($password !== $passwordConfirm) {
            $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
            header('Location: /login');
            exit;
        }

        $user = $this->userModel->getUserByEmail($email);

        if (!$user) {
            $_SESSION['error'] = "Aucun utilisateur trouvé avec cet email.";
            header('Location: /login');
            exit;
        }

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "Erreur de sécurité (CSRF). Veuillez réessayer.";
            header('Location: /login');
            exit;
        }

        if ($this->userModel->verifyResetCode($user['id'], $code)) {
            $this->userModel->changeUserPassword($user['id'], $password);
            $this->userModel->clearResetCode($user['id']);
            $_SESSION['success'] = "Votre mot de passe a été réinitialisé avec succès.";
        } else {
            $_SESSION['error'] = "Code de réinitialisation invalide.";
        }

        header('Location: /login');
        exit;
    }
}
?>

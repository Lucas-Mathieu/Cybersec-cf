<?php

require_once __DIR__ . '/../../core/Logger.php';
require_once __DIR__ . '/../../core/Validator.php';

class UserController
{
    private $userModel;

    public function __construct($userModel)
    {
        $this->userModel = $userModel;
    }

    // Show admin users page
    public function showAdminUsersPage()
    {
        // Check if the user is logged in and is an admin
        if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
            Logger::log('view_admin_users', $_SESSION['user']['email'] ?? null, 'failure', ['reason' => 'not_admin']);
            $_SESSION['error'] = "ERREURE 403 : Vous devez être administrateur pour accéder à cette page.";
            header('Location: /login');
            exit();
        }

        // Get all users
        $users = $this->userModel->getAllUsers();

        // Show the admin users page
        require_once '../app/views/admin/users.php';
    }

    // Handle verify/unverify user
    public function toggleVerify($userId)
    {
        // Check if the user is logged in and is an admin
        if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
            Logger::log('toggle_verify', $_SESSION['user']['email'] ?? null, 'failure', ['reason' => 'not_admin', 'target_user' => $userId]);
            $_SESSION['error'] = "ERREURE 403 : Vous devez être administrateur pour effectuer cette action.";
            header('Location: /login');
            exit();
        }

        // Get the user to check their current verification status
        $user = $this->userModel->getUserById($userId);
        if (!$user) {
            Logger::log('toggle_verify', $_SESSION['user']['email'] ?? null, 'failure', ['reason' => 'user_not_found', 'target_user' => $userId]);
            $_SESSION['error'] = "Utilisateur non trouvé.";
            header('Location: /admin/users');
            exit();
        }

        // Toggle verification status
        if ($user['is_verified']) {
            $this->userModel->unverifyUser($userId);
            $_SESSION['success'] = "Utilisateur dé-vérifié avec succès.";
            Logger::log('toggle_verify', $_SESSION['user']['email'], 'success', ['target_user' => $userId, 'new_status' => 'unverified']);
        } else {
            $this->userModel->verifyUser($userId);
            $_SESSION['success'] = "Utilisateur vérifié avec succès.";
            Logger::log('toggle_verify', $_SESSION['user']['email'], 'success', ['target_user' => $userId, 'new_status' => 'verified']);
        }

        header('Location: /admin/users');
        exit();
    }

    // Handle make/remove admin
    public function toggleAdmin($userId)
    {
        // Check if the user is logged in and is an admin
        if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
            Logger::log('toggle_admin', $_SESSION['user']['email'] ?? null, 'failure', ['reason' => 'not_admin', 'target_user' => $userId]);
            $_SESSION['error'] = "Vous devez être administrateur pour effectuer cette action.";
            header('Location: /login');
            exit();
        }

        // Prevent admin from removing their own admin rights
        if ($userId == $_SESSION['user']['id']) {
            Logger::log('toggle_admin', $_SESSION['user']['email'], 'failure', ['reason' => 'self_edit']);
            $_SESSION['error'] = "Vous ne pouvez pas modifier vos propres droits d'administrateur.";
            header('Location: /admin/users');
            exit();
        }

        // Get the user to check their current admin status
        $user = $this->userModel->getUserById($userId);
        if (!$user) {
            Logger::log('toggle_admin', $_SESSION['user']['email'], 'failure', ['reason' => 'user_not_found', 'target_user' => $userId]);
            $_SESSION['error'] = "Utilisateur non trouvé.";
            header('Location: /admin/users');
            exit();
        }

        // Toggle admin status
        if ($user['is_admin']) {
            $this->userModel->removeAdmin($userId);
            $_SESSION['success'] = "Droits d'administrateur retirés avec succès.";
            Logger::log('toggle_admin', $_SESSION['user']['email'], 'success', ['target_user' => $userId, 'new_status' => 'user']);
        } else {
            $this->userModel->makeAdmin($userId);
            $_SESSION['success'] = "Utilisateur promu administrateur avec succès.";
            Logger::log('toggle_admin', $_SESSION['user']['email'], 'success', ['target_user' => $userId, 'new_status' => 'admin']);
        }

        header('Location: /admin/users');
        exit();
    }

    // Handle delete user
    public function deleteUser($userId)
    {
        // Check if the user is logged in and is an admin
        if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
            Logger::log('delete_user', $_SESSION['user']['email'] ?? null, 'failure', ['reason' => 'not_admin', 'target_user' => $userId]);
            $_SESSION['error'] = "Vous devez être administrateur pour effectuer cette action.";
            header('Location: /login');
            exit();
        }

        // Prevent admin from deleting their own account
        if ($userId == $_SESSION['user']['id']) {
            Logger::log('delete_user', $_SESSION['user']['email'], 'failure', ['reason' => 'self_delete']);
            $_SESSION['error'] = "Vous ne pouvez pas supprimer votre propre compte.";
            header('Location: /admin/users');
            exit();
        }

        // Check if user exists
        $user = $this->userModel->getUserById($userId);
        if (!$user) {
            Logger::log('delete_user', $_SESSION['user']['email'], 'failure', ['reason' => 'user_not_found', 'target_user' => $userId]);
            $_SESSION['error'] = "Utilisateur non trouvé.";
            header('Location: /admin/users');
            exit();
        }

        // Delete the user
        $this->userModel->deleteUser($userId);

        // Optionally, delete associated profile picture
        $pfpDir = __DIR__ . "/../../www/uploads/pfps/{$userId}";
        if (is_dir($pfpDir)) {
            array_map('unlink', glob("$pfpDir/*"));
            rmdir($pfpDir);
        }

        $_SESSION['success'] = "Utilisateur supprimé avec succès.";
        Logger::log('delete_user', $_SESSION['user']['email'], 'success', ['target_user' => $userId]);
        header('Location: /admin/users');
        exit();
    }

    // Existing methods...

    // Show account page with user details
    public function showAccountPage()
    {
        // Check if the user is logged in
        if (!isset($_SESSION['user'])) {
            Logger::log('view_account', null, 'failure', ['reason' => 'not_authenticated']);
            $_SESSION['error'] = "Vous devez être connecté pour voir cette page.";
            header('Location: /login');
            exit();
        }

        // Get the user ID from the session
        $userId = $_SESSION['user']['id'];
        
        // Get the user details from the database
        $user = $this->userModel->getUserById($userId);

        // Get the user profile picture path
        $pfpPath = "uploads/pfps/{$userId}/avatar.jpg";
        if (!file_exists($pfpPath)) {
            $pfpPath = "Uploads/pfps/0/avatar.jpg"; // Default avatar path
        }

        // Show the account page with user details
        require_once '../app/views/auth/account.php';
    }

    public function uploadProfilePicture()
    {
        if (!isset($_SESSION['user'])) {
            Logger::log('upload_pfp', null, 'failure', ['reason' => 'not_authenticated']);
            $_SESSION['error'] = "Vous devez être connecté.";
            header('Location: /login');
            exit();
        }

        $userId = $_SESSION['user']['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
            $uploadDir = __DIR__ . "/../../www/uploads/pfps/{$userId}";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $targetPath = $uploadDir . "/avatar.jpg";

            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($_FILES['avatar']['type'], $allowedTypes)) {
                move_uploaded_file($_FILES['avatar']['tmp_name'], $targetPath);
                $_SESSION['user']['pfp_path'] = "/uploads/pfps/{$userId}/avatar.jpg";
                $_SESSION['success'] = "Photo de profil mise à jour.";
                Logger::log('upload_pfp', $_SESSION['user']['email'], 'success');
            } else {
                Logger::log('upload_pfp', $_SESSION['user']['email'], 'failure', ['reason' => 'invalid_type']);
                $_SESSION['error'] = "Format de fichier invalide.";
            }
        } else {
            Logger::log('upload_pfp', $_SESSION['user']['email'], 'failure', ['reason' => 'no_file']);
            $_SESSION['error'] = "Aucun fichier téléchargé.";
        }

        header('Location: /account');
        exit();
    }

    // Handle the update of the user profile
    public function updateProfile()
    {
        // Check if the user is logged in
        if (!isset($_SESSION['user'])) {
            Logger::log('update_profile', null, 'failure', ['reason' => 'not_authenticated']);
            $_SESSION['error'] = "Vous devez être connecté pour mettre à jour votre profil.";
            header('Location: /login');
            exit();
        }

        // Check if the request method is POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            } catch (InvalidArgumentException $e) {
                Logger::log('update_profile', $_SESSION['user']['email'], 'failure', ['reason' => 'validation']);
                $_SESSION['error'] = $e->getMessage();
                header('Location: /account');
                exit();
            }

            // Get the user ID from the session
            $userId = $_SESSION['user']['id'];

            // Update the user profile in the database
            $this->userModel->updateUserProfile($userId, $name);

            // Update the session with the new user data
            $_SESSION['user']['name'] = $name;

            // Redirect to the account page with a success message
            $_SESSION['success'] = "Profil mis à jour avec succès.";
            Logger::log('update_profile', $_SESSION['user']['email'], 'success');
            header('Location: /account');
            exit();
        }
    }
}

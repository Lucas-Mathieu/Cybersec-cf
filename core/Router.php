<?php
// Bootstrap session with hardened cookie flags and inactivity enforcement
function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? null) == 443;
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

startSecureSession();

$cspPolicy = "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'self'";
if (!headers_sent()) {
    header("Content-Security-Policy: {$cspPolicy}");
}
$GLOBALS['CSP_POLICY'] = $cspPolicy;

$sessionTimeout = 15 * 60;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $sessionTimeout) {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();
        session_destroy();
    }
    startSecureSession();
}
$_SESSION['last_activity'] = time();

// Load dependencies
require_once '../core/Logger.php';
require_once '../core/Validator.php';
require_once '../app/controllers/AuthController.php';
require_once '../app/controllers/PostController.php';
require_once '../app/controllers/UserController.php';

require_once '../app/models/PostModel.php';
require_once '../app/models/UserModel.php';
require_once '../app/models/CommentModel.php';
require_once '../app/models/TagModel.php';
require_once '../app/models/TechModel.php';

// Instantiate models and controllers
$postModel = new PostModel();
$userModel = new UserModel();
$commentModel = new CommentModel();
$tagModel = new TagModel();
$techModel = new TechModel();

$authController = new AuthController($userModel);
$postController = new PostController($postModel, $commentModel, $tagModel, $techModel);
$userController = new UserController($userModel);

if (isset($_SESSION['user'])) {
    // Check if the user is verified or admin on every page load
    $user = $userModel->getUserById($_SESSION['user']['id']);
    if ($user) {
        $_SESSION['user']['is_verified'] = $user['is_verified'];
        $_SESSION['user']['is_admin'] = $user['is_admin'];
    } else {
        unset($_SESSION['user']);
    }
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get the URI and HTTP method
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Routing logic
switch (true) {

    // Home page
    case $uri === '/':
        $postController->showPostsList(false);
        break;

    // Deleted posts
    case $uri === '/admin/archive':
        $postController->showPostsList(true);
        break;

    // Login page (GET) and login handling (POST)
    case $uri === '/login':
        $method === 'POST' 
            ? $authController->login() 
            : $authController->showLoginForm();
        break;

    // Register page (GET) and registration handling (POST)
    case $uri === '/register':
        $method === 'POST' 
            ? $authController->register() 
            : $authController->showRegisterForm();
        break;

    // Show account page (GET) and handle profile update (POST)
    case $uri === '/account':
        $method === 'POST' 
            ? $userController->updateProfile()
            : $userController->showAccountPage();
        break;

    // Admin users page
    case $uri === '/admin/users':
        $userController->showAdminUsersPage();
        break;

    // Toggle user verification
    case preg_match('#^/admin/verify/(\d+)$#', $uri, $matches):
        if ($method === 'POST') {
            $userId = $matches[1];
            $userController->toggleVerify($userId);
        }
        break;

    // Toggle user admin status
    case preg_match('#^/admin/toggle-admin/(\d+)$#', $uri, $matches):
        if ($method === 'POST') {
            $userId = $matches[1];
            $userController->toggleAdmin($userId);
        }
        break;

    // Delete user
    case preg_match('#^/admin/delete-user/(\d+)$#', $uri, $matches):
        if ($method === 'POST') {
            $userId = $matches[1];
            $userController->deleteUser($userId);
        }
        break;

    // List all posts
    case $uri === '/posts' && $method === 'GET':
        $postController->showPostsList(false);
        break;

    // Show a specific post and its comments
    case preg_match('#^/post/(\d+)$#', $uri, $matches):
        $postId = $matches[1];
        $postController->showPostDetail($postId);
        break;

    // Create new post
    case $uri === '/create-post':
        $method === 'POST'
            ? $postController->createPost()
            : $postController->showCreatePost();
        break;

    // Upload profile picture
    case $uri === '/upload-pfp' && $method === 'POST':
        $userController->uploadProfilePicture();
        break;

    // Logout
    case $uri === '/logout':
        $authController->logout();
        break;

    // Delete account
    case $uri === '/delete-account':
        $authController->deleteAccount();
        break;

    // Add or remove a like (AJAX)
    case $uri === '/ajax/toggle-like' && $method === 'POST':
        $postController->toggleLike();
        break;

    // Add a comment
    case $uri === '/ajax/add-comment' && $method === 'POST':
        header('Content-Type: application/json');
        if (!isset($_SESSION['user']) || !$_SESSION['user']['is_verified']) {
            Logger::log('add_comment', $_SESSION['user']['email'] ?? null, 'failure', ['reason' => 'not_verified']);
            echo json_encode(['success' => false, 'error' => 'Non autorisé']);
            exit;
        }
        try {
            $postId = Validator::numericId($_POST['post_id'] ?? null, 'Identifiant du post');
            $text = Validator::text($_POST['text'] ?? '', 'Commentaire', 3, 1500);
        } catch (InvalidArgumentException $e) {
            Logger::log('add_comment', $_SESSION['user']['email'] ?? null, 'failure', ['reason' => 'validation']);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
        $commentModel->addComment($_SESSION['user']['id'], $postId, $text);
        Logger::log('add_comment', $_SESSION['user']['email'], 'success', ['post_id' => $postId]);
        // Reloads the last comment
        $comments = $commentModel->getCommentsByPostId($postId);
        $lastComment = end($comments);
        ob_start();
        include '../app/views/partials/comment.php';
        $html = ob_get_clean();
        echo json_encode(['success' => true, 'html' => $html]);
        exit;

    // Add a reply to a comment
    case $uri === '/ajax/add-reply' && $method === 'POST':
        header('Content-Type: application/json');
        if (!isset($_SESSION['user']) || !$_SESSION['user']['is_verified']) {
            Logger::log('add_reply', $_SESSION['user']['email'] ?? null, 'failure', ['reason' => 'not_verified']);
            echo json_encode(['success' => false, 'error' => 'Non autorisé']);
            exit;
        }
        try {
            $postId = Validator::numericId($_POST['post_id'] ?? null, 'Identifiant du post');
            $commentId = Validator::numericId($_POST['comment_id'] ?? null, 'Identifiant du commentaire');
            $text = Validator::text($_POST['text'] ?? '', 'Réponse', 3, 1500);
        } catch (InvalidArgumentException $e) {
            Logger::log('add_reply', $_SESSION['user']['email'] ?? null, 'failure', ['reason' => 'validation']);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
        $commentModel->addReply($_SESSION['user']['id'], $postId, $commentId, $text);
        Logger::log('add_reply', $_SESSION['user']['email'], 'success', ['post_id' => $postId, 'comment_id' => $commentId]);
        // Reloads the last reply
        $comments = $commentModel->getCommentsByPostId($postId);
        $target = null;
        foreach ($comments as $comment) {
            if ($comment['id'] == $commentId) {
                $target = $comment;
                break;
            }
        }
        $lastReply = end($target['replies']);
        ob_start();
        include '../app/views/partials/reply.php';
        $html = ob_get_clean();
        echo json_encode(['success' => true, 'html' => $html]);
        exit;

    // Show edit post page
    case preg_match('#^/edit-post/(\d+)$#', $uri, $matches):
        $postId = $matches[1];
        $postController->showEditPost($postId);
        break;

    // Update a post
    case preg_match('#^/update-post/(\d+)$#', $uri, $matches):
        if ($method === 'POST') {
            $postId = $matches[1];
            $postController->updatePost($postId);
        }
        break;

    // Archive a post
    case preg_match('#^/delete-post/(\d+)$#', $uri, $matches):
        $postId = $matches[1];
        $postController->deletePost($postId);
        break;

    // Delete a post for good
    case preg_match('#^/nuke-post/(\d+)$#', $uri, $matches):
        $postId = $matches[1];
        $postController->nukePost($postId);
        break;

    // Restore a post
    case preg_match('#^/restore-post/(\d+)$#', $uri, $matches):
        $postId = $matches[1];
        $postController->restorePost($postId);
        break;

    // Delete a comment
    case preg_match('#^/delete-comment/(\d+)$#', $uri, $matches):
        $commentId = $matches[1];
        $postController->deleteComment($commentId);
        break;

    // Delete a reply
    case preg_match('#^/delete-reply/(\d+)$#', $uri, $matches):
        $replyId = $matches[1];
        $postController->deleteReply($replyId);
        break;

    // Show post history
    case preg_match('#^/post-history/(\d+)$#', $uri, $matches):
        $postId = $matches[1];
        $postController->showPostHistory($postId);
        break;

    // Send verification email
    case $uri === '/send-verification-email' && $method === 'POST':
        $authController->sendVerificationEmail();
        break;

    // Verify email code
    case $uri === '/verify-email' && $method === 'POST':
        $authController->verifyEmailCode();
        break;

    // Handle password reset
    case $uri === '/reset-password' && $method === 'POST':
        if (isset($_POST['send_code'])) {
            $authController->sendPasswordResetEmail();
        } else {
            $authController->resetPassword();
        }
        break;

    // Fallback 404
    default:
        http_response_code(404);
        echo '404 - Page not found';
        break;
}
?>

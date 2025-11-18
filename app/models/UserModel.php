<?php

require_once __DIR__ . '/../../core/Database.php';

class UserModel
{
    private $db;

    public function __construct()
    {
        $this->db = database::getConnection();
    }

    // Get user by email
    public function getUserByEmail($email)
    {
        $stmt = $this->db->prepare('SELECT * FROM user WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create a new user
    public function createUser($name, $email, $password)
    {
        $stmt = $this->db->prepare('INSERT INTO user (name, email, password) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, $password]);
    }

    // Get user by ID
    public function getUserById($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM user WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update user profile
    public function updateUserProfile($id, $name)
    {
        $stmt = $this->db->prepare('UPDATE user SET name = ? WHERE id = ?');
        $stmt->execute([$name, $id]);
    }

    // Change user password
    public function changeUserPassword($id, $newPassword)
    {
        $stmt = $this->db->prepare('UPDATE user SET password = ? WHERE id = ?');
        $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $id]);
    }

    // Delete user account
    public function deleteUser($id)
    {
        // Delete the user's replies on other users' posts
        $stmt = $this->db->prepare('
            DELETE pr FROM post_replies pr
            INNER JOIN post p ON pr.id_post = p.id
            WHERE pr.id_user = ? AND p.id_user != ?
        ');
        $stmt->execute([$id, $id]);

        // Delete the user's comments on other users' posts
        $stmt = $this->db->prepare('
            DELETE pc FROM post_comment pc
            INNER JOIN post p ON pc.id_post = p.id
            WHERE pc.id_user = ? AND p.id_user != ?
        ');
        $stmt->execute([$id, $id]);

        // Delete the user's likes on other users' posts
        $stmt = $this->db->prepare('
            DELETE pl FROM post_like pl
            INNER JOIN post p ON pl.id_post = p.id
            WHERE pl.id_user = ? AND p.id_user != ?
        ');
        $stmt->execute([$id, $id]);

        // Delete replies to comments on the user's posts
        $stmt = $this->db->prepare('
            DELETE pr FROM post_replies pr
            INNER JOIN post p ON pr.id_post = p.id
            WHERE p.id_user = ?
        ');
        $stmt->execute([$id]);

        // Delete comments on the user's posts
        $stmt = $this->db->prepare('
            DELETE pc FROM post_comment pc
            INNER JOIN post p ON pc.id_post = p.id
            WHERE p.id_user = ?
        ');
        $stmt->execute([$id]);

        // Delete likes on the user's posts
        $stmt = $this->db->prepare('
            DELETE pl FROM post_like pl
            INNER JOIN post p ON pl.id_post = p.id
            WHERE p.id_user = ?
        ');
        $stmt->execute([$id]);

        // Delete tags associated with the user's posts
        $stmt = $this->db->prepare('
            DELETE pt FROM post_tag pt
            INNER JOIN post p ON pt.id_post = p.id
            WHERE p.id_user = ?
        ');
        $stmt->execute([$id]);

        // Delete technologies associated with the user's posts
        $stmt = $this->db->prepare('
            DELETE pt FROM post_tech pt
            INNER JOIN post p ON pt.id_post = p.id
            WHERE p.id_user = ?
        ');
        $stmt->execute([$id]);

        // Delete the user's posts
        $stmt = $this->db->prepare('DELETE FROM post WHERE id_user = ?');
        $stmt->execute([$id]);

        // Delete the user
        $stmt = $this->db->prepare('DELETE FROM user WHERE id = ?');
        $stmt->execute([$id]);
    }
    
    // Get all users
    public function getAllUsers()
    {
        $stmt = $this->db->prepare('SELECT id, name, email, is_verified, is_admin FROM user');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Verify user
    public function verifyUser($id)
    {
        $stmt = $this->db->prepare('UPDATE user SET is_verified = 1, verification_code = NULL WHERE id = ?');
        $stmt->execute([$id]);
    }

    // Unverify user
    public function unverifyUser($id)
    {
        $stmt = $this->db->prepare('UPDATE user SET is_verified = 0 WHERE id = ?');
        $stmt->execute([$id]);
    }

    // Make user admin
    public function makeAdmin($id)
    {
        $stmt = $this->db->prepare('UPDATE user SET is_admin = 1 WHERE id = ?');
        $stmt->execute([$id]);
    }

    // Remove admin rights from user
    public function removeAdmin($id)
    {
        $stmt = $this->db->prepare('UPDATE user SET is_admin = 0 WHERE id = ?');
        $stmt->execute([$id]);
    }

    // Store verification code
    public function storeVerificationCode($id, $code)
    {
        $stmt = $this->db->prepare('UPDATE user SET verification_code = ? WHERE id = ?');
        $stmt->execute([$code, $id]);
    }

    // Verify code
    public function verifyCode($id, $code)
    {
        $stmt = $this->db->prepare('SELECT verification_code FROM user WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user && $user['verification_code'] === $code;
    }

    // Store password reset code
    public function storeResetCode($id, $code)
    {
        $stmt = $this->db->prepare('UPDATE user SET reset_code = ? WHERE id = ?');
        $stmt->execute([$code, $id]);
    }

    // Verify reset code
    public function verifyResetCode($id, $code)
    {
        $stmt = $this->db->prepare('SELECT reset_code FROM user WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user && $user['reset_code'] === $code;
    }

    // Clear reset code
    public function clearResetCode($id)
    {
        $stmt = $this->db->prepare('UPDATE user SET reset_code = NULL WHERE id = ?');
        $stmt->execute([$id]);
    }
}
?>
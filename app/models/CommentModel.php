<?php

require_once __DIR__ . '/../../core/Database.php';

class CommentModel
{
    private $db;

    public function __construct()
    {
        $this->db = database::getConnection();
    }

    public function getCommentById($commentId)
    {
        // Get the comment by ID
        $stmt = $this->db->prepare("
            SELECT post_comment.*, user.name AS commenter_name, user.id AS commenter_id
            FROM post_comment
            JOIN user ON post_comment.id_user = user.id
            WHERE post_comment.id = :commentId
        ");
        $stmt->execute(['commentId' => $commentId]);
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);

        return $comment;
    }

    public function getReplyById($replyId)
    {
        // Get the reply by ID
        $stmt = $this->db->prepare("
            SELECT post_replies.*, user.name AS replier_name, user.id AS replier_id
            FROM post_replies
            JOIN user ON post_replies.id_user = user.id
            WHERE post_replies.id = :replyId
        ");
        $stmt->execute(['replyId' => $replyId]);
        $reply = $stmt->fetch(PDO::FETCH_ASSOC);

        return $reply;
    }

    public function getCommentsByPostId($postId)
    {
        // Get all comments for the post
        $stmt = $this->db->prepare("
            SELECT post_comment.*, user.name AS commenter_name, user.id AS commenter_id
            FROM post_comment
            JOIN user ON post_comment.id_user = user.id
            WHERE post_comment.id_post = :postId
            ORDER BY post_comment.date ASC
        ");
        $stmt->execute(['postId' => $postId]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($comments as &$comment) {
            // Add pfp for each comment
            $pfpPath = "uploads/pfps/{$comment['commenter_id']}/avatar.jpg";
            if (!file_exists($pfpPath)) {
                $pfpPath = "uploads/pfps/0/avatar.jpg"; // Avatar par défaut
            }
            $comment['commenter_pfp'] = "/{$pfpPath}"; // Chemin complet de l'avatar

            // Get replies for this comment
            $stmtReplies = $this->db->prepare("
                SELECT post_replies.*, user.name AS replier_name, user.id AS replier_id
                FROM post_replies
                JOIN user ON post_replies.id_user = user.id
                WHERE post_replies.id_parent = :commentId
                ORDER BY post_replies.date ASC
            ");
            $stmtReplies->execute(['commentId' => $comment['id']]);
            $replies = $stmtReplies->fetchAll(PDO::FETCH_ASSOC);

            // Add pfp for each reply
            foreach ($replies as &$reply) {
                $replyPfpPath = "uploads/pfps/{$reply['replier_id']}/avatar.jpg";
                if (!file_exists($replyPfpPath)) {
                    $replyPfpPath = "uploads/pfps/0/avatar.jpg";
                }
                $reply['commenter_pfp'] = "/{$replyPfpPath}";
            }

            $comment['replies'] = $replies;
        }

        return $comments;
    }

    public function addComment($userId, $postId, $text)
    {
        $stmt = $this->db->prepare("
            INSERT INTO post_comment (id_user, id_post, text, date)
            VALUES (:userId, :postId, :text, NOW())
        ");
        $stmt->execute([
            'userId' => $userId,
            'postId' => $postId,
            'text' => $text
        ]);
    }

    public function addReply($userId, $postId, $commentId, $text)
    {
        $stmt = $this->db->prepare("
            INSERT INTO post_replies (id_user, id_post, id_parent, text, date)
            VALUES (:userId, :postId, :commentId, :text, NOW())
        ");
        $stmt->execute([
            'userId' => $userId,
            'postId' => $postId,
            'commentId' => $commentId,
            'text' => $text
        ]);
    }

    public function deleteComment($commentId)
    {
        $stmt = $this->db->prepare("DELETE FROM post_replies WHERE id_parent = :commentId");
        $stmt->execute(['commentId' => $commentId]);

        $stmt = $this->db->prepare("DELETE FROM post_comment WHERE id = :commentId");
        $stmt->execute(['commentId' => $commentId]);
    }
    
    public function deleteReply($replyId)
    {
        $stmt = $this->db->prepare("DELETE FROM post_replies WHERE id = :replyId");
        $stmt->execute(['replyId' => $replyId]);
    }
}
?>

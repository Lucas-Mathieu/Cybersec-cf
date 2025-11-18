<?php
class TagModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAllTags() {
        $stmt = $this->db->query("SELECT * FROM tag");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTagsByPostId($postId) {
        $stmt = $this->db->prepare("
            SELECT tag.*
            FROM tag
            JOIN post_tag ON post_tag.id_tag = tag.id
            WHERE post_tag.id_post = :post_id
        ");
        $stmt->execute(['post_id' => $postId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    
}
?>
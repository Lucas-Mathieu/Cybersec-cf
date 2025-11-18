<?php
class TechModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAllTechs() {
        $stmt = $this->db->query("SELECT * FROM tech");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTechsByPostId($postId) {
        $stmt = $this->db->prepare("
            SELECT tech.* 
            FROM tech
            JOIN post_tech ON post_tech.id_tech = tech.id
            WHERE post_tech.id_post = :postId
        ");
        $stmt->execute(['postId' => $postId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    
}
?>
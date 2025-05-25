<?php

require_once '../database/config.php';

class Filiere {
    private $conn;
    private $table = 'filieres';
    
    public $id;
    public $name;
    public $level;
  
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // CREATE - Ajouter un employé
    public function create() {
        $query = "INSERT INTO " . $this->table . " (name, level) VALUES (:name, :level)";
        
        $stmt = $this->conn->prepare($query);
        
        // Nettoyer les données
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->level = htmlspecialchars(strip_tags($this->level));
        
        
        // Lier les paramètres
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':level', $this->level);
       
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // READ - Lire tous les employés
    public function readAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    // READ - Lire un employé par ID
    public function readOne() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->name = $row['name'];
            $this->level = $row['level'];
            return true;
        }
        return false;
    }
    
    // UPDATE - Mettre à jour un employé
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET name = :name, level = :level 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Nettoyer les données
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->level = htmlspecialchars(strip_tags($this->level));
        
     
        
        // Lier les paramètres
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':level', $this->level);
        $stmt->bindParam(':id', $this->id);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // DELETE - Supprimer un employé
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Recherche
    public function search($keyword) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE name LIKE :keyword OR level LIKE :keyword 
                  ORDER BY name ASC";
        
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(':keyword', $keyword);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Validation de l'level
    public function levelExists($level, $excludeId = null) {
        $query = "SELECT id FROM " . $this->table . " WHERE level = :level";
        if ($excludeId) {
            $query .= " AND id != :excludeId";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':level', $level);
        if ($excludeId) {
            $stmt->bindParam(':excludeId', $excludeId);
        }
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
?>
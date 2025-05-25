<?php

require_once '../database/config.php';

class Professeur {
    private $conn;
    private $table = 'professeurs';
    
    public $id;
    public $username;
    public $email;
    public $speciality;
  
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // CREATE - Ajouter un employé
    public function create() {
        $query = "INSERT INTO " . $this->table . " (username, email, speciality) VALUES (:username, :email, :speciality)";
        
        $stmt = $this->conn->prepare($query);
        
        // Nettoyer les données
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
          $this->speciality = htmlspecialchars(strip_tags($this->speciality));
        
        
        // Lier les paramètres
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':speciality', $this->speciality);
       
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // READ - Lire tous les employés
    public function readAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY username ASC";
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
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->speciality = $row['speciality'];
            return true;
        }
        return false;
    }
    
    // UPDATE - Mettre à jour un employé
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET username = :username, email = :email , speciality = :speciality
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Nettoyer les données
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
         $this->speciality = htmlspecialchars(strip_tags($this->speciality));
        
     
        
        // Lier les paramètres
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':speciality', $this->speciality);
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
                  WHERE username LIKE :keyword OR email LIKE :keyword OR speciality LIKE :keyword 
                  ORDER BY username ASC";
        
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(':keyword', $keyword);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Validation de l'email
    public function emailExists($email, $excludeId = null) {
        $query = "SELECT id FROM " . $this->table . " WHERE email = :email";
        if ($excludeId) {
            $query .= " AND id != :excludeId";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        if ($excludeId) {
            $stmt->bindParam(':excludeId', $excludeId);
        }
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
?>
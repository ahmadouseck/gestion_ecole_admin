<?php
// etudiant.php
require_once '../database/config.php';

class Etudiant {
    private $conn;
    private $table_name = "etudiants";
    
    public $id;
    public $firstname;
    public $lastname;
    public $email;
    public $password;
    public $id_filiere;
    public $created_at;
    public $last_login;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Créer un nouvel étudiant
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET firstname=:firstname, lastname=:lastname, email=:email, 
                      password=:password, id_filiere=:id_filiere";
        
        $stmt = $this->conn->prepare($query);
        
        // Hash du mot de passe
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
        
        // Bind des valeurs
        $stmt->bindParam(":firstname", $this->firstname);
        $stmt->bindParam(":lastname", $this->lastname);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":id_filiere", $this->id_filiere);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Lire tous les étudiants avec leurs filières
    public function readAll() {
        $query = "SELECT e.id, e.firstname, e.lastname, e.email, e.created_at, e.last_login,
                         f.name as filiere_name, f.level as filiere_level
                  FROM " . $this->table_name . " e
                  LEFT JOIN filieres f ON e.id_filiere = f.id
                  ORDER BY e.lastname, e.firstname";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Lire un étudiant spécifique
    public function readOne() {
        $query = "SELECT e.id, e.firstname, e.lastname, e.email, e.id_filiere, 
                         e.created_at, e.last_login,
                         f.name as filiere_name, f.level as filiere_level
                  FROM " . $this->table_name . " e
                  LEFT JOIN filieres f ON e.id_filiere = f.id
                  WHERE e.id = ?
                  LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->firstname = $row['firstname'];
            $this->lastname = $row['lastname'];
            $this->email = $row['email'];
            $this->id_filiere = $row['id_filiere'];
            $this->created_at = $row['created_at'];
            $this->last_login = $row['last_login'];
            return true;
        }
        
        return false;
    }
    
    // Mettre à jour un étudiant
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET firstname=:firstname, lastname=:lastname, email=:email, 
                      id_filiere=:id_filiere";
        
        // Si un nouveau mot de passe est fourni
        if (!empty($this->password)) {
            $query .= ", password=:password";
        }
        
        $query .= " WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind des valeurs
        $stmt->bindParam(":firstname", $this->firstname);
        $stmt->bindParam(":lastname", $this->lastname);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":id_filiere", $this->id_filiere);
        $stmt->bindParam(":id", $this->id);
        
        // Si un nouveau mot de passe est fourni
        if (!empty($this->password)) {
            $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
            $stmt->bindParam(":password", $hashed_password);
        }
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Supprimer un étudiant
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Rechercher des étudiants
    public function search($searchTerm) {
        $query = "SELECT e.id, e.firstname, e.lastname, e.email, e.created_at, e.last_login,
                         f.name as filiere_name, f.level as filiere_level
                  FROM " . $this->table_name . " e
                  LEFT JOIN filieres f ON e.id_filiere = f.id
                  WHERE e.firstname LIKE :search 
                     OR e.lastname LIKE :search 
                     OR e.email LIKE :search
                     OR f.name LIKE :search
                     OR f.level LIKE :search
                  ORDER BY e.lastname, e.firstname";
        
        $stmt = $this->conn->prepare($query);
        $searchTerm = "%{$searchTerm}%";
        $stmt->bindParam(":search", $searchTerm);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Vérifier si l'email existe déjà
    public function emailExists($email, $excludeId = null) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email";
        
        if ($excludeId) {
            $query .= " AND id != :excludeId";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        
        if ($excludeId) {
            $stmt->bindParam(":excludeId", $excludeId);
        }
        
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    // Obtenir les statistiques
    public function getStats() {
        $query = "SELECT 
                    f.name as filiere_name,
                    f.level as filiere_level,
                    COUNT(e.id) as nombre_etudiants,
                    COUNT(CASE WHEN e.last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as actifs_30j
                  FROM filieres f
                  LEFT JOIN " . $this->table_name . " e ON e.id_filiere = f.id
                  GROUP BY f.id, f.name, f.level
                  ORDER BY nombre_etudiants DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Obtenir le total des étudiants
    public function getTotalCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
    
    // Obtenir toutes les filières pour les sélecteurs
    public function getAllFilieres() {
        $query = "SELECT id, name, level FROM filieres ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    // Authentification étudiant
    public function login($email, $password) {
        $query = "SELECT id, firstname, lastname, email, password, id_filiere 
                  FROM " . $this->table_name . " 
                  WHERE email = :email";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(password_verify($password, $row['password'])) {
                $this->id = $row['id'];
                $this->firstname = $row['firstname'];
                $this->lastname = $row['lastname'];
                $this->email = $row['email'];
                $this->id_filiere = $row['id_filiere'];
                
                // Mettre à jour la dernière connexion
                $this->updateLastLogin();
                
                return true;
            }
        }
        
        return false;
    }
    
    // Mettre à jour la dernière connexion
    private function updateLastLogin() {
        $query = "UPDATE " . $this->table_name . " SET last_login = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
    }
}
?>
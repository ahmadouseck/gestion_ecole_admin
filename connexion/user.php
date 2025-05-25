<?php
// User.php
require_once '../database/config.php';

class User {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    // Inscription d'un nouvel utilisateur
    public function register($username, $email, $password) {
        try {
            // Vérifier si l'utilisateur existe déjà
            $stmt = $this->db->prepare("SELECT id FROM admins WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Nom d\'utilisateur ou email déjà utilisé'];
            }
            
            // Hacher le mot de passe
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insérer le nouvel utilisateur
            $stmt = $this->db->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashedPassword]);
            
            return ['success' => true, 'message' => 'Inscription réussie'];
            
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de l\'inscription'];
        }
    }
    
    // Connexion utilisateur
    public function login($username, $password) {
        try {
            $stmt = $this->db->prepare("SELECT id, username, email, password FROM admins WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            
            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($password, $user['password'])) {
                    // Mettre à jour la dernière connexion
                    $updateStmt = $this->db->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
                    $updateStmt->execute([$user['id']]);
                    
                    // Créer la session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    
                    return ['success' => true, 'message' => 'Connexion réussie'];
                }
            }
            
            return ['success' => false, 'message' => 'Nom d\'utilisateur ou mot de passe incorrect'];
            
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de la connexion'];
        }
    }
    
    // Déconnexion
    public function logout() {
        session_destroy();
        return ['success' => true, 'message' => 'Déconnexion réussie'];
    }
    
    // Vérifier si l'utilisateur est connecté
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    // Obtenir les informations de l'utilisateur connecté
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'email' => $_SESSION['email']
            ];
        }
        return null;
    }
}
?>
<?php
require_once './database/config.php';

// Vérification de la connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: ./connexion/login.php');
    exit();
}

// Récupération des informations utilisateur
try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("SELECT username, email, created_at, last_login FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_info = $stmt->fetch();
} catch (PDOException $e) {
    $user_info = null;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord</title>
     <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="header">
        <h1>Tableau de bord</h1>
        <a href="./connexion/logout.php" class="logout-btn">Déconnexion</a>
    </div>
    
    <div class="container">
        <div class="welcome-card">
            <h2>Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            <p>Vous êtes connecté avec succès à votre espace personnel.</p>
            
            <?php if ($user_info): ?>
                <div class="user-info">
                    <div class="info-item">
                        <span class="info-label">Nom d'utilisateur:</span>
                        <span class="info-value"><?php echo htmlspecialchars($user_info['username']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?php echo htmlspecialchars($user_info['email']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Membre depuis:</span>
                        <span class="info-value"><?php echo date('d/m/Y', strtotime($user_info['created_at'])); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Dernière connexion:</span>
                        <span class="info-value">
                            <?php 
                            if ($user_info['last_login']) {
                                echo date('d/m/Y à H:i', strtotime($user_info['last_login']));
                            } else {
                                echo 'Première connexion';
                            }
                            ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="features-grid">


            <a href="./etudiant./index_etudiant.php"style="text-decoration:none">
            <div class="feature-card">
                <div class="feature-icon">👤</div>
                <h3 class="feature-title">Etudiants</h3>
                <p class="feature-description">Consultez la liste des etudiants.</p>
            </div>
            </a>
            <a href="./filiere./index_filiere.php"  style="text-decoration:none">
                <div class="feature-card">
                    <div class="feature-icon">📂</div>
                    <h3 class="feature-title">Filieres</h3>
                    <p class="feature-description">Consultez la liste des filieres.</p>
                </div>
            </a>
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3 class="feature-title">Statistiques</h3>
                <p class="feature-description">Consultez vos statistiques d'utilisation et activités.</p>
            </div>
            <a href="./professeur./index_prof.php" style="text-decoration:none">
                 <div class="feature-card">
                <div class="feature-icon">👤</div>
                <h3 class="feature-title">Professeurs</h3>
                <p class="feature-description">Consultez la liste des professeur.</p>
            </div>
            </a>
            
        </div>
    </div>
</body>
</html>
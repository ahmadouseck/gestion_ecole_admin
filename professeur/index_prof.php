<?php
// index.php
require_once 'professeur.php';

$professeurs = new Professeur();
$message = '';
$messageType = '';

// Traitement des actions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            $professeurs->username= $_POST['username'];
            $professeurs->email = $_POST['email'];
             $professeurs->speciality = $_POST['speciality'];
          
            
            // Validation
            if (empty($professeurs->username) || empty($professeurs->email) ) {
                $message = 'Tous les champs sont obligatoires';
                $messageType = 'error';
            } elseif (!filter_var($professeurs->email, FILTER_VALIDATE_EMAIL)) {
                $message = 'Email invalide';
                $messageType = 'error';
            } elseif ($professeurs->emailExists($professeurs->email)) {
                $message = 'Cet email existe déjà';
                $messageType = 'error';
            }
             else {
                if ($professeurs->create()) {
                    $message = 'Professeur ajouté avec succès';
                    $messageType = 'success';
                } else {
                    $message = 'Erreur lors de l\'ajout';
                    $messageType = 'error';
                }
            }
            break;
            
        case 'update':
            $professeurs->id = $_POST['id'];
            $professeurs->username= $_POST['username'];
            $professeurs->email = $_POST['email'];
            $professeurs->speciality = $_POST['speciality'];
         
            
            // Validation
            if (empty($professeurs->username) || empty($professeurs->email) ) {
                $message = 'Tous les champs sont obligatoires';
                $messageType = 'error';
            } elseif (!filter_var($professeurs->email, FILTER_VALIDATE_EMAIL)) {
                $message = 'Email invalide';
                $messageType = 'error';
            } elseif ($professeurs->emailExists($professeurs->email, $professeurs->id)) {
                $message = 'Cet email existe déjà';
                $messageType = 'error';
            }  else {
                if ($professeurs->update()) {
                    $message = 'Professeur mis à jour avec succès';
                    $messageType = 'success';
                } else {
                    $message = 'Erreur lors de la mise à jour';
                    $messageType = 'error';
                }
            }
            break;
    }
}

// Traitement de la suppression
if (isset($_GET['delete'])) {
    $professeurs->id = $_GET['delete'];
    if ($professeurs->delete()) {
        $message = 'Professeur supprimé avec succès';
        $messageType = 'success';
    } else {
        $message = 'Erreur lors de la suppression';
        $messageType = 'error';
    }
}

// Recherche
$searchTerm = $_GET['search'] ?? '';
if ($searchTerm) {
    $stmt = $professeurs->search($searchTerm);
} else {
    $stmt = $professeurs->readAll();
}

// Pour l'édition
$editprofesseurs = null;
if (isset($_GET['edit'])) {
    $editprofesseurs = new Professeur();
    $editprofesseurs->id = $_GET['edit'];
    $editprofesseurs->readOne();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Professeurs</title>
    <link rel="stylesheet" href="styleprof.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏢 Gestion des Professeurs</h1>
            <p>Eduplateform, vos ressources scolaires vous appartiennent</p>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Formulaire d'ajout/modification -->
        <div class="form-section">
            <h2><?php echo $editprofesseurs ? '✏️ Modifier le professeur' : '➕ Ajouter un Professeur'; ?></h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="<?php echo $editprofesseurs ? 'update' : 'create'; ?>">
                <?php if ($editprofesseurs): ?>
                    <input type="hidden" name="id" value="<?php echo $editprofesseurs->id; ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="username">Nom complet :</label>
                        <input type="text" id="username" name="username" required 
                               value="<?php echo $editprofesseurs ? htmlspecialchars($editprofesseurs->username) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email :</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo $editprofesseurs ? htmlspecialchars($editprofesseurs->email) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="speciality">Specialité :</label>
                        <input type="text" id="speciality" name="speciality" required 
                               value="<?php echo $editprofesseurs ? htmlspecialchars($editprofesseurs->speciality) : ''; ?>">
                    </div>
                   
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $editprofesseurs ? '💾 Mettre à jour' : '➕ Ajouter'; ?>
                    </button>
                    
                    <?php if ($editprofesseurs): ?>
                        <a href="index.php" class="btn btn-warning">❌ Annuler</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <!-- Recherche -->
        <div class="search-section">
            <form method="GET" action="" class="search-form">
                <div class="form-group" style="margin: 0;">
                    <label for="search">🔍 Rechercher :</label>
                    <input type="text" id="search" name="search" placeholder="username, email, speciality ..." 
                           value="<?php echo htmlspecialchars($searchTerm); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Rechercher</button>
                <?php if ($searchTerm): ?>
                    <a href="index_prof.php" class="btn btn-warning">Effacer</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Tableau des Professeurs -->
        <div class="table-section">
            <div class="table-header">
                <h2>📋 Liste des Professeurs <?php echo $searchTerm ? "(Résultats pour: '$searchTerm')" : ''; ?></h2>
            </div>
            
            <div class="table-responsive">
                <?php if ($stmt->rowCount() > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Specialité</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['speciality']); ?></td>
                                    <td>
                                        <div class="actions">
                                            <a href="?edit=<?php echo $row['id']; ?>" class="btn btn-warning btn-small">
                                                ✏️ Modifier
                                            </a>
                                            <a href="?delete=<?php echo $row['id']; ?>" 
                                               class="btn btn-danger btn-small"
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet Professeur ?')">
                                                🗑️ Supprimer
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-data">
                        <h3>Aucun professeur trouvé</h3>
                        <p><?php echo $searchTerm ? "Aucun résultat pour votre recherche." : "Commencez par ajouter des Professeurs."; ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-hide messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const messages = document.querySelectorAll('.message');
            messages.forEach(function(message) {
                setTimeout(function() {
                    message.style.opacity = '0';
                    setTimeout(function() {
                        message.remove();
                    }, 300);
                }, 5000);
            });
        });
    </script>
</body>
</html>
<?php
// index.php
require_once 'filiere.php';

$filieres = new Filiere();
$message = '';
$messageType = '';

// Traitement des actions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            $filieres->name= $_POST['name'];
            $filieres->level = $_POST['level'];
          
            
            // Validation
            if (empty($filieres->name) || empty($filieres->level) ) {
                $message = 'Tous les champs sont obligatoires';
                $messageType = 'error';
            }
            else {
                if ($filieres->create()) {
                    $message = 'Filiere ajouté avec succès';
                    $messageType = 'success';
                } else {
                    $message = 'Erreur lors de l\'ajout';
                    $messageType = 'error';
                }
            }
            break;
            
        case 'update':
            $filieres->id = $_POST['id'];
            $filieres->name= $_POST['name'];
            $filieres->level = $_POST['level'];
         
            
            // Validation
            if (empty($filieres->name) || empty($filieres->level) ) {
                $message = 'Tous les champs sont obligatoires';
                $messageType = 'error';
            }   else {
                if ($filieres->update()) {
                    $message = 'Filiere mis à jour avec succès';
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
    $filieres->id = $_GET['delete'];
    if ($filieres->delete()) {
        $message = 'Filiere supprimé avec succès';
        $messageType = 'success';
    } else {
        $message = 'Erreur lors de la suppression';
        $messageType = 'error';
    }
}

// Recherche
$searchTerm = $_GET['search'] ?? '';
if ($searchTerm) {
    $stmt = $filieres->search($searchTerm);
} else {
    $stmt = $filieres->readAll();
}

// Pour l'édition
$editfilieres = null;
if (isset($_GET['edit'])) {
    $editfilieres = new Filiere();
    $editfilieres->id = $_GET['edit'];
    $editfilieres->readOne();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des filieres</title>
    <link rel="stylesheet" href="stylefiliere.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏢 Gestion des filieres</h1>
            <p>Eduplateform, vos ressources scolaires vous appartiennent</p>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Formulaire d'ajout/modification -->
        <div class="form-section">
            <h2><?php echo $editfilieres ? '✏️ Modifier la Filiere' : '➕ Ajouter une Filiere'; ?></h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="<?php echo $editfilieres ? 'update' : 'create'; ?>">
                <?php if ($editfilieres): ?>
                    <input type="hidden" name="id" value="<?php echo $editfilieres->id; ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Nom complet :</label>
                        <input type="text" id="name" name="name" required 
                               value="<?php echo $editfilieres ? htmlspecialchars($editfilieres->name) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="level">Niveau :</label>
                        <input type="text" id="level" name="level" required 
                               value="<?php echo $editfilieres ? htmlspecialchars($editfilieres->level) : ''; ?>">
                    </div>
                    
                   
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $editfilieres ? '💾 Mettre à jour' : '➕ Ajouter'; ?>
                    </button>
                    
                    <?php if ($editfilieres): ?>
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
                    <input type="text" id="search" name="search" placeholder="nom, niveau ..." 
                           value="<?php echo htmlspecialchars($searchTerm); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Rechercher</button>
                <?php if ($searchTerm): ?>
                    <a href="index_filiere.php" class="btn btn-warning">Effacer</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Tableau des filieres -->
        <div class="table-section">
            <div class="table-header">
                <h2>📋 Liste des filieres <?php echo $searchTerm ? "(Résultats pour: '$searchTerm')" : ''; ?></h2>
            </div>
            
            <div class="table-responsive">
                <?php if ($stmt->rowCount() > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Niveau</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['level']); ?></td>
                                    <td>
                                        <div class="actions">
                                            <a href="?edit=<?php echo $row['id']; ?>" class="btn btn-warning btn-small">
                                                ✏️ Modifier
                                            </a>
                                            <a href="?delete=<?php echo $row['id']; ?>" 
                                               class="btn btn-danger btn-small"
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet Filiere ?')">
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
                        <h3>Aucune Filiere trouvé</h3>
                        <p><?php echo $searchTerm ? "Aucun résultat pour votre recherche." : "Commencez par ajouter des filieres."; ?></p>
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
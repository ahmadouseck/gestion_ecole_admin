<?php
// index_etudiant.php
require_once 'etudiant.php';

$etudiant = new Etudiant();
$message = '';
$messageType = '';

// Traitement des actions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                if (!$etudiant->emailExists($_POST['email'])) {
                    $etudiant->firstname = $_POST['firstname'];
                    $etudiant->lastname = $_POST['lastname'];
                    $etudiant->email = $_POST['email'];
                    $etudiant->password = $_POST['password'];
                    $etudiant->id_filiere = $_POST['id_filiere'];
                    
                    if ($etudiant->create()) {
                        $message = "Étudiant créé avec succès!";
                        $messageType = "success";
                    } else {
                        $message = "Erreur lors de la création de l'étudiant.";
                        $messageType = "error";
                    }
                } else {
                    $message = "Cet email existe déjà!";
                    $messageType = "error";
                }
                break;
                
            case 'update':
                if (!$etudiant->emailExists($_POST['email'], $_POST['id'])) {
                    $etudiant->id = $_POST['id'];
                    $etudiant->firstname = $_POST['firstname'];
                    $etudiant->lastname = $_POST['lastname'];
                    $etudiant->email = $_POST['email'];
                    $etudiant->password = $_POST['password']; // Peut être vide
                    $etudiant->id_filiere = $_POST['id_filiere'];
                    
                    if ($etudiant->update()) {
                        $message = "Étudiant mis à jour avec succès!";
                        $messageType = "success";
                    } else {
                        $message = "Erreur lors de la mise à jour.";
                        $messageType = "error";
                    }
                } else {
                    $message = "Cet email existe déjà!";
                    $messageType = "error";
                }
                break;
                
            case 'delete':
                $etudiant->id = $_POST['id'];
                if ($etudiant->delete()) {
                    $message = "Étudiant supprimé avec succès!";
                    $messageType = "success";
                } else {
                    $message = "Erreur lors de la suppression.";
                    $messageType = "error";
                }
                break;
        }
    }
}

// Gestion de la recherche
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
if ($searchTerm) {
    $result = $etudiant->search($searchTerm);
} else {
    $result = $etudiant->readAll();
}

// Récupération des filières pour le formulaire
$filieres = $etudiant->getAllFilieres();
$stats = $etudiant->getStats();
$totalEtudiants = $etudiant->getTotalCount();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Étudiants</title>
    <link rel="stylesheet" href="styleetudiant.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 Gestion des Étudiants</h1>
            <p>Interface de gestion des étudiants et leurs filières</p>
        </div>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalEtudiants; ?></div>
                <div>Total Étudiants</div>
            </div>
            <?php
            $statsData = $stats->fetchAll(PDO::FETCH_ASSOC);
            $totalFilieres = count($statsData);
            ?>
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalFilieres; ?></div>
                <div>Filières Actives</div>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Barre d'actions -->
        <div class="action-bar">
            <button class="btn btn-success" onclick="openModal('createModal')">
                ➕ Nouvel Étudiant
            </button>
            
            <form class="search-form" method="GET">
                <input type="text" name="search" placeholder="Rechercher un étudiant..." 
                       value="<?php echo htmlspecialchars($searchTerm); ?>">
                <button type="submit" class="btn btn-primary">🔍 Rechercher</button>
                <?php if ($searchTerm): ?>
                    <a href="index_etudiant.php" class="btn btn-warning"  style="text-decoration:none">✖️ Effacer</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Tableau des étudiants -->
        <div class="table-container">
            <?php if ($result->rowCount() > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom Complet</th>
                            <th>Email</th>
                            <th>Filière</th>
                            <th>Niveau</th>
                            <th>Date d'inscription</th>
                            <th>Dernière connexion</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td>
                                    <span class="filiere-badge">
                                        <?php echo htmlspecialchars($row['filiere_name'] ?? 'Non assigné'); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['filiere_level'] ?? '-'); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <?php 
                                    echo $row['last_login'] ? 
                                         date('d/m/Y H:i', strtotime($row['last_login'])) : 
                                         'Jamais connecté'; 
                                    ?>
                                </td>
                                <td>
                                    <button class="btn btn-warning btn-small" 
                                            onclick="editStudent(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                        ✏️ Modifier
                                    </button>
                                    <button class="btn btn-danger btn-small" 
                                            onclick="deleteStudent(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?>')">
                                        🗑️ Supprimer
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <h3>Aucun étudiant trouvé</h3>
                    <p>
                        <?php echo $searchTerm ? 
                            "Aucun résultat pour \"" . htmlspecialchars($searchTerm) . "\"" : 
                            "Commencez par ajouter des étudiants"; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Créer/Modifier Étudiant -->
    <div id="studentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('studentModal')">&times;</span>
            <h2 id="modalTitle">Nouvel Étudiant</h2>
            
            <form method="POST" id="studentForm">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="studentId">
                
                <div class="form-group">
                    <label for="firstname">Prénom *</label>
                    <input type="text" name="firstname" id="firstname" required>
                </div>
                
                <div class="form-group">
                    <label for="lastname">Nom *</label>
                    <input type="text" name="lastname" id="lastname" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" name="email" id="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe <span id="passwordNote">(requis)</span></label>
                    <input type="password" name="password" id="password">
                </div>
                
                <div class="form-group">
                    <label for="id_filiere">Filière *</label>
                    <select name="id_filiere" id="id_filiere" required>
                        <option value="">Sélectionner une filière</option>
                        <?php 
                        $filieres->execute(); // Réexécuter la requête
                        while ($filiere = $filieres->fetch(PDO::FETCH_ASSOC)): 
                        ?>
                            <option value="<?php echo $filiere['id']; ?>">
                                <?php echo htmlspecialchars($filiere['name'] . ' - ' . $filiere['level']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div style="text-align: right; margin-top: 30px;">
                    <button type="button" class="btn btn-warning" onclick="closeModal('studentModal')">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-success" id="submitBtn">
                        Créer l'étudiant
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('deleteModal')">&times;</span>
            <h2>Confirmer la suppression</h2>
            <p>Êtes-vous sûr de vouloir supprimer l'étudiant <strong id="deleteStudentName"></strong> ?</p>
            <p><em>Cette action est irréversible.</em></p>
            
            <form method="POST" id="deleteForm">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteStudentId">
                
                <div style="text-align: right; margin-top: 30px;">
                    <button type="button" class="btn btn-warning" onclick="closeModal('deleteModal')">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-danger">
                        Confirmer la suppression
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Gestion des modals
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            if (modalId === 'studentModal') {
                resetForm();
            }
        }

        // Fermer le modal en cliquant à l'extérieur
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
                if (event.target.id === 'studentModal') {
                    resetForm();
                }
            }
        }

        // Réinitialiser le formulaire
        function resetForm() {
            document.getElementById('studentForm').reset();
            document.getElementById('formAction').value = 'create';
            document.getElementById('studentId').value = '';
            document.getElementById('modalTitle').textContent = 'Nouvel Étudiant';
            document.getElementById('submitBtn').textContent = 'Créer l\'étudiant';
            document.getElementById('passwordNote').textContent = '(requis)';
            document.getElementById('password').required = true;
        }

        // Ouvrir le modal de création
        function openCreateModal() {
            resetForm();
            openModal('studentModal');
        }

        // Modifier un étudiant
        function editStudent(student) {
            document.getElementById('formAction').value = 'update';
            document.getElementById('studentId').value = student.id;
            document.getElementById('firstname').value = student.firstname;
            document.getElementById('lastname').value = student.lastname;
            document.getElementById('email').value = student.email;
            document.getElementById('id_filiere').value = student.id_filiere || '';
            document.getElementById('password').value = '';
            document.getElementById('password').required = false;
            
            document.getElementById('modalTitle').textContent = 'Modifier l\'étudiant';
            document.getElementById('submitBtn').textContent = 'Mettre à jour';
            document.getElementById('passwordNote').textContent = '(laisser vide pour ne pas changer)';
            
            openModal('studentModal');
        }

        // Supprimer un étudiant
        function deleteStudent(id, name) {
            document.getElementById('deleteStudentId').value = id;
            document.getElementById('deleteStudentName').textContent = name;
            openModal('deleteModal');
        }

        // Validation du formulaire
        document.getElementById('studentForm').addEventListener('submit', function(e) {
            const firstname = document.getElementById('firstname').value.trim();
            const lastname = document.getElementById('lastname').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const filiere = document.getElementById('id_filiere').value;
            const action = document.getElementById('formAction').value;

            if (!firstname || !lastname || !email || !filiere) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires.');
                return;
            }

            if (action === 'create' && !password) {
                e.preventDefault();
                alert('Le mot de passe est requis pour créer un nouveau compte.');
                return;
            }

            // Validation de l'email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Veuillez entrer une adresse email valide.');
                return;
            }

            // Validation du mot de passe (si fourni)
            if (password && password.length < 6) {
                e.preventDefault();
                alert('Le mot de passe doit contenir au moins 6 caractères.');
                return;
            }
        });

        // Fonction pour créer un nouvel étudiant (bouton principal)
        document.addEventListener('DOMContentLoaded', function() {
            // Créer un modal spécifique pour la création
            const createModal = document.getElementById('studentModal').cloneNode(true);
            createModal.id = 'createModal';
            document.body.appendChild(createModal);
        });
    </script>
</body>
</html>
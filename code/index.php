<?php
// Démarrage de la session et gestion des erreurs
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inclusion des fichiers nécessaires
require_once 'config.php';

// Variables pour les messages
$message = '';
$messageType = '';

try {
    // Connexion à la base de données
    $database = new Database();
    $db = $database->getConnection();
    $task = new Task($db);
    
    // Traitement des actions POST
    if ($_POST) {
        $result = handlePostActions($task, $_POST);
        $message = $result['message'];
        $messageType = $result['type'];
    }
    
    // Récupération des tâches
    $stmt = $task->read();
    $tasks = $stmt ? $stmt->fetchAll() : [];
    
    // Statistiques
    $stats = $task->countByStatus();
    $statsData = processStats($stats);
    
} catch (Exception $e) {
    $message = "Erreur : " . $e->getMessage();
    $messageType = 'error';
    $tasks = [];
    $statsData = ['en_cours' => 0, 'termine' => 0, 'total' => 0];
}

/**
 * Traite les actions POST
 */
function handlePostActions($task, $postData) {
    if (!isset($postData['action'])) {
        return ['message' => '', 'type' => ''];
    }
    
    switch ($postData['action']) {
        case 'add':
            return handleAddTask($task, $postData);
            
        case 'toggle':
            return handleToggleTask($task, $postData);
            
        case 'delete':
            return handleDeleteTask($task, $postData);
            
        default:
            return ['message' => 'Action non reconnue', 'type' => 'error'];
    }
}

/**
 * Ajoute une nouvelle tâche
 */
function handleAddTask($task, $postData) {
    // Validation
    $errors = Task::validate($postData);
    if (!empty($errors)) {
        return [
            'message' => 'Erreurs de validation : ' . implode(', ', $errors),
            'type' => 'error'
        ];
    }
    
    // Création de la tâche
    $task->title = trim($postData['title']);
    $task->description = trim($postData['description'] ?? '');
    $task->status = 'en_cours';
    
    if ($task->create()) {
        return [
            'message' => '✅ Tâche "' . htmlspecialchars($task->title) . '" ajoutée avec succès !',
            'type' => 'success'
        ];
    } else {
        return [
            'message' => '❌ Erreur lors de l\'ajout de la tâche',
            'type' => 'error'
        ];
    }
}

/**
 * Bascule le statut d'une tâche
 */
function handleToggleTask($task, $postData) {
    if (!isset($postData['id']) || !isset($postData['current_status'])) {
        return ['message' => 'Données manquantes', 'type' => 'error'];
    }
    
    $task->id = (int)$postData['id'];
    $task->status = $postData['current_status'] === 'en_cours' ? 'termine' : 'en_cours';
    
    if ($task->updateStatus()) {
        $statusText = $task->status === 'termine' ? 'terminée' : 'remise en cours';
        return [
            'message' => '✅ Tâche ' . $statusText . ' avec succès !',
            'type' => 'success'
        ];
    } else {
        return [
            'message' => '❌ Erreur lors de la mise à jour du statut',
            'type' => 'error'
        ];
    }
}

/**
 * Supprime une tâche
 */
function handleDeleteTask($task, $postData) {
    if (!isset($postData['id'])) {
        return ['message' => 'ID de tâche manquant', 'type' => 'error'];
    }
    
    $task->id = (int)$postData['id'];
    
    if ($task->delete()) {
        return [
            'message' => '✅ Tâche supprimée avec succès !',
            'type' => 'success'
        ];
    } else {
        return [
            'message' => '❌ Erreur lors de la suppression',
            'type' => 'error'
        ];
    }
}

/**
 * Traite les statistiques
 */
function processStats($stats) {
    $data = ['en_cours' => 0, 'termine' => 0, 'total' => 0];
    
    if ($stats) {
        foreach ($stats as $stat) {
            $data[$stat['status']] = (int)$stat['count'];
            $data['total'] += (int)$stat['count'];
        }
    }
    
    return $data;
}

/**
 * Formate une date
 */
function formatDate($date) {
    return date('d/m/Y à H:i', strtotime($date));
}

/**
 * Échappe et affiche du texte
 */
function e($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <meta name="description" content="Gestionnaire de tâches simple et efficace en PHP et MySQL">
    <meta name="author" content="Votre nom">
    
    <!-- Styles CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📝</text></svg>">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📝 <?php echo APP_NAME; ?></h1>
            <p>Organisez votre quotidien simplement</p>
        </div>

        <!-- Statistiques -->
        <?php if ($statsData['total'] > 0): ?>
        <div class="stats-container">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $statsData['total']; ?></div>
                    <div class="stat-label">Total</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $statsData['en_cours']; ?></div>
                    <div class="stat-label">En cours</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $statsData['termine']; ?></div>
                    <div class="stat-label">Terminées</div>
                </div>
                <?php if ($statsData['total'] > 0): ?>
                <div class="stat-item">
                    <div class="stat-number"><?php echo round(($statsData['termine'] / $statsData['total']) * 100); ?>%</div>
                    <div class="stat-label">Progression</div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Messages -->
        <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <!-- Formulaire d'ajout de tâche -->
        <div class="form-container">
            <form method="POST" action="" id="taskForm">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label for="title">Titre de la tâche <span style="color: #dc3545;">*</span></label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        required 
                        maxlength="255"
                        placeholder="Ex: Faire les courses"
                        value="<?php echo isset($_POST['title']) && $messageType === 'error' ? e($_POST['title']) : ''; ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="description">Description (optionnelle)</label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="3" 
                        maxlength="1000"
                        placeholder="Décrivez votre tâche en détail..."
                    ><?php echo isset($_POST['description']) && $messageType === 'error' ? e($_POST['description']) : ''; ?></textarea>
                </div>
                
                <button type="submit" class="btn" id="submitBtn">
                    ➕ Ajouter la tâche
                </button>
                
                <div style="margin-top: 15px; font-size: 0.9rem; color: #6c757d;">
                    💡 <strong>Astuce :</strong> Utilisez Ctrl+Entrée pour ajouter rapidement, ou Échap pour vider le formulaire
                </div>
            </form>
        </div>

        <!-- Liste des tâches -->
        <div class="tasks-container">
            <?php if (empty($tasks)): ?>
                <div class="empty-state">
                    <div class="icon">📋</div>
                    <h3>Aucune tâche pour le moment</h3>
                    <p>Commencez par ajouter votre première tâche ci-dessus !</p>
                    
                    <div style="margin-top: 30px; font-size: 0.9rem; color: #adb5bd;">
                        <p>💡 <strong>Conseils pour bien commencer :</strong></p>
                        <ul style="text-align: left; display: inline-block; margin-top: 10px;">
                            <li>Utilisez des titres clairs et précis</li>
                            <li>Ajoutez des descriptions pour les tâches complexes</li>
                            <li>Marquez les tâches comme terminées pour suivre vos progrès</li>
                        </ul>
                    </div>
                </div>
            <?php else: ?>
                <div class="tasks-header">
                    <h2 style="margin: 0; color: #2c3e50;">
                        📋 Mes tâches 
                        <span style="font-weight: normal; color: #6c757d; font-size: 1rem;">
                            (<?php echo count($tasks); ?> au total)
                        </span>
                    </h2>
                </div>
                
                <?php foreach ($tasks as $taskItem): ?>
                    <div class="task-item <?php echo $taskItem['status'] === 'termine' ? 'completed' : ''; ?>">
                        <div class="task-title"><?php echo e($taskItem['title']); ?></div>
                        
                        <?php if (!empty($taskItem['description'])): ?>
                            <div class="task-description">
                                <?php echo nl2br(e($taskItem['description'])); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="task-meta">
                            <div class="task-info">
                                <span class="status-badge status-<?php echo $taskItem['status']; ?>">
                                    <?php echo $taskItem['status'] === 'termine' ? '✅ Terminée' : '⏳ En cours'; ?>
                                </span>
                                <div class="task-date">
                                    📅 Créée le <?php echo formatDate($taskItem['created_at']); ?>
                                    <?php if ($taskItem['updated_at'] !== $taskItem['created_at']): ?>
                                        <br>📝 Modifiée le <?php echo formatDate($taskItem['updated_at']); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="task-actions">
                                <!-- Basculer le statut -->
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="id" value="<?php echo $taskItem['id']; ?>">
                                    <input type="hidden" name="current_status" value="<?php echo $taskItem['status']; ?>">
                                    <button type="submit" class="btn-small btn-toggle <?php echo $taskItem['status'] === 'termine' ? 'completed' : ''; ?>">
                                        <?php echo $taskItem['status'] === 'termine' ? '↩️ Reouvrir' : '✅ Terminer'; ?>
                                    </button>
                                </form>
                                
                                <!-- Supprimer -->
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $taskItem['id']; ?>">
                                    <button type="submit" class="btn-small btn-delete">
                                        🗑️ Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Footer -->
        <div style="text-align: center; padding: 20px; color: #6c757d; font-size: 0.9rem; border-top: 1px solid #e9ecef;">
            <p>📝 <?php echo APP_NAME; ?> v<?php echo APP_VERSION; ?> - Créé avec ❤️ en PHP & MySQL</p>
            <p style="margin-top: 5px; font-size: 0.8rem;">
                <?php echo count($tasks); ?> tâche<?php echo count($tasks) > 1 ? 's' : ''; ?> 
                <?php if ($statsData['total'] > 0): ?>
                    • <?php echo $statsData['termine']; ?> terminée<?php echo $statsData['termine'] > 1 ? 's' : ''; ?>
                    • <?php echo round(($statsData['termine'] / $statsData['total']) * 100); ?>% de progression
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Scripts JavaScript -->
    <script src="assets/js/script.js"></script>
    
    <!-- Service Worker pour une future version PWA -->
    <script>
        // Placeholder pour Service Worker
        if ('serviceWorker' in navigator) {
            // navigator.serviceWorker.register('sw.js');
        }
    </script>
</body>
</html>
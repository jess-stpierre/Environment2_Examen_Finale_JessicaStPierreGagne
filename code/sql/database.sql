-- Gestionnaire de Tâches PHP/MySQL

-- Suppression de la base si elle existe (ATTENTION: supprime toutes les données)
-- DROP DATABASE IF EXISTS todo_app;

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS todo_app 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Sélection de la base
USE todo_app;

-- Suppression de la table si elle existe (pour réinitialiser)
DROP TABLE IF EXISTS tasks;

-- Création de la table tasks
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('en_cours', 'termine') DEFAULT 'en_cours',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Index pour améliorer les performances
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_status_created (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données d'exemple pour tester l'application
INSERT INTO tasks (title, description, status) VALUES 
('Faire les courses', 'Acheter des légumes frais, du pain et du lait pour la semaine', 'en_cours'),
('Terminer le projet PHP', 'Finaliser l application de gestion de tâches avec toutes les fonctionnalités', 'termine'),
('Appeler le dentiste', 'Prendre rendez-vous pour un contrôle annuel avant la fin du mois', 'en_cours'),
('Lire un livre', 'Finir de lire "Clean Code" de Robert C. Martin', 'en_cours'),
('Faire du sport', 'Séance de course à pied de 30 minutes dans le parc', 'termine'),
('Organiser les photos', 'Trier et organiser les photos de vacances dans des albums', 'en_cours'),
('Apprendre une nouvelle technologie', 'Explorer Vue.js et créer un petit projet de test', 'en_cours'),
('Nettoyer le bureau', 'Ranger et nettoyer l espace de travail pour une meilleure productivité', 'termine');

-- Création d'une vue pour les statistiques (optionnel)
CREATE VIEW task_stats AS
SELECT 
    status,
    COUNT(*) as count,
    ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM tasks)), 2) as percentage
FROM tasks 
GROUP BY status;

-- Création d'une procédure stockée pour nettoyer les anciennes tâches (optionnel)
DELIMITER //
CREATE PROCEDURE CleanOldCompletedTasks(IN days_old INT)
BEGIN
    DELETE FROM tasks 
    WHERE status = 'termine' 
    AND updated_at < DATE_SUB(NOW(), INTERVAL days_old DAY);
END //
DELIMITER ;

-- Création d'un trigger pour log des modifications (optionnel)
-- CREATE TABLE task_logs (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     task_id INT,
--     action VARCHAR(50),
--     old_status VARCHAR(20),
--     new_status VARCHAR(20),
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- );

-- DELIMITER //
-- CREATE TRIGGER task_status_log 
-- AFTER UPDATE ON tasks
-- FOR EACH ROW
-- BEGIN
--     IF OLD.status != NEW.status THEN
--         INSERT INTO task_logs (task_id, action, old_status, new_status)
--         VALUES (NEW.id, 'status_change', OLD.status, NEW.status);
--     END IF;
-- END //
-- DELIMITER ;

-- Affichage des informations sur la base créée
SELECT 'Base de données créée avec succès!' as message;
SELECT 'Nombre de tâches d exemple insérées:' as info, COUNT(*) as count FROM tasks;
SELECT 'Répartition par statut:' as info, status, COUNT(*) as count FROM tasks GROUP BY status;

-- Création d'un utilisateur dédié (optionnel, pour la production)
-- CREATE USER 'todo_user'@'localhost' IDENTIFIED BY 'mot_de_passe_securise';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON todo_app.* TO 'todo_user'@'localhost';
-- FLUSH PRIVILEGES;

-- Commandes utiles pour la maintenance
-- SHOW TABLE STATUS FROM todo_app;
-- ANALYZE TABLE tasks;
-- OPTIMIZE TABLE tasks;

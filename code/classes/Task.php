<?php
class Task {
    private $conn;
    private $table = "tasks";

    // Propriétés de la tâche
    public $id;
    public $title;
    public $description;
    public $status;
    public $created_at;
    public $updated_at;

    /**
     * Constructeur
     * @param PDO $db Connexion à la base de données
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Créer une nouvelle tâche
     * @return bool
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " (title, description, status) VALUES (:title, :description, :status)";
        $stmt = $this->conn->prepare($query);

        // Nettoyage des données
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Liaison des paramètres
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":status", $this->status);

        try {
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Erreur lors de la création de la tâche: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lire toutes les tâches
     * @return PDOStatement|false
     */
    public function read() {
        $query = "SELECT id, title, description, status, created_at, updated_at 
                  FROM " . $this->table . " 
                  ORDER BY created_at DESC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch(PDOException $e) {
            error_log("Erreur lors de la lecture des tâches: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lire une tâche par son ID
     * @return bool
     */
    public function readOne() {
        $query = "SELECT id, title, description, status, created_at, updated_at 
                  FROM " . $this->table . " 
                  WHERE id = :id 
                  LIMIT 0,1";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $this->id);
            $stmt->execute();

            $row = $stmt->fetch();

            if($row) {
                $this->title = $row['title'];
                $this->description = $row['description'];
                $this->status = $row['status'];
                $this->created_at = $row['created_at'];
                $this->updated_at = $row['updated_at'];
                return true;
            }

            return false;
        } catch(PDOException $e) {
            error_log("Erreur lors de la lecture de la tâche: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mettre à jour le statut d'une tâche
     * @return bool
     */
    public function updateStatus() {
        $query = "UPDATE " . $this->table . " 
                  SET status = :status, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($query);

            // Nettoyage des données
            $this->status = htmlspecialchars(strip_tags($this->status));
            $this->id = htmlspecialchars(strip_tags($this->id));

            // Liaison des paramètres
            $stmt->bindParam(":status", $this->status);
            $stmt->bindParam(":id", $this->id);

            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Erreur lors de la mise à jour du statut: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mettre à jour une tâche complète
     * @return bool
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET title = :title, description = :description, status = :status, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($query);

            // Nettoyage des données
            $this->title = htmlspecialchars(strip_tags($this->title));
            $this->description = htmlspecialchars(strip_tags($this->description));
            $this->status = htmlspecialchars(strip_tags($this->status));
            $this->id = htmlspecialchars(strip_tags($this->id));

            // Liaison des paramètres
            $stmt->bindParam(":title", $this->title);
            $stmt->bindParam(":description", $this->description);
            $stmt->bindParam(":status", $this->status);
            $stmt->bindParam(":id", $this->id);

            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Erreur lors de la mise à jour de la tâche: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprimer une tâche
     * @return bool
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($query);
            $this->id = htmlspecialchars(strip_tags($this->id));
            $stmt->bindParam(":id", $this->id);

            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Erreur lors de la suppression de la tâche: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Compter le nombre de tâches par statut
     * @return array|false
     */
    public function countByStatus() {
        $query = "SELECT status, COUNT(*) as count FROM " . $this->table . " GROUP BY status";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Erreur lors du comptage des tâches: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Valider les données d'une tâche
     * @param array $data
     * @return array Erreurs de validation
     */
    public static function validate($data) {
        $errors = [];

        if (empty($data['title'])) {
            $errors[] = "Le titre est obligatoire";
        } elseif (strlen($data['title']) > 255) {
            $errors[] = "Le titre ne peut pas dépasser 255 caractères";
        }

        if (isset($data['description']) && strlen($data['description']) > 1000) {
            $errors[] = "La description ne peut pas dépasser 1000 caractères";
        }

        if (isset($data['status']) && !in_array($data['status'], ['en_cours', 'termine'])) {
            $errors[] = "Le statut doit être 'en_cours' ou 'termine'";
        }

        return $errors;
    }
}
?>
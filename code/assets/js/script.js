document.addEventListener("DOMContentLoaded", function () {
  // Initialisation de l'application
  initApp();

  // Configuration des événements
  setupEventListeners();

  // Animations d'entrée
  animateTasksEntrance();

  // Auto-resize du textarea
  setupTextareaAutoResize();

  // Messages temporaires
  setupTemporaryMessages();
});

/**
 * Initialise l'application
 */
function initApp() {
  console.log("📝 Gestionnaire de Tâches - Démarrage");

  // Vérification du support des fonctionnalités modernes
  if (!window.fetch) {
    console.warn("⚠️ Fetch API non supporté");
  }

  // Focus automatique sur le premier champ
  const titleInput = document.querySelector("#title");
  if (titleInput) {
    titleInput.focus();
  }
}

/**
 * Configure les événements
 */
function setupEventListeners() {
  // Confirmation de suppression
  const deleteButtons = document.querySelectorAll(
    'button[name="action"][value="delete"]'
  );
  deleteButtons.forEach((button) => {
    button.closest("form").addEventListener("submit", handleDeleteConfirmation);
  });

  // Animation au survol des tâches
  const taskItems = document.querySelectorAll(".task-item");
  taskItems.forEach((item) => {
    item.addEventListener("mouseenter", handleTaskHover);
    item.addEventListener("mouseleave", handleTaskLeave);
  });

  // Raccourcis clavier
  document.addEventListener("keydown", handleKeyboardShortcuts);

  // Validation du formulaire
  const taskForm = document.querySelector('form[action=""]');
  if (taskForm && taskForm.querySelector('input[name="action"][value="add"]')) {
    taskForm.addEventListener("submit", handleFormValidation);
  }

  // Gestion des clics sur les boutons toggle
  const toggleButtons = document.querySelectorAll(".btn-toggle");
  toggleButtons.forEach((button) => {
    button.addEventListener("click", handleToggleClick);
  });
}

/**
 * Animation d'entrée des tâches
 */
function animateTasksEntrance() {
  const taskItems = document.querySelectorAll(".task-item");

  taskItems.forEach((item, index) => {
    // Reset initial state
    item.style.opacity = "0";
    item.style.transform = "translateY(20px)";

    // Animate in
    setTimeout(() => {
      item.style.transition = "opacity 0.5s ease, transform 0.5s ease";
      item.style.opacity = "1";
      item.style.transform = "translateY(0)";
    }, index * 100);
  });

  // Animation pour les éléments vides
  const emptyState = document.querySelector(".empty-state");
  if (emptyState) {
    emptyState.style.opacity = "0";
    emptyState.style.transform = "translateY(30px)";

    setTimeout(() => {
      emptyState.style.transition = "opacity 0.8s ease, transform 0.8s ease";
      emptyState.style.opacity = "1";
      emptyState.style.transform = "translateY(0)";
    }, 300);
  }
}

/**
 * Auto-resize du textarea
 */
function setupTextareaAutoResize() {
  const textarea = document.querySelector("#description");
  if (textarea) {
    // Fonction de redimensionnement
    function autoResize() {
      this.style.height = "auto";
      this.style.height = Math.min(this.scrollHeight, 200) + "px";
    }

    // Événements
    textarea.addEventListener("input", autoResize);
    textarea.addEventListener("focus", autoResize);

    // Redimensionnement initial
    autoResize.call(textarea);
  }
}

/**
 * Gestion des messages temporaires
 */
function setupTemporaryMessages() {
  const messages = document.querySelectorAll(".message");

  messages.forEach((message) => {
    // Auto-hide après 5 secondes
    setTimeout(() => {
      fadeOut(message);
    }, 5000);

    // Ajout d'un bouton de fermeture
    const closeBtn = document.createElement("span");
    closeBtn.innerHTML = "&times;";
    closeBtn.className = "message-close";
    closeBtn.style.cssText = `
            float: right;
            font-size: 1.5rem;
            cursor: pointer;
            margin-left: 10px;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        `;

    closeBtn.addEventListener("click", () => fadeOut(message));
    closeBtn.addEventListener(
      "mouseenter",
      () => (closeBtn.style.opacity = "1")
    );
    closeBtn.addEventListener(
      "mouseleave",
      () => (closeBtn.style.opacity = "0.7")
    );

    message.appendChild(closeBtn);
  });
}

/**
 * Confirmation de suppression
 */
function handleDeleteConfirmation(e) {
  const taskTitle =
    this.closest(".task-item")?.querySelector(".task-title")?.textContent ||
    "cette tâche";

  if (!confirm(`Êtes-vous sûr de vouloir supprimer "${taskTitle}" ?`)) {
    e.preventDefault();
    return false;
  }

  // Animation de suppression
  const taskItem = this.closest(".task-item");
  if (taskItem) {
    taskItem.style.transition = "all 0.3s ease";
    taskItem.style.transform = "translateX(-100%)";
    taskItem.style.opacity = "0";
  }
}

/**
 * Survol des tâches
 */
function handleTaskHover() {
  this.style.transform = "translateY(-3px) scale(1.02)";
}

function handleTaskLeave() {
  this.style.transform = "";
}

/**
 * Raccourcis clavier
 */
function handleKeyboardShortcuts(e) {
  // Ctrl + Entrée : Soumettre le formulaire
  if (e.ctrlKey && e.key === "Enter") {
    const form = document.querySelector('form[action=""]');
    if (form && form.querySelector('input[name="action"][value="add"]')) {
      form.submit();
    }
  }

  // Échap : Vider le formulaire
  if (e.key === "Escape") {
    const titleInput = document.querySelector("#title");
    const descInput = document.querySelector("#description");

    if (titleInput) titleInput.value = "";
    if (descInput) descInput.value = "";

    if (titleInput) titleInput.focus();
  }
}

/**
 * Validation du formulaire
 */
function handleFormValidation(e) {
  const titleInput = this.querySelector("#title");
  const errors = [];

  // Validation du titre
  if (!titleInput.value.trim()) {
    errors.push("Le titre est obligatoire");
  } else if (titleInput.value.trim().length > 255) {
    errors.push("Le titre ne peut pas dépasser 255 caractères");
  }

  // Validation de la description
  const descInput = this.querySelector("#description");
  if (descInput && descInput.value.length > 1000) {
    errors.push("La description ne peut pas dépasser 1000 caractères");
  }

  // Affichage des erreurs
  if (errors.length > 0) {
    e.preventDefault();
    showValidationErrors(errors);
    return false;
  }

  // Animation de soumission
  const submitBtn = this.querySelector(".btn");
  if (submitBtn) {
    submitBtn.style.transform = "scale(0.95)";
    submitBtn.textContent = "⏳ Ajout...";

    setTimeout(() => {
      submitBtn.style.transform = "";
    }, 200);
  }
}

/**
 * Gestion des clics sur les boutons toggle
 */
function handleToggleClick(e) {
  const button = e.target;
  const form = button.closest("form");

  // Animation du bouton
  button.style.transform = "scale(0.9)";

  setTimeout(() => {
    button.style.transform = "";
  }, 150);

  // Animation de la tâche
  const taskItem = form.closest(".task-item");
  if (taskItem) {
    taskItem.style.transition = "all 0.3s ease";
    taskItem.style.opacity = "0.7";

    setTimeout(() => {
      taskItem.style.opacity = "";
    }, 300);
  }
}

/**
 * Affiche les erreurs de validation
 */
function showValidationErrors(errors) {
  // Suppression des anciens messages d'erreur
  const existingErrors = document.querySelectorAll(".validation-error");
  existingErrors.forEach((error) => error.remove());

  // Création du message d'erreur
  const errorDiv = document.createElement("div");
  errorDiv.className = "message error validation-error";
  errorDiv.innerHTML = `
        <strong>Erreur de validation :</strong>
        <ul style="margin-top: 10px; margin-left: 20px;">
            ${errors.map((error) => `<li>${error}</li>`).join("")}
        </ul>
    `;

  // Insertion avant le formulaire
  const formContainer = document.querySelector(".form-container");
  if (formContainer) {
    formContainer.insertBefore(errorDiv, formContainer.firstChild);

    // Scroll vers l'erreur
    errorDiv.scrollIntoView({ behavior: "smooth", block: "center" });

    // Auto-hide après 8 secondes
    setTimeout(() => fadeOut(errorDiv), 8000);
  }
}

/**
 * Effet de fade out
 */
function fadeOut(element) {
  if (!element) return;

  element.style.transition = "opacity 0.5s ease, transform 0.5s ease";
  element.style.opacity = "0";
  element.style.transform = "translateY(-10px)";

  setTimeout(() => {
    if (element.parentNode) {
      element.parentNode.removeChild(element);
    }
  }, 500);
}

/**
 * Compteur de caractères
 */
function setupCharacterCounter() {
  const titleInput = document.querySelector("#title");
  const descInput = document.querySelector("#description");

  if (titleInput) {
    addCharacterCounter(titleInput, 255, "Titre");
  }

  if (descInput) {
    addCharacterCounter(descInput, 1000, "Description");
  }
}

/**
 * Ajoute un compteur de caractères à un champ
 */
function addCharacterCounter(input, maxLength, label) {
  const counter = document.createElement("div");
  counter.className = "character-counter";
  counter.style.cssText = `
        font-size: 0.8rem;
        color: #6c757d;
        text-align: right;
        margin-top: 5px;
        transition: color 0.3s ease;
    `;

  function updateCounter() {
    const length = input.value.length;
    counter.textContent = `${length}/${maxLength} caractères`;

    if (length > maxLength * 0.8) {
      counter.style.color = length > maxLength ? "#dc3545" : "#ffc107";
    } else {
      counter.style.color = "#6c757d";
    }
  }

  input.addEventListener("input", updateCounter);
  input.parentNode.appendChild(counter);

  updateCounter();
}

/**
 * Recherche en temps réel (pour une future version)
 */
function setupLiveSearch() {
  // Placeholder pour une fonctionnalité de recherche
  console.log("🔍 Recherche en temps réel prête à être implémentée");
}

/**
 * Mode sombre (pour une future version)
 */
function setupDarkMode() {
  // Placeholder pour le mode sombre
  console.log("🌙 Mode sombre prêt à être implémenté");
}

// Export des fonctions pour utilisation externe (si nécessaire)
window.TodoApp = {
  fadeOut,
  showValidationErrors,
  setupCharacterCounter,
  animateTasksEntrance,
};

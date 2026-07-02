<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Matières — <?= htmlspecialchars($_ENV['APP_NAME'] ?? 'EICG') ?></title>
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
<div class="layout">
  <?php require __DIR__ . '/partials/sidebar.php'; ?>
  <div class="main">
    <header class="topbar">
      <span class="topbar-title">Matières</span>
      <div class="topbar-actions">
        <button class="btn btn-primary btn-sm" id="btn-add-matiere">+ Nouvelle matière</button>
      </div>
    </header>
    <main class="content">
      <div class="card">
        <div class="card-header">
          <span class="card-title">Liste des matières</span>
          <span class="text-sm text-muted" id="mat-count"></span>
        </div>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr><th>Libellé</th><th>Code</th><th>Statut</th><th>Actions</th></tr>
            </thead>
            <tbody id="tbody-matieres">
              <tr><td colspan="4" style="text-align:center;padding:40px;color:var(--text-muted)">Chargement…</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>

<!-- Modal -->
<div class="modal-backdrop" id="modal-matiere">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title" id="modal-mat-title">Nouvelle matière</span>
      <button class="modal-close" data-close="modal-matiere">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="mat-code">
      <div class="form-group">
        <label class="form-label">Libellé <span class="req">*</span></label>
        <input type="text" id="mat-libelle" class="form-control" placeholder="Ex : Mathématiques, Informatique…">
        <div class="form-error" id="err-mat-libelle"></div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-close="modal-matiere">Annuler</button>
      <button class="btn btn-primary" id="btn-save-mat">
        <span id="save-mat-text">Enregistrer</span>
        <span class="spinner" id="save-mat-spinner" style="display:none"></span>
      </button>
    </div>
  </div>
</div>

<div class="toast-container" id="toast-container"></div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/assets/js/ajax/auth.js"></script>
<script src="/assets/js/ajax/matieres.js"></script>
</body>
</html>

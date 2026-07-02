<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Classes — <?= htmlspecialchars($_ENV['APP_NAME'] ?? 'EICG') ?></title>
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
<div class="layout">
  <?php require __DIR__ . '/partials/sidebar.php'; ?>
  <div class="main">
    <header class="topbar">
      <span class="topbar-title">Classes</span>
      <div class="topbar-actions">
        <button class="btn btn-primary btn-sm" id="btn-add-classe">+ Nouvelle classe</button>
      </div>
    </header>
    <main class="content">

      <!-- Filtres -->
      <div class="card" style="margin-bottom:20px;">
        <div class="card-body" style="padding:16px 24px;">
          <div style="display:flex; gap:16px; align-items:flex-end; flex-wrap:wrap;">
            <div class="form-group" style="margin:0; flex:1; min-width:180px;">
              <label class="form-label">Filtrer par année</label>
              <select id="filter-annee" class="form-control">
                <option value="">Toutes les années</option>
              </select>
            </div>
            <div class="form-group" style="margin:0; flex:1; min-width:180px;">
              <label class="form-label">Filtrer par filière</label>
              <select id="filter-filiere" class="form-control">
                <option value="">Toutes les filières</option>
              </select>
            </div>
            <button class="btn btn-outline btn-sm" id="btn-filter">Filtrer</button>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <span class="card-title">Liste des classes</span>
          <span class="text-sm text-muted" id="classe-count"></span>
        </div>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Classe</th>
                <th>Filière</th>
                <th>Niveau</th>
                <th>Année</th>
                <th>Capacité</th>
                <th>Étudiants</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="tbody-classes">
              <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted)">Chargement…</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>

<!-- Modal Classe -->
<div class="modal-backdrop" id="modal-classe">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title" id="modal-classe-title">Nouvelle classe</span>
      <button class="modal-close" data-close="modal-classe">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="classe-code">
      <div class="form-group">
        <label class="form-label">Libellé <span class="req">*</span></label>
        <input type="text" id="classe-libelle" class="form-control" placeholder="Ex : L1 Info A, BTS1 Compta…">
        <div class="form-error" id="err-classe-libelle"></div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Année scolaire <span class="req">*</span></label>
          <select id="classe-annee" class="form-control">
            <option value="">— Choisir —</option>
          </select>
          <div class="form-error" id="err-classe-annee"></div>
        </div>
        <div class="form-group">
          <label class="form-label">Filière <span class="req">*</span></label>
          <select id="classe-filiere" class="form-control">
            <option value="">— Choisir une filière —</option>
          </select>
          <div class="form-error" id="err-classe-filiere"></div>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Niveau <span class="req">*</span></label>
          <select id="classe-niveau" class="form-control">
            <option value="">— Choisir d'abord une filière —</option>
          </select>
          <div class="form-error" id="err-classe-niveau"></div>
        </div>
        <div class="form-group">
          <label class="form-label">Capacité max</label>
          <input type="number" id="classe-capacite" class="form-control" placeholder="Ex : 40" min="1">
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-close="modal-classe">Annuler</button>
      <button class="btn btn-primary" id="btn-save-classe">
        <span id="save-classe-text">Enregistrer</span>
        <span class="spinner" id="save-classe-spinner" style="display:none"></span>
      </button>
    </div>
  </div>
</div>

<div class="toast-container" id="toast-container"></div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/assets/js/ajax/auth.js"></script>
<script src="/assets/js/ajax/classes.js"></script>
</body>
</html>

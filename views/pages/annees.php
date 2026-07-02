<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Années scolaires — <?= htmlspecialchars($_ENV['APP_NAME'] ?? 'EICG') ?></title>
  <link rel="stylesheet" href="/assets/css/app.css">
  <style>
    .annee-row { cursor: pointer; transition: background .15s; }
    .annee-row:hover td { background: var(--surface) !important; }
    .semestres-panel {
      display: none;
      background: var(--surface);
      border-top: 2px solid var(--accent);
    }
    .semestres-panel.open { display: table-row; }
    .semestres-inner { padding: 20px 24px; }
    .semestres-header {
      display: flex; align-items: center; justify-content: space-between;
      margin-bottom: 14px;
    }
    .semestres-header h4 { font-size: .9rem; color: var(--primary); margin: 0; }
    .sem-table { width: 100%; border-collapse: collapse; font-size: .83rem; }
    .sem-table th {
      text-align: left; padding: 8px 12px;
      color: var(--text-muted); font-size: .72rem;
      font-weight: 600; text-transform: uppercase;
      border-bottom: 1px solid var(--border);
    }
    .sem-table td { padding: 9px 12px; border-bottom: 1px solid var(--border); vertical-align: middle; }
    .sem-table tbody tr:last-child td { border-bottom: none; }
    .expand-icon { transition: transform .2s; display: inline-block; }
    .annee-row.expanded .expand-icon { transform: rotate(90deg); }
  </style>
</head>
<body>

<div class="layout">
  <?php require __DIR__ . '/partials/sidebar.php'; ?>

  <div class="main">

    <header class="topbar">
      <span class="topbar-title">Années scolaires</span>
      <div class="topbar-actions">
        <button class="btn btn-primary btn-sm" id="btn-add-annee">
          <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
          Nouvelle année
        </button>
      </div>
    </header>

    <main class="content">
      <div class="card">
        <div class="card-header">
          <span class="card-title">Années scolaires</span>
          <span class="text-sm text-muted" id="annee-count"></span>
        </div>
        <div class="table-responsive">
          <table class="table" id="table-annees">
            <thead>
              <tr>
                <th style="width:32px;"></th>
                <th>Libellé</th>
                <th>Date début</th>
                <th>Date fin</th>
                <th>Semestres</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="tbody-annees">
              <tr><td colspan="7" style="text-align:center; padding:40px; color:var(--text-muted);">Chargement…</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>

<!-- Modal Année -->
<div class="modal-backdrop" id="modal-annee">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title" id="modal-annee-title">Nouvelle année scolaire</span>
      <button class="modal-close" data-close="modal-annee">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="annee-id">

      <div class="form-group">
        <label class="form-label" for="annee-libelle">Libellé <span class="req">*</span></label>
        <input type="text" id="annee-libelle" class="form-control" placeholder="Ex : 2024-2025">
        <div class="form-error" id="err-annee-libelle"></div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="annee-debut">Date de début <span class="req">*</span></label>
          <input type="date" id="annee-debut" class="form-control">
          <div class="form-error" id="err-annee-debut"></div>
        </div>
        <div class="form-group">
          <label class="form-label" for="annee-fin">Date de fin <span class="req">*</span></label>
          <input type="date" id="annee-fin" class="form-control">
          <div class="form-error" id="err-annee-fin"></div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-close="modal-annee">Annuler</button>
      <button class="btn btn-primary" id="btn-save-annee">
        <span id="save-annee-text">Enregistrer</span>
        <span class="spinner" id="save-annee-spinner" style="display:none;"></span>
      </button>
    </div>
  </div>
</div>

<!-- Modal Semestre -->
<div class="modal-backdrop" id="modal-semestre">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title" id="modal-sem-title">Nouveau semestre</span>
      <button class="modal-close" data-close="modal-semestre">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="sem-code">
      <input type="hidden" id="sem-annee-id">
      <input type="hidden" id="sem-annee-libelle">

      <div class="form-group">
        <label class="form-label">Année scolaire</label>
        <input type="text" class="form-control" id="sem-annee-display" readonly style="background:var(--surface); cursor:default;">
      </div>

      <div class="form-group">
        <label class="form-label" for="sem-libelle">Libellé <span class="req">*</span></label>
        <input type="text" id="sem-libelle" class="form-control" placeholder="Ex : Semestre 1">
        <div class="form-error" id="err-sem-libelle"></div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="sem-debut">Date de début</label>
          <input type="date" id="sem-debut" class="form-control">
        </div>
        <div class="form-group">
          <label class="form-label" for="sem-fin">Date de fin</label>
          <input type="date" id="sem-fin" class="form-control">
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-close="modal-semestre">Annuler</button>
      <button class="btn btn-primary" id="btn-save-sem">
        <span id="save-sem-text">Enregistrer</span>
        <span class="spinner" id="save-sem-spinner" style="display:none;"></span>
      </button>
    </div>
  </div>
</div>

<!-- Toast -->
<div class="toast-container" id="toast-container"></div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/assets/js/ajax/auth.js"></script>
<script src="/assets/js/ajax/annees.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cycles & Filières — <?= htmlspecialchars($_ENV['APP_NAME'] ?? 'EICG') ?></title>
  <link rel="stylesheet" href="/assets/css/app.css">
  <style>
    .tree-panel { background: var(--surface); border-top: 2px solid var(--accent); display: none; }
    .tree-panel.open { display: table-row; }
    .tree-inner { padding: 16px 24px; }
    .sub-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
    .sub-header h4 { font-size:.875rem; color:var(--primary); margin:0; }
    .sub-table { width:100%; border-collapse:collapse; font-size:.83rem; }
    .sub-table th { text-align:left; padding:7px 12px; color:var(--text-muted); font-size:.7rem; font-weight:600; text-transform:uppercase; border-bottom:1px solid var(--border); }
    .sub-table td { padding:9px 12px; border-bottom:1px solid var(--border); vertical-align:middle; }
    .sub-table tr:last-child td { border-bottom:none; }
    .expandable { cursor:pointer; }
    .expand-icon { transition:transform .2s; display:inline-block; color:var(--text-muted); font-size:.7rem; }
    .expandable.expanded .expand-icon { transform:rotate(90deg); }
    /* Niveaux sous filières */
    .niv-panel { background:#fff; border-top:1px solid var(--accent); display:none; }
    .niv-panel.open { display:table-row; }
    .niv-inner { padding:12px 16px; }
    .niv-table { width:100%; border-collapse:collapse; font-size:.8rem; }
    .niv-table th { text-align:left; padding:6px 10px; color:var(--text-muted); font-size:.68rem; font-weight:600; text-transform:uppercase; border-bottom:1px solid var(--border); }
    .niv-table td { padding:8px 10px; border-bottom:1px solid var(--border); vertical-align:middle; }
    .niv-table tr:last-child td { border-bottom:none; }
    .tabs { display:flex; gap:4px; margin-bottom:20px; border-bottom:2px solid var(--border); }
    .tab-btn { padding:10px 20px; font-size:.875rem; font-weight:600; color:var(--text-muted); cursor:pointer; border:none; background:none; border-bottom:2px solid transparent; margin-bottom:-2px; transition:all .2s; }
    .tab-btn.active { color:var(--primary); border-bottom-color:var(--primary); }
    .tab-content { display:none; }
    .tab-content.active { display:block; }
  </style>
</head>
<body>
<div class="layout">
  <?php require __DIR__ . '/partials/sidebar.php'; ?>
  <div class="main">
    <header class="topbar">
      <span class="topbar-title">Structure académique</span>
      <div class="topbar-actions" id="topbar-actions">
        <button class="btn btn-primary btn-sm" id="btn-add-cycle">+ Nouveau cycle</button>
      </div>
    </header>
    <main class="content">

      <!-- Onglets -->
      <div class="tabs">
        <button class="tab-btn active" data-tab="tab-cycles">Cycles & Filières & Niveaux</button>
        <button class="tab-btn" data-tab="tab-salles">Salles</button>
      </div>

      <!-- Tab Cycles -->
      <div class="tab-content active" id="tab-cycles">
        <div class="card">
          <div class="card-header">
            <span class="card-title">Cycles</span>
            <span class="text-sm text-muted" id="cycle-count"></span>
          </div>
          <div class="table-responsive">
            <table class="table">
              <thead><tr><th style="width:28px"></th><th>Cycle</th><th>Filières</th><th>Statut</th><th>Actions</th></tr></thead>
              <tbody id="tbody-cycles">
                <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--text-muted)">Chargement…</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Tab Salles -->
      <div class="tab-content" id="tab-salles">
        <div class="card">
          <div class="card-header">
            <span class="card-title">Salles</span>
            <button class="btn btn-primary btn-sm" id="btn-add-salle">+ Nouvelle salle</button>
          </div>
          <div class="table-responsive">
            <table class="table">
              <thead><tr><th>Libellé</th><th>Code</th><th>Statut</th><th>Actions</th></tr></thead>
              <tbody id="tbody-salles">
                <tr><td colspan="4" style="text-align:center;padding:40px;color:var(--text-muted)">Chargement…</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </main>
  </div>
</div>

<!-- Modal Cycle -->
<div class="modal-backdrop" id="modal-cycle">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title" id="modal-cycle-title">Nouveau cycle</span>
      <button class="modal-close" data-close="modal-cycle">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="cycle-code">
      <div class="form-group">
        <label class="form-label">Libellé <span class="req">*</span></label>
        <input type="text" id="cycle-libelle" class="form-control" placeholder="Ex : Licence, Master, BTS…">
        <div class="form-error" id="err-cycle-libelle"></div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-close="modal-cycle">Annuler</button>
      <button class="btn btn-primary" id="btn-save-cycle">
        <span id="save-cycle-text">Enregistrer</span>
        <span class="spinner" id="save-cycle-spinner" style="display:none"></span>
      </button>
    </div>
  </div>
</div>

<!-- Modal Filière -->
<div class="modal-backdrop" id="modal-filiere">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title" id="modal-fil-title">Nouvelle filière</span>
      <button class="modal-close" data-close="modal-filiere">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="fil-code">
      <input type="hidden" id="fil-cycle-code">
      <div class="form-group">
        <label class="form-label">Cycle</label>
        <input type="text" id="fil-cycle-display" class="form-control" readonly style="background:var(--surface)">
      </div>
      <div class="form-group">
        <label class="form-label">Libellé <span class="req">*</span></label>
        <input type="text" id="fil-libelle" class="form-control" placeholder="Ex : Informatique, Gestion…">
        <div class="form-error" id="err-fil-libelle"></div>
      </div>
      <div class="form-group">
        <label class="form-label">Description</label>
        <input type="text" id="fil-description" class="form-control" placeholder="Description optionnelle">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-close="modal-filiere">Annuler</button>
      <button class="btn btn-primary" id="btn-save-fil">
        <span id="save-fil-text">Enregistrer</span>
        <span class="spinner" id="save-fil-spinner" style="display:none"></span>
      </button>
    </div>
  </div>
</div>

<!-- Modal Niveau -->
<div class="modal-backdrop" id="modal-niveau">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title" id="modal-niv-title">Nouveau niveau</span>
      <button class="modal-close" data-close="modal-niveau">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="niv-code">
      <input type="hidden" id="niv-filiere-code">
      <div class="form-group">
        <label class="form-label">Filière</label>
        <input type="text" id="niv-filiere-display" class="form-control" readonly style="background:var(--surface)">
      </div>
      <div class="form-group">
        <label class="form-label">Libellé <span class="req">*</span></label>
        <input type="text" id="niv-libelle" class="form-control" placeholder="Ex : L1, L2, M1, BTS1…">
        <div class="form-error" id="err-niv-libelle"></div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-close="modal-niveau">Annuler</button>
      <button class="btn btn-primary" id="btn-save-niv">
        <span id="save-niv-text">Enregistrer</span>
        <span class="spinner" id="save-niv-spinner" style="display:none"></span>
      </button>
    </div>
  </div>
</div>

<!-- Modal Salle -->
<div class="modal-backdrop" id="modal-salle">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title" id="modal-salle-title">Nouvelle salle</span>
      <button class="modal-close" data-close="modal-salle">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="salle-code">
      <div class="form-group">
        <label class="form-label">Libellé <span class="req">*</span></label>
        <input type="text" id="salle-libelle" class="form-control" placeholder="Ex : Amphi A, Salle 101…">
        <div class="form-error" id="err-salle-libelle"></div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-close="modal-salle">Annuler</button>
      <button class="btn btn-primary" id="btn-save-salle">
        <span id="save-salle-text">Enregistrer</span>
        <span class="spinner" id="save-salle-spinner" style="display:none"></span>
      </button>
    </div>
  </div>
</div>

<div class="toast-container" id="toast-container"></div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/assets/js/ajax/auth.js"></script>
<script src="/assets/js/ajax/cycles.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inscriptions — <?= htmlspecialchars($_ENV['APP_NAME'] ?? 'EICG') ?></title>
  <link rel="stylesheet" href="/assets/css/app.css">
  <style>
    .tabs { display:flex; gap:4px; margin-bottom:20px; border-bottom:2px solid var(--border); }
    .tab-btn { padding:10px 20px; font-size:.875rem; font-weight:600; color:var(--text-muted); cursor:pointer; border:none; background:none; border-bottom:2px solid transparent; margin-bottom:-2px; transition:all .2s; }
    .tab-btn.active { color:var(--primary); border-bottom-color:var(--primary); }
    .tab-content { display:none; }
    .tab-content.active { display:block; }
    .ins-row { cursor:pointer; }
    .ins-panel { background:var(--surface); border-top:2px solid var(--accent); display:none; }
    .ins-panel.open { display:table-row; }
    .ins-inner { padding:16px 24px; }
    .expand-icon { transition:transform .2s; display:inline-block; color:var(--text-muted); font-size:.7rem; }
    .expandable.expanded .expand-icon { transform:rotate(90deg); }
    .statut-badge-valide  { background:rgba(39,174,96,.1); color:var(--success); }
    .statut-badge-solde   { background:rgba(46,134,193,.1); color:var(--info); }
    .statut-badge-annule  { background:rgba(224,62,62,.1); color:var(--danger); }
  </style>
</head>
<body>
<div class="layout">
  <?php require __DIR__ . '/partials/sidebar.php'; ?>
  <div class="main">
    <header class="topbar">
      <span class="topbar-title">Inscriptions</span>
      <div class="topbar-actions">
        <button class="btn btn-primary btn-sm" id="btn-add-ins">+ Nouvelle inscription</button>
      </div>
    </header>
    <main class="content">

      <div class="tabs">
        <button class="tab-btn active" data-tab="tab-inscriptions">Inscriptions</button>
        <button class="tab-btn" data-tab="tab-accessoires">Accessoires</button>
      </div>

      <!-- Tab Inscriptions -->
      <div class="tab-content active" id="tab-inscriptions">

        <!-- Filtres -->
        <div class="card" style="margin-bottom:20px">
          <div class="card-body" style="padding:16px 24px">
            <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
              <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <label class="form-label">Année scolaire</label>
                <select id="f-annee" class="form-control"><option value="">Toutes</option></select>
              </div>
              <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <label class="form-label">Classe</label>
                <select id="f-classe" class="form-control"><option value="">Toutes</option></select>
              </div>
              <div class="form-group" style="margin:0;flex:1;min-width:140px">
                <label class="form-label">Statut</label>
                <select id="f-statut" class="form-control">
                  <option value="">Tous</option>
                  <option value="valide">Valide</option>
                  <option value="solde">Soldé</option>
                  <option value="annule">Annulé</option>
                </select>
              </div>
              <div class="form-group" style="margin:0;flex:2;min-width:200px">
                <label class="form-label">Recherche</label>
                <input type="text" id="f-search" class="form-control" placeholder="Nom, prénom ou matricule…">
              </div>
              <button class="btn btn-outline btn-sm" id="btn-filter-ins">Filtrer</button>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <span class="card-title">Liste des inscriptions</span>
            <span class="text-sm text-muted" id="ins-count"></span>
          </div>
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th style="width:28px"></th>
                  <th>Étudiant</th>
                  <th>Matricule</th>
                  <th>Classe</th>
                  <th>Filière</th>
                  <th>Année</th>
                  <th>Montant</th>
                  <th>Statut</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="tbody-ins">
                <tr><td colspan="9" style="text-align:center;padding:40px;color:var(--text-muted)">Chargement…</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Tab Accessoires -->
      <div class="tab-content" id="tab-accessoires">
        <div class="card">
          <div class="card-header">
            <span class="card-title">Accessoires</span>
            <button class="btn btn-primary btn-sm" id="btn-add-acc">+ Nouvel accessoire</button>
          </div>
          <div class="table-responsive">
            <table class="table">
              <thead><tr><th>Libellé</th><th>Code</th><th>Statut</th><th>Actions</th></tr></thead>
              <tbody id="tbody-acc">
                <tr><td colspan="4" style="text-align:center;padding:40px;color:var(--text-muted)">Chargement…</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </main>
  </div>
</div>

<!-- Modal Inscription -->
<div class="modal-backdrop" id="modal-ins">
  <div class="modal" style="max-width:580px">
    <div class="modal-header">
      <span class="modal-title">Nouvelle inscription</span>
      <button class="modal-close" data-close="modal-ins">✕</button>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <label class="form-label">Année scolaire <span class="req">*</span></label>
        <select id="ins-annee" class="form-control"><option value="">— Choisir —</option></select>
        <div class="form-error" id="err-ins-annee"></div>
      </div>
      <div class="form-group">
        <label class="form-label">Étudiant <span class="req">*</span></label>
        <select id="ins-etudiant" class="form-control"><option value="">— Choisir un étudiant —</option></select>
        <div class="form-error" id="err-ins-etudiant"></div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Filière <span class="req">*</span></label>
          <select id="ins-filiere" class="form-control"><option value="">— Choisir —</option></select>
        </div>
        <div class="form-group">
          <label class="form-label">Classe <span class="req">*</span></label>
          <select id="ins-classe" class="form-control"><option value="">— Choisir d'abord une filière —</option></select>
          <div class="form-error" id="err-ins-classe"></div>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Montant scolarité (FCFA)</label>
        <input type="number" id="ins-montant" class="form-control" placeholder="Ex : 500000" min="0">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-close="modal-ins">Annuler</button>
      <button class="btn btn-primary" id="btn-save-ins">
        <span id="save-ins-text">Inscrire</span>
        <span class="spinner" id="save-ins-spinner" style="display:none"></span>
      </button>
    </div>
  </div>
</div>

<!-- Modal Modifier montant -->
<div class="modal-backdrop" id="modal-montant">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Modifier le montant</span>
      <button class="modal-close" data-close="modal-montant">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="mont-ins-code">
      <div class="form-group">
        <label class="form-label">Montant scolarité (FCFA) <span class="req">*</span></label>
        <input type="number" id="mont-valeur" class="form-control" min="0">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-close="modal-montant">Annuler</button>
      <button class="btn btn-primary" id="btn-save-mont">
        <span id="save-mont-text">Enregistrer</span>
        <span class="spinner" id="save-mont-spinner" style="display:none"></span>
      </button>
    </div>
  </div>
</div>

<!-- Modal Accessoire référentiel -->
<div class="modal-backdrop" id="modal-acc">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title" id="modal-acc-title">Nouvel accessoire</span>
      <button class="modal-close" data-close="modal-acc">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="acc-code">
      <div class="form-group">
        <label class="form-label">Libellé <span class="req">*</span></label>
        <input type="text" id="acc-libelle" class="form-control" placeholder="Ex : Tenue, Badge, Manuel…">
        <div class="form-error" id="err-acc-libelle"></div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-close="modal-acc">Annuler</button>
      <button class="btn btn-primary" id="btn-save-acc">
        <span id="save-acc-text">Enregistrer</span>
        <span class="spinner" id="save-acc-spinner" style="display:none"></span>
      </button>
    </div>
  </div>
</div>

<div class="toast-container" id="toast-container"></div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/assets/js/ajax/auth.js"></script>
<script src="/assets/js/ajax/inscriptions.js"></script>
</body>
</html>

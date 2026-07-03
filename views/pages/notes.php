<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notes & Bulletins — <?= htmlspecialchars($_ENV['APP_NAME'] ?? 'EICG') ?></title>
  <link rel="stylesheet" href="/assets/css/app.css">
  <style>
    .tabs { display:flex; gap:4px; margin-bottom:20px; border-bottom:2px solid var(--border); }
    .tab-btn { padding:10px 20px; font-size:.875rem; font-weight:600; color:var(--text-muted); cursor:pointer; border:none; background:none; border-bottom:2px solid transparent; margin-bottom:-2px; transition:all .2s; }
    .tab-btn.active { color:var(--primary); border-bottom-color:var(--primary); }
    .tab-content { display:none; }
    .tab-content.active { display:block; }

    /* Saisie de notes par classe */
    .saisie-grid { width:100%; border-collapse:collapse; font-size:.83rem; }
    .saisie-grid th { background:var(--primary); color:#fff; padding:10px 12px; text-align:left; font-size:.75rem; font-weight:600; border:1px solid rgba(255,255,255,.15); }
    .saisie-grid td { border:1px solid var(--border); padding:6px 10px; vertical-align:middle; }
    .saisie-grid tbody tr:hover { background:var(--surface); }
    .note-input { width:70px; padding:4px 8px; border:1.5px solid var(--border); border-radius:4px; font-size:.83rem; text-align:center; }
    .note-input:focus { outline:none; border-color:var(--primary-light); }
    .note-badge { display:inline-block; padding:2px 8px; border-radius:12px; font-size:.75rem; font-weight:600; }
    .note-ok    { background:rgba(39,174,96,.12); color:var(--success); }
    .note-med   { background:rgba(245,158,11,.12); color:#d97706; }
    .note-fail  { background:rgba(224,62,62,.12);  color:var(--danger); }

    /* Bulletin */
    .bulletin-card { background:var(--white); border:1px solid var(--border); border-radius:var(--radius); padding:28px; max-width:760px; margin:0 auto; }
    .bulletin-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; padding-bottom:16px; border-bottom:2px solid var(--primary); }
    .bulletin-school { font-family:'DM Serif Display',serif; font-size:1.2rem; color:var(--primary); }
    .bulletin-title  { font-size:.85rem; color:var(--text-muted); text-align:right; }
    .bulletin-student { background:var(--surface); border-radius:var(--radius); padding:14px 18px; margin-bottom:16px; display:grid; grid-template-columns:repeat(3,1fr); gap:10px; }
    .bul-field label { font-size:.68rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; display:block; }
    .bul-field span  { font-size:.875rem; color:var(--text); font-weight:500; }
    .bul-table { width:100%; border-collapse:collapse; font-size:.83rem; margin-bottom:16px; }
    .bul-table th { background:var(--primary); color:#fff; padding:8px 12px; text-align:left; font-size:.75rem; }
    .bul-table td { border-bottom:1px solid var(--border); padding:8px 12px; }
    .bul-table tr:last-child td { border-bottom:none; }
    .bul-total { background:var(--primary); color:#fff; border-radius:var(--radius); padding:12px 18px; display:flex; justify-content:space-between; align-items:center; }
    .bul-avg   { font-size:1.3rem; font-weight:700; }

    /* Classement */
    .rank-1 { color:gold; font-weight:800; }
    .rank-2 { color:silver; font-weight:700; }
    .rank-3 { color:#cd7f32; font-weight:700; }
  </style>
</head>
<body>
<div class="layout">
  <?php require __DIR__ . '/partials/sidebar.php'; ?>
  <div class="main">
    <header class="topbar">
      <span class="topbar-title">Notes & Bulletins</span>
    </header>
    <main class="content">

      <div class="tabs">
        <button class="tab-btn active" data-tab="tab-saisie">Saisie des notes</button>
        <button class="tab-btn" data-tab="tab-bulletin">Bulletin étudiant</button>
        <button class="tab-btn" data-tab="tab-classement">Classement</button>
      </div>

      <!-- ═══ TAB SAISIE ═══ -->
      <div class="tab-content active" id="tab-saisie">
        <div class="card" style="margin-bottom:20px">
          <div class="card-body" style="padding:16px 24px">
            <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
              <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <label class="form-label">Année scolaire</label>
                <select id="s-annee" class="form-control"><option value="">— Choisir —</option></select>
              </div>
              <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <label class="form-label">Semestre</label>
                <select id="s-semestre" class="form-control" disabled><option value="">— Choisir d'abord une année —</option></select>
              </div>
              <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <label class="form-label">Classe</label>
                <select id="s-classe" class="form-control"><option value="">— Choisir —</option></select>
              </div>
              <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <label class="form-label">Matière</label>
                <select id="s-matiere" class="form-control"><option value="">— Choisir —</option></select>
              </div>
              <div class="form-group" style="margin:0;max-width:160px">
                <label class="form-label">Type d'évaluation</label>
                <select id="s-type" class="form-control">
                  <option value="">— Choisir —</option>
                  <option value="devoir1">Devoir 1</option>
                  <option value="devoir2">Devoir 2</option>
                  <option value="examen">Examen</option>
                  <option value="tp">TP</option>
                  <option value="rattrapage">Rattrapage</option>
                </select>
              </div>
              <button class="btn btn-primary btn-sm" id="btn-charger-saisie" style="align-self:flex-end">Charger</button>
            </div>
          </div>
        </div>

        <div class="card" id="saisie-panel" style="display:none">
          <div class="card-header">
            <span class="card-title" id="saisie-title">Saisie des notes</span>
            <div class="d-flex gap-2 align-center">
              <span class="text-sm text-muted" id="saisie-info"></span>
              <button class="btn btn-primary btn-sm" id="btn-enregistrer-notes">Enregistrer tout</button>
            </div>
          </div>
          <div style="overflow-x:auto">
            <table class="saisie-grid">
              <thead>
                <tr>
                  <th>Étudiant</th>
                  <th>Matricule</th>
                  <th style="width:90px;text-align:center">Note /20</th>
                  <th>Observation</th>
                  <th style="width:80px">Statut</th>
                </tr>
              </thead>
              <tbody id="tbody-saisie"></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ═══ TAB BULLETIN ═══ -->
      <div class="tab-content" id="tab-bulletin">
        <div class="card" style="margin-bottom:20px">
          <div class="card-body" style="padding:16px 24px">
            <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
              <div class="form-group" style="margin:0;flex:2;min-width:200px">
                <label class="form-label">Étudiant (rechercher par nom ou matricule)</label>
                <select id="b-inscription" class="form-control"><option value="">— Choisir un étudiant —</option></select>
              </div>
              <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <label class="form-label">Semestre</label>
                <select id="b-semestre" class="form-control"><option value="">— Choisir —</option></select>
              </div>
              <button class="btn btn-primary btn-sm" id="btn-afficher-bulletin" style="align-self:flex-end">Afficher</button>
            </div>
          </div>
        </div>
        <div id="bulletin-view"></div>
      </div>

      <!-- ═══ TAB CLASSEMENT ═══ -->
      <div class="tab-content" id="tab-classement">
        <div class="card" style="margin-bottom:20px">
          <div class="card-body" style="padding:16px 24px">
            <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
              <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <label class="form-label">Classe</label>
                <select id="c-classe" class="form-control"><option value="">— Choisir —</option></select>
              </div>
              <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <label class="form-label">Semestre</label>
                <select id="c-semestre" class="form-control"><option value="">— Choisir —</option></select>
              </div>
              <button class="btn btn-primary btn-sm" id="btn-afficher-classement" style="align-self:flex-end">Afficher</button>
            </div>
          </div>
        </div>
        <div class="card" id="classement-panel" style="display:none">
          <div class="card-header">
            <span class="card-title" id="classement-title">Classement</span>
            <span class="text-sm text-muted" id="classement-info"></span>
          </div>
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr><th style="width:60px">Rang</th><th>Étudiant</th><th>Matricule</th><th>Matières évaluées</th><th>Moyenne générale</th><th>Mention</th></tr>
              </thead>
              <tbody id="tbody-classement"></tbody>
            </table>
          </div>
        </div>
      </div>

    </main>
  </div>
</div>

<!-- Modal note individuelle -->
<div class="modal-backdrop" id="modal-note">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title" id="modal-note-title">Modifier la note</span>
      <button class="modal-close" data-close="modal-note">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="note-code">
      <div class="form-group">
        <label class="form-label">Note /20 <span class="req">*</span></label>
        <input type="number" id="note-valeur" class="form-control" min="0" max="20" step="0.25">
        <div class="form-error" id="err-note-valeur"></div>
      </div>
      <div class="form-group">
        <label class="form-label">Type d'évaluation <span class="req">*</span></label>
        <select id="note-type" class="form-control">
          <option value="devoir1">Devoir 1</option>
          <option value="devoir2">Devoir 2</option>
          <option value="examen">Examen</option>
          <option value="tp">TP</option>
          <option value="rattrapage">Rattrapage</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Observation</label>
        <input type="text" id="note-obs" class="form-control" placeholder="Optionnel…">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-close="modal-note">Annuler</button>
      <button class="btn btn-primary" id="btn-save-note">
        <span id="save-note-text">Enregistrer</span>
        <span class="spinner" id="save-note-spinner" style="display:none"></span>
      </button>
    </div>
  </div>
</div>

<div class="toast-container" id="toast-container"></div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/assets/js/ajax/auth.js"></script>
<script src="/assets/js/ajax/notes.js"></script>
</body>
</html>

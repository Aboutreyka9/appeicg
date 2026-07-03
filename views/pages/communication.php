<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Communication — <?= htmlspecialchars($_ENV['APP_NAME'] ?? 'EICG') ?></title>
  <link rel="stylesheet" href="/assets/css/app.css">
  <style>
    .tabs { display:flex; gap:4px; margin-bottom:20px; border-bottom:2px solid var(--border); }
    .tab-btn { padding:10px 20px; font-size:.875rem; font-weight:600; color:var(--text-muted); cursor:pointer; border:none; background:none; border-bottom:2px solid transparent; margin-bottom:-2px; transition:all .2s; }
    .tab-btn.active { color:var(--primary); border-bottom-color:var(--primary); }
    .tab-content { display:none; }
    .tab-content.active { display:block; }

    /* Messages */
    .msg-card { background:var(--white); border:1px solid var(--border); border-radius:var(--radius); padding:18px 20px; margin-bottom:12px; display:flex; align-items:flex-start; gap:16px; }
    .msg-card:hover { border-color:var(--primary-light); }
    .msg-icon { width:42px; height:42px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:1.1rem; }
    .msg-icon.en_attente  { background:rgba(245,158,11,.12); }
    .msg-icon.envoye      { background:rgba(46,134,193,.12); }
    .msg-icon.vue         { background:rgba(39,174,96,.12); }
    .msg-icon.archive     { background:rgba(107,122,153,.12); }
    .msg-body { flex:1; min-width:0; }
    .msg-objet { font-weight:600; font-size:.9rem; color:var(--text); margin-bottom:4px; }
    .msg-desc  { font-size:.82rem; color:var(--text-muted); line-height:1.5; }
    .msg-meta  { font-size:.72rem; color:var(--text-muted); margin-top:6px; }
    .msg-actions { display:flex; gap:6px; flex-shrink:0; flex-wrap:wrap; }

    /* Événements */
    .ev-card { background:var(--white); border:1px solid var(--border); border-radius:var(--radius); overflow:hidden; display:flex; gap:0; margin-bottom:12px; }
    .ev-badge-principal { position:absolute; top:8px; left:8px; background:var(--accent); color:#fff; font-size:.65rem; font-weight:700; padding:2px 8px; border-radius:10px; }
    .ev-body { padding:16px 20px; flex:1; }
    .ev-titre { font-weight:700; font-size:.95rem; color:var(--text); margin-bottom:4px; }
    .ev-desc  { font-size:.82rem; color:var(--text-muted); line-height:1.5; }
    .ev-meta  { font-size:.72rem; color:var(--text-muted); margin-top:8px; }
    .ev-actions { padding:16px; display:flex; flex-direction:column; gap:6px; justify-content:center; flex-shrink:0; }

    /* Statut badges */
    .statut-en_attente { background:rgba(245,158,11,.1); color:#d97706; }
    .statut-envoye     { background:rgba(46,134,193,.1); color:var(--info); }
    .statut-vue        { background:rgba(39,174,96,.1);  color:var(--success); }
    .statut-archive    { background:rgba(107,122,153,.1);color:var(--text-muted); }
  </style>
</head>
<body>
<div class="layout">
  <?php require __DIR__ . '/partials/sidebar.php'; ?>
  <div class="main">
    <header class="topbar">
      <span class="topbar-title">Documents & Communication</span>
    </header>
    <main class="content">

      <div class="tabs">
        <button class="tab-btn active" data-tab="tab-docs">Documents</button>
        <button class="tab-btn" data-tab="tab-messages">Messagerie</button>
        <button class="tab-btn" data-tab="tab-evenements">Événements</button>
      </div>

      <!-- ═══ TAB DOCUMENTS ═══ -->
      <div class="tab-content active" id="tab-docs">

        <!-- Filtres -->
        <div class="card" style="margin-bottom:20px">
          <div class="card-body" style="padding:16px 24px">
            <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
              <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <label class="form-label">Filière</label>
                <select id="d-filiere" class="form-control"><option value="">Toutes</option></select>
              </div>
              <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <label class="form-label">Niveau</label>
                <select id="d-niveau" class="form-control"><option value="">Tous</option></select>
              </div>
              <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <label class="form-label">Année</label>
                <select id="d-annee" class="form-control"><option value="">Toutes</option></select>
              </div>
              <div style="display:flex;gap:8px;align-self:flex-end">
                <button class="btn btn-outline btn-sm" id="btn-filter-docs">Filtrer</button>
                <button class="btn btn-primary btn-sm" id="btn-add-doc">+ Ajouter</button>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <span class="card-title">Documents disponibles</span>
            <span class="text-sm text-muted" id="docs-count"></span>
          </div>
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr><th>Libellé</th><th>Filière</th><th>Niveau</th><th>Année</th><th>Lien</th><th>Actions</th></tr>
              </thead>
              <tbody id="tbody-docs">
                <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text-muted)">Chargement…</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ═══ TAB MESSAGES ═══ -->
      <div class="tab-content" id="tab-messages">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
          <div class="d-flex gap-2">
            <select id="f-msg-statut" class="form-control" style="width:180px">
              <option value="">Tous les statuts</option>
              <option value="en_attente">En attente</option>
              <option value="envoye">Envoyé</option>
              <option value="vue">Vu</option>
              <option value="archive">Archivé</option>
            </select>
            <button class="btn btn-outline btn-sm" id="btn-filter-msgs">Filtrer</button>
          </div>
          <button class="btn btn-primary btn-sm" id="btn-add-msg">+ Nouveau message</button>
        </div>
        <div id="messages-list">
          <p class="text-muted" style="text-align:center;padding:40px">Chargement…</p>
        </div>
      </div>

      <!-- ═══ TAB ÉVÉNEMENTS ═══ -->
      <div class="tab-content" id="tab-evenements">
        <div style="display:flex;justify-content:flex-end;margin-bottom:16px">
          <button class="btn btn-primary btn-sm" id="btn-add-ev">+ Nouvel événement</button>
        </div>
        <div id="evenements-list">
          <p class="text-muted" style="text-align:center;padding:40px">Chargement…</p>
        </div>
      </div>

    </main>
  </div>
</div>

<!-- Modal Document -->
<div class="modal-backdrop" id="modal-doc">
  <div class="modal" style="max-width:560px">
    <div class="modal-header">
      <span class="modal-title" id="modal-doc-title">Ajouter un document</span>
      <button class="modal-close" data-close="modal-doc">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="doc-id">
      <div class="form-group">
        <label class="form-label">Libellé <span class="req">*</span></label>
        <input type="text" id="doc-libelle" class="form-control" placeholder="Ex : Programme de cours S1">
        <div class="form-error" id="err-doc-libelle"></div>
      </div>
      <div class="form-group">
        <label class="form-label">Lien / URL <span class="req">*</span></label>
        <input type="text" id="doc-lien" class="form-control" placeholder="https://… ou chemin relatif">
        <div class="form-error" id="err-doc-lien"></div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Filière <span class="req">*</span></label>
          <select id="doc-filiere" class="form-control"><option value="">— Choisir —</option></select>
          <div class="form-error" id="err-doc-filiere"></div>
        </div>
        <div class="form-group">
          <label class="form-label">Niveau <span class="req">*</span></label>
          <select id="doc-niveau" class="form-control"><option value="">— Choisir d'abord une filière —</option></select>
          <div class="form-error" id="err-doc-niveau"></div>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Année scolaire <span class="req">*</span></label>
        <select id="doc-annee" class="form-control"><option value="">— Choisir —</option></select>
        <div class="form-error" id="err-doc-annee"></div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-close="modal-doc">Annuler</button>
      <button class="btn btn-primary" id="btn-save-doc">
        <span id="save-doc-text">Enregistrer</span>
        <span class="spinner" id="save-doc-spinner" style="display:none"></span>
      </button>
    </div>
  </div>
</div>

<!-- Modal Message -->
<div class="modal-backdrop" id="modal-msg">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Nouveau message</span>
      <button class="modal-close" data-close="modal-msg">✕</button>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <label class="form-label">Objet <span class="req">*</span></label>
        <input type="text" id="msg-objet" class="form-control" placeholder="Objet du message…">
        <div class="form-error" id="err-msg-objet"></div>
      </div>
      <div class="form-group">
        <label class="form-label">Contenu <span class="req">*</span></label>
        <textarea id="msg-desc" class="form-control" rows="5" placeholder="Rédigez votre message…" style="resize:vertical"></textarea>
        <div class="form-error" id="err-msg-desc"></div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-close="modal-msg">Annuler</button>
      <button class="btn btn-primary" id="btn-save-msg">
        <span id="save-msg-text">Enregistrer</span>
        <span class="spinner" id="save-msg-spinner" style="display:none"></span>
      </button>
    </div>
  </div>
</div>

<!-- Modal Événement -->
<div class="modal-backdrop" id="modal-ev">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title" id="modal-ev-title">Nouvel événement</span>
      <button class="modal-close" data-close="modal-ev">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="ev-id">
      <div class="form-group">
        <label class="form-label">Titre <span class="req">*</span></label>
        <input type="text" id="ev-titre" class="form-control" placeholder="Titre de l'événement">
        <div class="form-error" id="err-ev-titre"></div>
      </div>
      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea id="ev-desc" class="form-control" rows="4" placeholder="Description…" style="resize:vertical"></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Image (URL)</label>
        <input type="text" id="ev-image" class="form-control" placeholder="https://…">
      </div>
      <div class="form-group">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
          <input type="checkbox" id="ev-principal"> Événement principal (à la une)
        </label>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-close="modal-ev">Annuler</button>
      <button class="btn btn-primary" id="btn-save-ev">
        <span id="save-ev-text">Enregistrer</span>
        <span class="spinner" id="save-ev-spinner" style="display:none"></span>
      </button>
    </div>
  </div>
</div>

<div class="toast-container" id="toast-container"></div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/assets/js/ajax/auth.js"></script>
<script src="/assets/js/ajax/communication.js"></script>
</body>
</html>

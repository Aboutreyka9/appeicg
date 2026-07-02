<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Étudiants — <?= htmlspecialchars($_ENV['APP_NAME'] ?? 'EICG') ?></title>
  <link rel="stylesheet" href="/assets/css/app.css">
  <style>
    .etu-avatar { width:38px; height:38px; border-radius:50%; background:linear-gradient(135deg,var(--primary-light),var(--accent)); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:.8rem; flex-shrink:0; }
    .detail-panel { background:var(--surface); border-top:2px solid var(--accent); display:none; }
    .detail-panel.open { display:table-row; }
    .detail-inner { padding:20px 24px; }
    .detail-tabs { display:flex; gap:4px; margin-bottom:16px; border-bottom:1px solid var(--border); }
    .dtab { padding:8px 16px; font-size:.82rem; font-weight:600; color:var(--text-muted); cursor:pointer; border:none; background:none; border-bottom:2px solid transparent; margin-bottom:-1px; }
    .dtab.active { color:var(--primary); border-bottom-color:var(--primary); }
    .dtab-content { display:none; }
    .dtab-content.active { display:block; }
    .info-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; }
    .info-item label { font-size:.72rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.04em; display:block; margin-bottom:2px; }
    .info-item span { font-size:.875rem; color:var(--text); }
    .doc-item { display:flex; align-items:center; justify-content:space-between; padding:10px 14px; background:var(--white); border:1px solid var(--border); border-radius:var(--radius); margin-bottom:8px; }
    .doc-label { font-size:.875rem; font-weight:500; }
    .doc-meta { font-size:.75rem; color:var(--text-muted); }
    .expandable { cursor:pointer; }
    .expand-icon { transition:transform .2s; display:inline-block; color:var(--text-muted); font-size:.7rem; }
    .expandable.expanded .expand-icon { transform:rotate(90deg); }
    .search-bar { display:flex; gap:10px; margin-bottom:20px; }
  </style>
</head>
<body>
<div class="layout">
  <?php require __DIR__ . '/partials/sidebar.php'; ?>
  <div class="main">
    <header class="topbar">
      <span class="topbar-title">Étudiants</span>
      <div class="topbar-actions">
        <button class="btn btn-primary btn-sm" id="btn-add-etu">+ Nouvel étudiant</button>
      </div>
    </header>
    <main class="content">

      <!-- Barre de recherche -->
      <div class="search-bar">
        <input type="text" id="search-etu" class="form-control" placeholder="Rechercher par nom, prénom ou matricule…" style="max-width:360px">
        <select id="filter-statut" class="form-control" style="max-width:180px">
          <option value="">Tous les statuts</option>
          <option value="actif">Actifs</option>
          <option value="inactif">Inactifs</option>
        </select>
        <button class="btn btn-outline btn-sm" id="btn-search">Rechercher</button>
      </div>

      <div class="card">
        <div class="card-header">
          <span class="card-title">Liste des étudiants</span>
          <span class="text-sm text-muted" id="etu-count"></span>
        </div>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th style="width:28px"></th>
                <th>Étudiant</th>
                <th>Matricule</th>
                <th>Téléphone</th>
                <th>Sexe</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="tbody-etu">
              <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted)">Chargement…</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>

<!-- Modal Étudiant -->
<div class="modal-backdrop" id="modal-etu">
  <div class="modal" style="max-width:640px">
    <div class="modal-header">
      <span class="modal-title" id="modal-etu-title">Nouvel étudiant</span>
      <button class="modal-close" data-close="modal-etu">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="etu-code">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Nom <span class="req">*</span></label>
          <input type="text" id="etu-nom" class="form-control" placeholder="NOM">
          <div class="form-error" id="err-etu-nom"></div>
        </div>
        <div class="form-group">
          <label class="form-label">Prénom(s) <span class="req">*</span></label>
          <input type="text" id="etu-prenom" class="form-control" placeholder="Prénom(s)">
          <div class="form-error" id="err-etu-prenom"></div>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Sexe</label>
          <select id="etu-sexe" class="form-control">
            <option value="">— Choisir —</option>
            <option value="M">Masculin</option>
            <option value="F">Féminin</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Date de naissance</label>
          <input type="date" id="etu-datenaissance" class="form-control">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Lieu de naissance</label>
          <input type="text" id="etu-lieunaissance" class="form-control" placeholder="Ex : Abidjan">
        </div>
        <div class="form-group">
          <label class="form-label">Nationalité</label>
          <input type="text" id="etu-nationalite" class="form-control" placeholder="Ex : Ivoirienne">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Téléphone</label>
          <input type="text" id="etu-telephone" class="form-control" placeholder="+225 07 00 00 00 00">
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" id="etu-email" class="form-control" placeholder="email@exemple.ci">
          <div class="form-error" id="err-etu-email"></div>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Lieu de résidence</label>
          <input type="text" id="etu-residence" class="form-control" placeholder="Quartier, ville…">
        </div>
        <div class="form-group">
          <label class="form-label">N° CNI / Passeport</label>
          <input type="text" id="etu-cni" class="form-control" placeholder="CI0000000">
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-close="modal-etu">Annuler</button>
      <button class="btn btn-primary" id="btn-save-etu">
        <span id="save-etu-text">Enregistrer</span>
        <span class="spinner" id="save-etu-spinner" style="display:none"></span>
      </button>
    </div>
  </div>
</div>

<!-- Modal Parent -->
<div class="modal-backdrop" id="modal-parent">
  <div class="modal" style="max-width:600px">
    <div class="modal-header">
      <span class="modal-title">Fiche parent — <span id="parent-etu-nom"></span></span>
      <button class="modal-close" data-close="modal-parent">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="parent-etu-code">
      <p class="text-sm text-muted mb-3">Renseignez au moins un contact (père, mère ou tuteur).</p>

      <fieldset style="border:1px solid var(--border);border-radius:var(--radius);padding:16px;margin-bottom:16px">
        <legend style="font-size:.8rem;font-weight:600;color:var(--primary);padding:0 8px">Père</legend>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Nom complet</label><input type="text" id="p-nom-pere" class="form-control" placeholder="Nom du père"></div>
          <div class="form-group"><label class="form-label">Téléphone</label><input type="text" id="p-tel-pere" class="form-control" placeholder="+225…"></div>
        </div>
        <div class="form-group"><label class="form-label">Profession</label><input type="text" id="p-prof-pere" class="form-control" placeholder="Ex : Commerçant"></div>
      </fieldset>

      <fieldset style="border:1px solid var(--border);border-radius:var(--radius);padding:16px;margin-bottom:16px">
        <legend style="font-size:.8rem;font-weight:600;color:var(--primary);padding:0 8px">Mère</legend>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Nom complet</label><input type="text" id="p-nom-mere" class="form-control" placeholder="Nom de la mère"></div>
          <div class="form-group"><label class="form-label">Téléphone</label><input type="text" id="p-tel-mere" class="form-control" placeholder="+225…"></div>
        </div>
        <div class="form-group"><label class="form-label">Profession</label><input type="text" id="p-prof-mere" class="form-control" placeholder="Ex : Enseignante"></div>
      </fieldset>

      <fieldset style="border:1px solid var(--border);border-radius:var(--radius);padding:16px">
        <legend style="font-size:.8rem;font-weight:600;color:var(--primary);padding:0 8px">Tuteur</legend>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Nom complet</label><input type="text" id="p-nom-tuteur" class="form-control" placeholder="Nom du tuteur"></div>
          <div class="form-group"><label class="form-label">Téléphone</label><input type="text" id="p-tel-tuteur" class="form-control" placeholder="+225…"></div>
        </div>
      </fieldset>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-close="modal-parent">Annuler</button>
      <button class="btn btn-primary" id="btn-save-parent">
        <span id="save-parent-text">Enregistrer</span>
        <span class="spinner" id="save-parent-spinner" style="display:none"></span>
      </button>
    </div>
  </div>
</div>

<!-- Modal Dossier -->
<div class="modal-backdrop" id="modal-dossier">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Ajouter un document</span>
      <button class="modal-close" data-close="modal-dossier">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="dos-etu-code">
      <div class="form-group">
        <label class="form-label">Libellé du document <span class="req">*</span></label>
        <input type="text" id="dos-libelle" class="form-control" placeholder="Ex : Copie CNI, Acte de naissance, Photo…">
        <div class="form-error" id="err-dos-libelle"></div>
      </div>
      <div class="form-group">
        <label class="form-label">Année scolaire</label>
        <select id="dos-annee" class="form-control">
          <option value="">— Aucune —</option>
        </select>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-close="modal-dossier">Annuler</button>
      <button class="btn btn-primary" id="btn-save-dos">
        <span id="save-dos-text">Ajouter</span>
        <span class="spinner" id="save-dos-spinner" style="display:none"></span>
      </button>
    </div>
  </div>
</div>

<div class="toast-container" id="toast-container"></div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/assets/js/ajax/auth.js"></script>
<script src="/assets/js/ajax/etudiants.js"></script>
</body>
</html>

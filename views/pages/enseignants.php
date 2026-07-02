<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Enseignants — <?= htmlspecialchars($_ENV['APP_NAME'] ?? 'EICG') ?></title>
  <link rel="stylesheet" href="/assets/css/app.css">
  <style>
    .ens-avatar { width:36px; height:36px; border-radius:50%; background:var(--primary-light); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:.8rem; flex-shrink:0; }
    .mat-tag { display:inline-flex; align-items:center; gap:4px; background:rgba(27,58,107,.08); color:var(--primary); border-radius:20px; padding:2px 10px; font-size:.72rem; font-weight:600; margin:2px; }
    .mat-tag .remove-mat { cursor:pointer; color:var(--danger); margin-left:2px; font-weight:700; }
    .mat-panel { background:var(--surface); border-top:2px solid var(--accent); display:none; }
    .mat-panel.open { display:table-row; }
    .mat-inner { padding:16px 24px; }
    .expandable { cursor:pointer; }
    .expand-icon { transition:transform .2s; display:inline-block; color:var(--text-muted); font-size:.7rem; }
    .expandable.expanded .expand-icon { transform:rotate(90deg); }
  </style>
</head>
<body>
<div class="layout">
  <?php require __DIR__ . '/partials/sidebar.php'; ?>
  <div class="main">
    <header class="topbar">
      <span class="topbar-title">Enseignants</span>
      <div class="topbar-actions">
        <button class="btn btn-primary btn-sm" id="btn-add-ens">+ Nouvel enseignant</button>
      </div>
    </header>
    <main class="content">
      <div class="card">
        <div class="card-header">
          <span class="card-title">Liste des enseignants</span>
          <span class="text-sm text-muted" id="ens-count"></span>
        </div>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th style="width:28px"></th>
                <th>Enseignant</th>
                <th>Matricule</th>
                <th>Téléphone</th>
                <th>Email</th>
                <th>Matières</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="tbody-ens">
              <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted)">Chargement…</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>

<!-- Modal Enseignant -->
<div class="modal-backdrop" id="modal-ens">
  <div class="modal" style="max-width:600px">
    <div class="modal-header">
      <span class="modal-title" id="modal-ens-title">Nouvel enseignant</span>
      <button class="modal-close" data-close="modal-ens">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="ens-code">
      <div class="form-group">
        <label class="form-label">Nom complet <span class="req">*</span></label>
        <input type="text" id="ens-nom" class="form-control" placeholder="Ex : KOUASSI Jean-Marc">
        <div class="form-error" id="err-ens-nom"></div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Téléphone <span class="req">*</span></label>
          <input type="text" id="ens-telephone" class="form-control" placeholder="+225 07 00 00 00 00">
          <div class="form-error" id="err-ens-telephone"></div>
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" id="ens-email" class="form-control" placeholder="email@exemple.ci">
          <div class="form-error" id="err-ens-email"></div>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Sexe</label>
          <select id="ens-sexe" class="form-control">
            <option value="">— Choisir —</option>
            <option value="M">Masculin</option>
            <option value="F">Féminin</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Date de naissance</label>
          <input type="date" id="ens-datenaissance" class="form-control">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Lieu de naissance</label>
        <input type="text" id="ens-lieunaissance" class="form-control" placeholder="Ex : Abidjan">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-close="modal-ens">Annuler</button>
      <button class="btn btn-primary" id="btn-save-ens">
        <span id="save-ens-text">Enregistrer</span>
        <span class="spinner" id="save-ens-spinner" style="display:none"></span>
      </button>
    </div>
  </div>
</div>

<!-- Modal Affectation Matières -->
<div class="modal-backdrop" id="modal-aff">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Matières de <span id="aff-ens-nom"></span></span>
      <button class="modal-close" data-close="modal-aff">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="aff-ens-code">
      <div class="form-group">
        <label class="form-label">Ajouter une matière</label>
        <div class="d-flex gap-2">
          <select id="aff-matiere-select" class="form-control">
            <option value="">— Choisir une matière —</option>
          </select>
          <button class="btn btn-accent" id="btn-affecter" style="white-space:nowrap">Affecter</button>
        </div>
      </div>
      <div style="margin-top:16px;">
        <label class="form-label">Matières affectées</label>
        <div id="aff-matieres-list"><p class="text-sm text-muted">Aucune matière.</p></div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-primary" data-close="modal-aff">Fermer</button>
    </div>
  </div>
</div>

<div class="toast-container" id="toast-container"></div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/assets/js/ajax/auth.js"></script>
<script src="/assets/js/ajax/enseignants.js"></script>
</body>
</html>

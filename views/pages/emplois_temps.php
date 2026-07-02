<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Emplois du temps — <?= htmlspecialchars($_ENV['APP_NAME'] ?? 'EICG') ?></title>
  <link rel="stylesheet" href="/assets/css/app.css">
  <style>
    .view-tabs { display:flex; gap:4px; margin-bottom:20px; border-bottom:2px solid var(--border); }
    .vtab { padding:10px 20px; font-size:.875rem; font-weight:600; color:var(--text-muted); cursor:pointer; border:none; background:none; border-bottom:2px solid transparent; margin-bottom:-2px; transition:all .2s; }
    .vtab.active { color:var(--primary); border-bottom-color:var(--primary); }

    /* Grille hebdomadaire */
    .timetable { width:100%; border-collapse:collapse; font-size:.78rem; }
    .timetable th { background:var(--primary); color:#fff; padding:10px 8px; text-align:center; font-weight:600; border:1px solid rgba(255,255,255,.15); }
    .timetable td { border:1px solid var(--border); padding:4px; vertical-align:top; min-width:120px; min-height:60px; }
    .timetable td.time-col { background:var(--surface); color:var(--text-muted); font-size:.72rem; text-align:center; font-weight:600; width:70px; }
    .slot { background:var(--primary-light); color:#fff; border-radius:6px; padding:6px 8px; margin:2px; font-size:.72rem; line-height:1.4; }
    .slot .slot-mat { font-weight:700; }
    .slot .slot-meta { opacity:.85; font-size:.68rem; margin-top:2px; }
    .slot .slot-actions { display:flex; gap:4px; margin-top:4px; }
    .slot .slot-btn { background:rgba(255,255,255,.2); border:none; color:#fff; border-radius:3px; padding:2px 6px; font-size:.65rem; cursor:pointer; }
    .slot .slot-btn:hover { background:rgba(255,255,255,.35); }
    .slot .slot-btn.del { background:rgba(224,62,62,.4); }
    .slot .slot-btn.del:hover { background:rgba(224,62,62,.7); }

    .filters-bar { display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end; margin-bottom:20px; }
    .filters-bar .form-group { margin:0; flex:1; min-width:160px; }
  </style>
</head>
<body>
<div class="layout">
  <?php require __DIR__ . '/partials/sidebar.php'; ?>
  <div class="main">
    <header class="topbar">
      <span class="topbar-title">Emplois du temps</span>
      <div class="topbar-actions">
        <button class="btn btn-primary btn-sm" id="btn-add-emp">+ Ajouter un créneau</button>
      </div>
    </header>
    <main class="content">

      <!-- Filtres -->
      <div class="card" style="margin-bottom:20px">
        <div class="card-body" style="padding:16px 24px">
          <div class="filters-bar">
            <div class="form-group">
              <label class="form-label">Année scolaire</label>
              <select id="f-emp-annee" class="form-control"><option value="">Toutes</option></select>
            </div>
            <div class="form-group">
              <label class="form-label">Vue par classe</label>
              <select id="f-emp-classe" class="form-control"><option value="">Toutes les classes</option></select>
            </div>
            <div class="form-group">
              <label class="form-label">Vue par enseignant</label>
              <select id="f-emp-ens" class="form-control"><option value="">Tous les enseignants</option></select>
            </div>
            <div class="form-group" style="max-width:140px">
              <label class="form-label">Jour</label>
              <select id="f-emp-jour" class="form-control">
                <option value="">Tous</option>
                <option value="lundi">Lundi</option>
                <option value="mardi">Mardi</option>
                <option value="mercredi">Mercredi</option>
                <option value="jeudi">Jeudi</option>
                <option value="vendredi">Vendredi</option>
                <option value="samedi">Samedi</option>
              </select>
            </div>
            <button class="btn btn-outline btn-sm" id="btn-filter-emp" style="align-self:flex-end">Afficher</button>
          </div>
        </div>
      </div>

      <!-- Onglets vue -->
      <div class="view-tabs">
        <button class="vtab active" data-view="grille">Vue grille</button>
        <button class="vtab" data-view="liste">Vue liste</button>
      </div>

      <!-- Vue grille -->
      <div id="view-grille">
        <div class="card">
          <div class="card-header">
            <span class="card-title" id="grille-title">Grille hebdomadaire</span>
            <span class="text-sm text-muted" id="emp-count"></span>
          </div>
          <div style="overflow-x:auto; padding:16px">
            <table class="timetable" id="timetable">
              <thead>
                <tr>
                  <th>Heure</th>
                  <th>Lundi</th>
                  <th>Mardi</th>
                  <th>Mercredi</th>
                  <th>Jeudi</th>
                  <th>Vendredi</th>
                  <th>Samedi</th>
                </tr>
              </thead>
              <tbody id="tbody-grille">
                <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted)">Sélectionnez une classe ou un enseignant pour afficher l'emploi du temps.</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Vue liste -->
      <div id="view-liste" style="display:none">
        <div class="card">
          <div class="card-header">
            <span class="card-title">Liste des créneaux</span>
          </div>
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>Jour</th>
                  <th>Horaire</th>
                  <th>Matière</th>
                  <th>Enseignant</th>
                  <th>Classe</th>
                  <th>Salle</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="tbody-liste">
                <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted)">Chargement…</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </main>
  </div>
</div>

<!-- Modal Créneau -->
<div class="modal-backdrop" id="modal-emp">
  <div class="modal" style="max-width:580px">
    <div class="modal-header">
      <span class="modal-title" id="modal-emp-title">Nouveau créneau</span>
      <button class="modal-close" data-close="modal-emp">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="emp-code">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Année scolaire <span class="req">*</span></label>
          <select id="emp-annee" class="form-control"><option value="">— Choisir —</option></select>
          <div class="form-error" id="err-emp-annee"></div>
        </div>
        <div class="form-group">
          <label class="form-label">Jour <span class="req">*</span></label>
          <select id="emp-jour" class="form-control">
            <option value="">— Choisir —</option>
            <option value="lundi">Lundi</option>
            <option value="mardi">Mardi</option>
            <option value="mercredi">Mercredi</option>
            <option value="jeudi">Jeudi</option>
            <option value="vendredi">Vendredi</option>
            <option value="samedi">Samedi</option>
            <option value="dimanche">Dimanche</option>
          </select>
          <div class="form-error" id="err-emp-jour"></div>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Heure début <span class="req">*</span></label>
          <input type="time" id="emp-debut" class="form-control">
          <div class="form-error" id="err-emp-debut"></div>
        </div>
        <div class="form-group">
          <label class="form-label">Heure fin <span class="req">*</span></label>
          <input type="time" id="emp-fin" class="form-control">
          <div class="form-error" id="err-emp-fin"></div>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Classe <span class="req">*</span></label>
        <select id="emp-classe" class="form-control"><option value="">— Choisir —</option></select>
        <div class="form-error" id="err-emp-classe"></div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Matière <span class="req">*</span></label>
          <select id="emp-matiere" class="form-control"><option value="">— Choisir —</option></select>
          <div class="form-error" id="err-emp-matiere"></div>
        </div>
        <div class="form-group">
          <label class="form-label">Enseignant <span class="req">*</span></label>
          <select id="emp-enseignant" class="form-control"><option value="">— Choisir —</option></select>
          <div class="form-error" id="err-emp-enseignant"></div>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Salle <span class="req">*</span></label>
        <select id="emp-salle" class="form-control"><option value="">— Choisir —</option></select>
        <div class="form-error" id="err-emp-salle"></div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-close="modal-emp">Annuler</button>
      <button class="btn btn-primary" id="btn-save-emp">
        <span id="save-emp-text">Enregistrer</span>
        <span class="spinner" id="save-emp-spinner" style="display:none"></span>
      </button>
    </div>
  </div>
</div>

<div class="toast-container" id="toast-container"></div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/assets/js/ajax/auth.js"></script>
<script src="/assets/js/ajax/emplois_temps.js"></script>
</body>
</html>

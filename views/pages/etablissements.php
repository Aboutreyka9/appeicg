<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Établissements — <?= htmlspecialchars($_ENV['APP_NAME'] ?? 'EICG') ?></title>
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>

<div class="layout">

  <?php require __DIR__ . '/partials/sidebar.php'; ?>

  <div class="main">

    <header class="topbar">
      <span class="topbar-title">Établissements</span>
      <div class="topbar-actions">
        <button class="btn btn-primary btn-sm" id="btn-add-etab">
          <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
          Ajouter
        </button>
      </div>
    </header>

    <main class="content">
      <div class="card">
        <div class="card-header">
          <span class="card-title">Liste des établissements</span>
          <span class="text-sm text-muted" id="etab-count"></span>
        </div>
        <div class="table-responsive">
          <table class="table" id="table-etab">
            <thead>
              <tr>
                <th>Établissement</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Adresse</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="tbody-etab">
              <tr><td colspan="6" style="text-align:center; padding:40px; color:var(--text-muted);">Chargement…</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>

<!-- Modal Ajouter / Modifier -->
<div class="modal-backdrop" id="modal-etab">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title" id="modal-etab-title">Ajouter un établissement</span>
      <button class="modal-close" id="btn-close-modal">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="etab-code">

      <div class="form-group">
        <label class="form-label" for="etab-libelle">Nom de l'établissement <span class="req">*</span></label>
        <input type="text" id="etab-libelle" class="form-control" placeholder="Ex : Institut Supérieur de Technologie">
        <div class="form-error" id="err-libelle"></div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="etab-tel1">Téléphone principal</label>
          <input type="text" id="etab-tel1" class="form-control" placeholder="+225 07 00 00 00 00">
        </div>
        <div class="form-group">
          <label class="form-label" for="etab-tel2">Téléphone secondaire</label>
          <input type="text" id="etab-tel2" class="form-control" placeholder="+225 05 00 00 00 00">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="etab-email">Email</label>
        <input type="email" id="etab-email" class="form-control" placeholder="contact@etablissement.ci">
        <div class="form-error" id="err-email"></div>
      </div>

      <div class="form-group">
        <label class="form-label" for="etab-adresse">Adresse</label>
        <input type="text" id="etab-adresse" class="form-control" placeholder="Abidjan, Cocody">
      </div>

      <div class="form-group">
        <label class="form-label" for="etab-slogan">Slogan</label>
        <input type="text" id="etab-slogan" class="form-control" placeholder="Ex : L'excellence au service du savoir">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" id="btn-cancel-modal">Annuler</button>
      <button class="btn btn-primary" id="btn-save-etab">
        <span id="save-text">Enregistrer</span>
        <span class="spinner" id="save-spinner" style="display:none;"></span>
      </button>
    </div>
  </div>
</div>

<!-- Toast -->
<div class="toast-container" id="toast-container"></div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/assets/js/ajax/auth.js"></script>
<script src="/assets/js/ajax/etablissements.js"></script>
</body>
</html>

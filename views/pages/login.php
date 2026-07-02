<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion — <?= htmlspecialchars($_ENV['APP_NAME'] ?? 'EICG') ?></title>
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>

<div class="login-page">

  <!-- Côté gauche : branding -->
  <div class="login-left">
    <div class="login-branding">
      <div class="school-name">
        Gestion<br><span>Scolaire</span><br>EICG
      </div>
      <div class="divider"></div>
      <p>Plateforme unifiée de gestion des établissements : étudiants, notes, emplois du temps et finances — en un seul endroit.</p>
    </div>
  </div>

  <!-- Côté droit : formulaire -->
  <div class="login-right">
    <div class="login-box">
      <h2>Connexion</h2>
      <p class="subtitle">Accédez à votre espace de gestion</p>

      <div id="login-alert" class="alert alert-danger" style="display:none;"></div>

      <div class="form-group">
        <label class="form-label" for="email">Adresse email <span class="req">*</span></label>
        <input type="email" id="email" name="email" class="form-control" placeholder="admin@eicg.ci" autocomplete="email">
        <div class="form-error" id="err-email"></div>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Mot de passe <span class="req">*</span></label>
        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" autocomplete="current-password">
        <div class="form-error" id="err-password"></div>
      </div>

      <button type="button" id="btn-login" class="btn btn-primary w-100 mt-3" style="justify-content:center;">
        Se connecter
      </button>

      <p class="text-sm text-muted mt-4" style="text-align:center;">
        © <?= date('Y') ?> EICG — Gestion Scolaire
      </p>
    </div>
  </div>

</div>

<!-- Toast container -->
<div class="toast-container" id="toast-container"></div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/assets/js/ajax/auth.js"></script>
</body>
</html>

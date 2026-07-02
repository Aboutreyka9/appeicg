<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tableau de bord — <?= htmlspecialchars($_ENV['APP_NAME'] ?? 'EICG') ?></title>
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>

<div class="layout">

  <?php require __DIR__ . '/partials/sidebar.php'; ?>

  <div class="main">

    <!-- Topbar -->
    <header class="topbar">
      <span class="topbar-title">Tableau de bord</span>
      <div class="topbar-actions">
        <span class="text-sm text-muted" id="topbar-date"></span>
      </div>
    </header>

    <!-- Contenu -->
    <main class="content">

      <!-- Stats -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon blue">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          </div>
          <div>
            <div class="stat-value" id="stat-etudiants">—</div>
            <div class="stat-label">Étudiants inscrits</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon gold">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          </div>
          <div>
            <div class="stat-value" id="stat-enseignants">—</div>
            <div class="stat-label">Enseignants</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
          </div>
          <div>
            <div class="stat-value" id="stat-classes">—</div>
            <div class="stat-label">Classes actives</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon red">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          </div>
          <div>
            <div class="stat-value" id="stat-paiements">—</div>
            <div class="stat-label">Paiements ce mois</div>
          </div>
        </div>
      </div>

      <!-- Contenu principal -->
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
        <div class="card">
          <div class="card-header">
            <span class="card-title">Accès rapides</span>
          </div>
          <div class="card-body">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
              <a href="/etudiants" class="btn btn-outline" style="justify-content:center;">👨‍🎓 Étudiants</a>
              <a href="/inscriptions" class="btn btn-outline" style="justify-content:center;">📋 Inscriptions</a>
              <a href="/notes" class="btn btn-outline" style="justify-content:center;">📝 Notes</a>
              <a href="/paiements" class="btn btn-outline" style="justify-content:center;">💳 Paiements</a>
              <a href="/emplois-du-temps" class="btn btn-outline" style="justify-content:center;">🗓 Emplois du temps</a>
              <a href="/etablissements" class="btn btn-outline" style="justify-content:center;">🏫 Établissements</a>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <span class="card-title">Modules disponibles</span>
          </div>
          <div class="card-body">
            <div style="display:flex; flex-direction:column; gap:10px;">
              <?php
              $modules = [
                  ['✅ Module 0', 'Fondations (Auth, Établissements)', 'success'],
                  ['✅ Module 1', 'Années & Semestres', 'success'],
                  ['✅ Module 2', 'Structure académique (Cycles, Classes, Salles)', 'success'],
                  ['✅ Module 3', 'Matières & Enseignants', 'success'],
                  ['✅ Module 4', 'Étudiants & Dossiers', 'success'],
                  ['✅ Module 5', 'Inscriptions & Accessoires', 'success'],
                  ['✅ Module 6', 'Scolarité & Paiements', 'success'],
                  ['✅ Module 7', 'Emploi du temps', 'success'],
                  ['⏳ Module 8', 'Notes & Bulletins', 'warning'],
                  ['⏳ Module 9', 'Documents & Communication', 'warning'],
              ];
              foreach ($modules as [$label, $desc, $type]):
              ?>
              <div style="display:flex; align-items:center; gap:10px;">
                <span class="badge badge-<?= $type ?>" style="min-width:90px; justify-content:center;"><?= $label ?></span>
                <span class="text-sm text-muted"><?= $desc ?></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>

    </main>
  </div>
</div>

<!-- Toast -->
<div class="toast-container" id="toast-container"></div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/assets/js/ajax/auth.js"></script>
<script>
  // Date dans le topbar
  const now = new Date();
  document.getElementById('topbar-date').textContent = now.toLocaleDateString('fr-FR', {
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
  });
</script>
</body>
</html>

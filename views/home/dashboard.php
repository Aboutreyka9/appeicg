
<?php
// Déterminer la page active
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
function isActived(string $path, string $current): string {
    return $current === $path ? 'active' : '';
}
?>
<aside class="sidebar" id="sidebar">

  <div class="sidebar-brand">
    <span class="brand-name">EICG</span>
    <span class="brand-sub">Gestion Scolaire</span>
  </div>

  <nav class="sidebar-nav">

    <span class="nav-section-label">Principal</span>
    <a href="<?= url('/dashboard') ?>
    " class="nav-item <?= isActived('/dashboard', $currentPath) ?>">
      <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
      Tableau de bord
    </a>

    <span class="nav-section-label">Configuration</span>
    <a href="<?= url('/etablissements') ?>" class="nav-item <?= isActived('/etablissements', $currentPath) ?>">
      <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
      Établissements
    </a>
    <a href="<?= url('annees') ?>" class="nav-item <?= isActived('/annees', $currentPath) ?>">
      <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
      Années &amp; Semestres
    </a>

    <span class="nav-section-label">Académique</span>
    <a href="<?= url('cycles') ?>" class="nav-item <?= isActived('/cycles', $currentPath) ?>">
      <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
      Cycles & Filières
    </a>
    <a href="<?= url('classes') ?>" class="nav-item <?= isActived('/classes', $currentPath) ?>">
      <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
      Classes & Salles
    </a>
    <a href="<?= url('matieres') ?>" class="nav-item <?= isActived('/matieres', $currentPath) ?>">
      <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
      Matières
    </a>
    <a href="<?= url('enseignants') ?>" class="nav-item <?= isActived('/enseignants', $currentPath) ?>">
      <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
      Enseignants
    </a>

    <span class="nav-section-label">Étudiants</span>
    <a href="<?= url('etudiants') ?>" class="nav-item <?= isActived('/etudiants', $currentPath) ?>">
      <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
      Étudiants
    </a>
    <a href="<?= url('inscriptions') ?>" class="nav-item <?= isActived('/inscriptions', $currentPath) ?>">
      <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
      Inscriptions
    </a>

    <span class="nav-section-label">Pédagogie</span>
    <a href="<?= url('emplois-du-temps') ?>" class="nav-item <?= isActived('/emplois-du-temps', $currentPath) ?>">
      <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      Emplois du temps
    </a>
    <a href="<?= url('notes') ?>" class="nav-item <?= isActived('/notes', $currentPath) ?>">
      <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
      Notes & Bulletins
    </a>

    <span class="nav-section-label">Finance</span>
    <a href="<?= url('paiements') ?>" class="nav-item <?= isActived('/paiements', $currentPath) ?>">
      <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
      Paiements
    </a>

      <span class="nav-section-label">Communication</span>
    <a href="<?= url('communication') ?>" class="nav-item <?= isActived('/communication', $currentPath) ?>">
      <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
      Documents & Communication
    </a>

  </nav>

  <div class="sidebar-footer">
    <div class="user-card">
      <div class="user-avatar" id="user-avatar-initials">--</div>
      <div class="user-info">
        <div class="user-name" id="sidebar-user-name">Chargement…</div>
        <div class="user-role">Administrateur</div>
      </div>
      <button class="btn btn-icon" id="btn-logout" title="Déconnexion" style="color:rgba(255,255,255,.5);">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
      </button>
    </div>
  </div>

</aside>
  

<!-- SIDE -->

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
              <a href="<?= url('etudiants') ?>" class="btn btn-outline" style="justify-content:center;">👨‍🎓 Étudiants</a>
              <a href="<?= url('inscriptions') ?>" class="btn btn-outline" style="justify-content:center;">📋 Inscriptions</a>
              <a href="<?= url('notes') ?>" class="btn btn-outline" style="justify-content:center;">📝 Notes</a>
              <a href="<?= url('paiements') ?>" class="btn btn-outline" style="justify-content:center;">💳 Paiements</a>
              <a href="<?= url('emplois-du-temps') ?>" class="btn btn-outline" style="justify-content:center;">🗓 Emplois du temps</a>
              <a href="<?= url('etablissements') ?>" class="btn btn-outline" style="justify-content:center;">🏫 Établissements</a>
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


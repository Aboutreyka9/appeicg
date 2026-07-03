
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

<div class="toast-container" id="toast-container"></div>

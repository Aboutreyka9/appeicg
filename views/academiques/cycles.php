  <style>
    .tree-panel { background: var(--surface); border-top: 2px solid var(--accent); display: none; }
    .tree-panel.open { display: table-row; }
    .tree-inner { padding: 16px 24px; }
    .sub-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
    .sub-header h4 { font-size:.875rem; color:var(--primary); margin:0; }
    .sub-table { width:100%; border-collapse:collapse; font-size:.83rem; }
    .sub-table th { text-align:left; padding:7px 12px; color:var(--text-muted); font-size:.7rem; font-weight:600; text-transform:uppercase; border-bottom:1px solid var(--border); }
    .sub-table td { padding:9px 12px; border-bottom:1px solid var(--border); vertical-align:middle; }
    .sub-table tr:last-child td { border-bottom:none; }
    .expandable { cursor:pointer; }
    .expand-icon { transition:transform .2s; display:inline-block; color:var(--text-muted); font-size:.7rem; }
    .expandable.expanded .expand-icon { transform:rotate(90deg); }
    /* Niveaux sous filières */
    .niv-panel { background:#fff; border-top:1px solid var(--accent); display:none; }
    .niv-panel.open { display:table-row; }
    .niv-inner { padding:12px 16px; }
    .niv-table { width:100%; border-collapse:collapse; font-size:.8rem; }
    .niv-table th { text-align:left; padding:6px 10px; color:var(--text-muted); font-size:.68rem; font-weight:600; text-transform:uppercase; border-bottom:1px solid var(--border); }
    .niv-table td { padding:8px 10px; border-bottom:1px solid var(--border); vertical-align:middle; }
    .niv-table tr:last-child td { border-bottom:none; }
    .tabs { display:flex; gap:4px; margin-bottom:20px; border-bottom:2px solid var(--border); }
    .tab-btn { padding:10px 20px; font-size:.875rem; font-weight:600; color:var(--text-muted); cursor:pointer; border:none; background:none; border-bottom:2px solid transparent; margin-bottom:-2px; transition:all .2s; }
    .tab-btn.active { color:var(--primary); border-bottom-color:var(--primary); }
    .tab-content { display:none; }
    .tab-content.active { display:block; }
  </style>
  
  <div class="main">
    <header class="topbar">
      <span class="topbar-title">Structure académique</span>
      <div class="topbar-actions" id="topbar-actions">
        <button class="btn btn-primary btn-sm" id="btn-add-cycle">+ Nouveau cycle</button>
      </div>
    </header>
    <main class="content">

      <!-- Onglets -->
      <div class="tabs">
        <button class="tab-btn active" data-tab="tab-cycles">Cycles & Filières & Niveaux</button>
        <button class="tab-btn" data-tab="tab-salles">Salles</button>
      </div>

      <!-- Tab Cycles -->
      <div class="tab-content active" id="tab-cycles">
        <div class="card">
          <div class="card-header">
            <span class="card-title">Cycles</span>
            <span class="text-sm text-muted" id="cycle-count"></span>
          </div>
          <div class="table-responsive">
            <table class="table">
              <thead><tr><th style="width:28px"></th><th>Cycle</th><th>Filières</th><th>Statut</th><th>Actions</th></tr></thead>
              <tbody id="tbody-cycles">
                <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--text-muted)">Chargement…</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Tab Salles -->
      <div class="tab-content" id="tab-salles">
        <div class="card">
          <div class="card-header">
            <span class="card-title">Salles</span>
            <button class="btn btn-primary btn-sm" id="btn-add-salle">+ Nouvelle salle</button>
          </div>
          <div class="table-responsive">
            <table class="table">
              <thead><tr><th>Libellé</th><th>Code</th><th>Statut</th><th>Actions</th></tr></thead>
              <tbody id="tbody-salles">
                <tr><td colspan="4" style="text-align:center;padding:40px;color:var(--text-muted)">Chargement…</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </main>
  </div>
</div>

<div class="toast-container" id="toast-container"></div>


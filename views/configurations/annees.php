
<style>
    .annee-row { cursor: pointer; transition: background .15s; }
    .annee-row:hover td { background: var(--surface) !important; }
    .semestres-panel {
      display: none;
      background: var(--surface);
      border-top: 2px solid var(--accent);
    }
    .semestres-panel.open { display: table-row; }
    .semestres-inner { padding: 20px 24px; }
    .semestres-header {
      display: flex; align-items: center; justify-content: space-between;
      margin-bottom: 14px;
    }
    .semestres-header h4 { font-size: .9rem; color: var(--primary); margin: 0; }
    .sem-table { width: 100%; border-collapse: collapse; font-size: .83rem; }
    .sem-table th {
      text-align: left; padding: 8px 12px;
      color: var(--text-muted); font-size: .72rem;
      font-weight: 600; text-transform: uppercase;
      border-bottom: 1px solid var(--border);
    }
    .sem-table td { padding: 9px 12px; border-bottom: 1px solid var(--border); vertical-align: middle; }
    .sem-table tbody tr:last-child td { border-bottom: none; }
    .expand-icon { transition: transform .2s; display: inline-block; }
    .annee-row.expanded .expand-icon { transform: rotate(90deg); }
  </style>
  
  <div class="main">

    <header class="topbar">
      <span class="topbar-title">Années scolaires</span>
      <div class="topbar-actions">
        <button class="btn btn-primary btn-sm" id="btn-add-annee">
          <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
          Nouvelle année
        </button>
      </div>
    </header>

    <main class="content">
      <div class="card">
        <div class="card-header">
          <span class="card-title">Années scolaires</span>
          <span class="text-sm text-muted" id="annee-count"></span>
        </div>
        <div class="table-responsive">
          <table class="table" id="table-annees">
            <thead>
              <tr>
                <th style="width:32px;"></th>
                <th>Libellé</th>
                <th>Date début</th>
                <th>Date fin</th>
                <th>Semestres</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="tbody-annees">
              <tr><td colspan="7" style="text-align:center; padding:40px; color:var(--text-muted);">Chargement…</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>


<!-- Toast -->
<div class="toast-container" id="toast-container"></div>


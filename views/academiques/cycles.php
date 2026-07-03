
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

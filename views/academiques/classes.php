
  <div class="main">
    <header class="topbar">
      <span class="topbar-title">Classes</span>
      <div class="topbar-actions">
        <button class="btn btn-primary btn-sm" id="btn-add-classe">+ Nouvelle classe</button>
      </div>
    </header>
    <main class="content">

      <!-- Filtres -->
      <div class="card" style="margin-bottom:20px;">
        <div class="card-body" style="padding:16px 24px;">
          <div style="display:flex; gap:16px; align-items:flex-end; flex-wrap:wrap;">
            <div class="form-group" style="margin:0; flex:1; min-width:180px;">
              <label class="form-label">Filtrer par année</label>
              <select id="filter-annee" class="form-control">
                <option value="">Toutes les années</option>
              </select>
            </div>
            <div class="form-group" style="margin:0; flex:1; min-width:180px;">
              <label class="form-label">Filtrer par filière</label>
              <select id="filter-filiere" class="form-control">
                <option value="">Toutes les filières</option>
              </select>
            </div>
            <button class="btn btn-outline btn-sm" id="btn-filter">Filtrer</button>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <span class="card-title">Liste des classes</span>
          <span class="text-sm text-muted" id="classe-count"></span>
        </div>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Classe</th>
                <th>Filière</th>
                <th>Niveau</th>
                <th>Année</th>
                <th>Capacité</th>
                <th>Étudiants</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="tbody-classes">
              <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted)">Chargement…</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>

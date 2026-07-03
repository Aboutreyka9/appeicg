
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

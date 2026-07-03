

  <div class="main">
    <header class="topbar">
      <span class="topbar-title">Enseignants</span>
      <div class="topbar-actions">
        <button class="btn btn-primary btn-sm" id="btn-add-ens">+ Nouvel enseignant</button>
      </div>
    </header>
    <main class="content">
      <div class="card">
        <div class="card-header">
          <span class="card-title">Liste des enseignants</span>
          <span class="text-sm text-muted" id="ens-count"></span>
        </div>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th style="width:28px"></th>
                <th>Enseignant</th>
                <th>Matricule</th>
                <th>Téléphone</th>
                <th>Email</th>
                <th>Matières</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="tbody-ens">
              <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted)">Chargement…</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>

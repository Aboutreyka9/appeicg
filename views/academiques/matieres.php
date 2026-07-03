
  <div class="main">
    <header class="topbar">
      <span class="topbar-title">Matières</span>
      <div class="topbar-actions">
        <button class="btn btn-primary btn-sm" id="btn-add-matiere">+ Nouvelle matière</button>
      </div>
    </header>
    <main class="content">
      <div class="card">
        <div class="card-header">
          <span class="card-title">Liste des matières</span>
          <span class="text-sm text-muted" id="mat-count"></span>
        </div>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr><th>Libellé</th><th>Code</th><th>Statut</th><th>Actions</th></tr>
            </thead>
            <tbody id="tbody-matieres">
              <tr><td colspan="4" style="text-align:center;padding:40px;color:var(--text-muted)">Chargement…</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>


  <div class="main">
    <header class="topbar">
      <span class="topbar-title">Inscriptions</span>
      <div class="topbar-actions">
        <button class="btn btn-primary btn-sm" id="btn-add-ins">+ Nouvelle inscription</button>
      </div>
    </header>
    <main class="content">

      <div class="tabs">
        <button class="tab-btn active" data-tab="tab-inscriptions">Inscriptions</button>
        <button class="tab-btn" data-tab="tab-accessoires">Accessoires</button>
      </div>

      <!-- Tab Inscriptions -->
      <div class="tab-content active" id="tab-inscriptions">

        <!-- Filtres -->
        <div class="card" style="margin-bottom:20px">
          <div class="card-body" style="padding:16px 24px">
            <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
              <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <label class="form-label">Année scolaire</label>
                <select id="f-annee" class="form-control"><option value="">Toutes</option></select>
              </div>
              <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <label class="form-label">Classe</label>
                <select id="f-classe" class="form-control"><option value="">Toutes</option></select>
              </div>
              <div class="form-group" style="margin:0;flex:1;min-width:140px">
                <label class="form-label">Statut</label>
                <select id="f-statut" class="form-control">
                  <option value="">Tous</option>
                  <option value="valide">Valide</option>
                  <option value="solde">Soldé</option>
                  <option value="annule">Annulé</option>
                </select>
              </div>
              <div class="form-group" style="margin:0;flex:2;min-width:200px">
                <label class="form-label">Recherche</label>
                <input type="text" id="f-search" class="form-control" placeholder="Nom, prénom ou matricule…">
              </div>
              <button class="btn btn-outline btn-sm" id="btn-filter-ins">Filtrer</button>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <span class="card-title">Liste des inscriptions</span>
            <span class="text-sm text-muted" id="ins-count"></span>
          </div>
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th style="width:28px"></th>
                  <th>Étudiant</th>
                  <th>Matricule</th>
                  <th>Classe</th>
                  <th>Filière</th>
                  <th>Année</th>
                  <th>Montant</th>
                  <th>Statut</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="tbody-ins">
                <tr><td colspan="9" style="text-align:center;padding:40px;color:var(--text-muted)">Chargement…</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Tab Accessoires -->
      <div class="tab-content" id="tab-accessoires">
        <div class="card">
          <div class="card-header">
            <span class="card-title">Accessoires</span>
            <button class="btn btn-primary btn-sm" id="btn-add-acc">+ Nouvel accessoire</button>
          </div>
          <div class="table-responsive">
            <table class="table">
              <thead><tr><th>Libellé</th><th>Code</th><th>Statut</th><th>Actions</th></tr></thead>
              <tbody id="tbody-acc">
                <tr><td colspan="4" style="text-align:center;padding:40px;color:var(--text-muted)">Chargement…</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </main>
  </div>
</div>

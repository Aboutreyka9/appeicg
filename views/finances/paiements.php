
  <style>
    .tabs { display:flex; gap:4px; margin-bottom:20px; border-bottom:2px solid var(--border); }
    .tab-btn { padding:10px 20px; font-size:.875rem; font-weight:600; color:var(--text-muted); cursor:pointer; border:none; background:none; border-bottom:2px solid transparent; margin-bottom:-2px; transition:all .2s; }
    .tab-btn.active { color:var(--primary); border-bottom-color:var(--primary); }
    .tab-content { display:none; }
    .tab-content.active { display:block; }
    .kpi-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:16px; margin-bottom:24px; }
    .kpi-card { background:var(--white); border:1px solid var(--border); border-radius:var(--radius); padding:20px; }
    .kpi-value { font-size:1.5rem; font-weight:700; color:var(--primary); }
    .kpi-label { font-size:.78rem; color:var(--text-muted); margin-top:4px; }
  </style>

  <div class="main">
    <header class="topbar">
      <span class="topbar-title">Paiements & Scolarité</span>
      <div class="topbar-actions">
        <button class="btn btn-primary btn-sm" id="btn-add-pay">+ Enregistrer un paiement</button>
      </div>
    </header>
    <main class="content">

      <div class="tabs">
        <button class="tab-btn active" data-tab="tab-paiements">Paiements</button>
        <button class="tab-btn" data-tab="tab-scolarites">Grille tarifaire</button>
      </div>

      <!-- Tab Paiements -->
      <div class="tab-content active" id="tab-paiements">

        <!-- KPIs -->
        <div class="kpi-grid" id="kpi-grid">
          <div class="kpi-card">
            <div class="kpi-value" id="kpi-total">—</div>
            <div class="kpi-label">Total encaissé (année)</div>
          </div>
          <div class="kpi-card">
            <div class="kpi-value" id="kpi-nb">—</div>
            <div class="kpi-label">Nombre de paiements</div>
          </div>
        </div>

        <!-- Filtres -->
        <div class="card" style="margin-bottom:20px">
          <div class="card-body" style="padding:16px 24px">
            <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
              <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <label class="form-label">Année</label>
                <select id="f-pay-annee" class="form-control"><option value="">Toutes</option></select>
              </div>
              <div class="form-group" style="margin:0;flex:1;min-width:140px">
                <label class="form-label">Type</label>
                <select id="f-pay-type" class="form-control">
                  <option value="">Tous</option>
                  <option value="scolarite">Scolarité</option>
                  <option value="inscription">Inscription</option>
                  <option value="accessoire">Accessoire</option>
                  <option value="autre">Autre</option>
                </select>
              </div>
              <div class="form-group" style="margin:0;flex:1;min-width:140px">
                <label class="form-label">Statut</label>
                <select id="f-pay-statut" class="form-control">
                  <option value="">Tous</option>
                  <option value="confirme">Confirmé</option>
                  <option value="annule">Annulé</option>
                </select>
              </div>
              <div class="form-group" style="margin:0;flex:2;min-width:200px">
                <label class="form-label">Recherche</label>
                <input type="text" id="f-pay-search" class="form-control" placeholder="Nom, matricule, code paiement…">
              </div>
              <button class="btn btn-outline btn-sm" id="btn-filter-pay">Filtrer</button>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <span class="card-title">Historique des paiements</span>
            <span class="text-sm text-muted" id="pay-count"></span>
          </div>
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>Code</th>
                  <th>Étudiant</th>
                  <th>Montant</th>
                  <th>Type</th>
                  <th>Mode</th>
                  <th>Année</th>
                  <th>Date</th>
                  <th>Statut</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="tbody-pay">
                <tr><td colspan="9" style="text-align:center;padding:40px;color:var(--text-muted)">Chargement…</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Tab Scolarités -->
      <div class="tab-content" id="tab-scolarites">
        <div class="card">
          <div class="card-header">
            <span class="card-title">Grille tarifaire</span>
            <div class="d-flex gap-2 align-center">
              <select id="f-sco-annee" class="form-control" style="width:180px">
                <option value="">Toutes les années</option>
              </select>
              <button class="btn btn-primary btn-sm" id="btn-add-sco">+ Nouveau tarif</button>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr><th>Filière</th><th>Niveau</th><th>Année</th><th>Montant</th><th>Actions</th></tr>
              </thead>
              <tbody id="tbody-sco">
                <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--text-muted)">Chargement…</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </main>
  </div>
</div>

<div class="toast-container" id="toast-container"></div>

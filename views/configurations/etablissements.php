




  <div class="main">

    <header class="topbar">
      <span class="topbar-title">Établissements</span>
      <div class="topbar-actions">
        <button class="btn btn-primary btn-sm" id="btn-add-etab">
          <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
          Ajouter
        </button>
      </div>
    </header>

    <main class="content">
      <div class="card">
        <div class="card-header">
          <span class="card-title">Liste des établissements</span>
          <span class="text-sm text-muted" id="etab-count"></span>
        </div>
        <div class="table-responsive">
          <table class="table" id="table-etab">
            <thead>
              <tr>
                <th>Établissement</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Adresse</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="tbody-etab">
              <tr><td colspan="6" style="text-align:center; padding:40px; color:var(--text-muted);">Chargement…</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>



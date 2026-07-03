

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


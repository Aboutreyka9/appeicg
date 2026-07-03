
  <style>
    .ens-avatar { width:36px; height:36px; border-radius:50%; background:var(--primary-light); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:.8rem; flex-shrink:0; }
    .mat-tag { display:inline-flex; align-items:center; gap:4px; background:rgba(27,58,107,.08); color:var(--primary); border-radius:20px; padding:2px 10px; font-size:.72rem; font-weight:600; margin:2px; }
    .mat-tag .remove-mat { cursor:pointer; color:var(--danger); margin-left:2px; font-weight:700; }
    .mat-panel { background:var(--surface); border-top:2px solid var(--accent); display:none; }
    .mat-panel.open { display:table-row; }
    .mat-inner { padding:16px 24px; }
    .expandable { cursor:pointer; }
    .expand-icon { transition:transform .2s; display:inline-block; color:var(--text-muted); font-size:.7rem; }
    .expandable.expanded .expand-icon { transform:rotate(90deg); }
  </style>

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


<div class="toast-container" id="toast-container"></div>

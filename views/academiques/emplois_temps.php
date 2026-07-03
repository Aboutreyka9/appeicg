
  <div class="main">
    <header class="topbar">
      <span class="topbar-title">Emplois du temps</span>
      <div class="topbar-actions">
        <button class="btn btn-primary btn-sm" id="btn-add-emp">+ Ajouter un créneau</button>
      </div>
    </header>
    <main class="content">

      <!-- Filtres -->
      <div class="card" style="margin-bottom:20px">
        <div class="card-body" style="padding:16px 24px">
          <div class="filters-bar">
            <div class="form-group">
              <label class="form-label">Année scolaire</label>
              <select id="f-emp-annee" class="form-control"><option value="">Toutes</option></select>
            </div>
            <div class="form-group">
              <label class="form-label">Vue par classe</label>
              <select id="f-emp-classe" class="form-control"><option value="">Toutes les classes</option></select>
            </div>
            <div class="form-group">
              <label class="form-label">Vue par enseignant</label>
              <select id="f-emp-ens" class="form-control"><option value="">Tous les enseignants</option></select>
            </div>
            <div class="form-group" style="max-width:140px">
              <label class="form-label">Jour</label>
              <select id="f-emp-jour" class="form-control">
                <option value="">Tous</option>
                <option value="lundi">Lundi</option>
                <option value="mardi">Mardi</option>
                <option value="mercredi">Mercredi</option>
                <option value="jeudi">Jeudi</option>
                <option value="vendredi">Vendredi</option>
                <option value="samedi">Samedi</option>
              </select>
            </div>
            <button class="btn btn-outline btn-sm" id="btn-filter-emp" style="align-self:flex-end">Afficher</button>
          </div>
        </div>
      </div>

      <!-- Onglets vue -->
      <div class="view-tabs">
        <button class="vtab active" data-view="grille">Vue grille</button>
        <button class="vtab" data-view="liste">Vue liste</button>
      </div>

      <!-- Vue grille -->
      <div id="view-grille">
        <div class="card">
          <div class="card-header">
            <span class="card-title" id="grille-title">Grille hebdomadaire</span>
            <span class="text-sm text-muted" id="emp-count"></span>
          </div>
          <div style="overflow-x:auto; padding:16px">
            <table class="timetable" id="timetable">
              <thead>
                <tr>
                  <th>Heure</th>
                  <th>Lundi</th>
                  <th>Mardi</th>
                  <th>Mercredi</th>
                  <th>Jeudi</th>
                  <th>Vendredi</th>
                  <th>Samedi</th>
                </tr>
              </thead>
              <tbody id="tbody-grille">
                <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted)">Sélectionnez une classe ou un enseignant pour afficher l'emploi du temps.</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Vue liste -->
      <div id="view-liste" style="display:none">
        <div class="card">
          <div class="card-header">
            <span class="card-title">Liste des créneaux</span>
          </div>
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>Jour</th>
                  <th>Horaire</th>
                  <th>Matière</th>
                  <th>Enseignant</th>
                  <th>Classe</th>
                  <th>Salle</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="tbody-liste">
                <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted)">Chargement…</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </main>
  </div>
</div>

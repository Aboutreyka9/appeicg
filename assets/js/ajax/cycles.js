/**
 * cycles.js — Cycles, Filières, Niveaux, Salles (AJAX)
 * EICG Gestion Scolaire
 */

$(document).ready(function () {

  // ═══════════════════════════════════════════════════════════
  // ONGLETS
  // ═══════════════════════════════════════════════════════════
  $('.tab-btn').on('click', function () {
    const target = $(this).data('tab');
    $('.tab-btn').removeClass('active');
    $('.tab-content').removeClass('active');
    $(this).addClass('active');
    $('#' + target).addClass('active');

    if (target === 'tab-salles') loadSalles();
  });

  // ═══════════════════════════════════════════════════════════
  // CYCLES
  // ═══════════════════════════════════════════════════════════
  function loadCycles() {
    $.get('/api/cycles/liste', function (res) {
      const cycles = res.data || [];
      $('#cycle-count').text(`${cycles.length} cycle${cycles.length > 1 ? 's' : ''}`);
      const $tbody = $('#tbody-cycles');
      $tbody.empty();

      if (!cycles.length) {
        $tbody.html(`<tr><td colspan="5"><div class="empty-state"><p>Aucun cycle enregistré.</p></div></td></tr>`);
        return;
      }

      cycles.forEach(function (c) {
        const rowId = `fil-row-${c.code_cycle}`;
        const badge = c.statut_cycle === 'actif' ? 'badge-success' : 'badge-danger';
        $tbody.append(`
          <tr class="expandable" data-target="${rowId}" data-code="${c.code_cycle}">
            <td><span class="expand-icon">▶</span></td>
            <td class="fw-600">${esc(c.libelle_cycle)}</td>
            <td><span class="badge badge-info fil-count-${c.code_cycle}">— fil.</span></td>
            <td><span class="badge ${badge}">${c.statut_cycle === 'actif' ? 'Actif' : 'Inactif'}</span></td>
            <td>
              <div class="d-flex gap-2" onclick="event.stopPropagation()">
                <button class="btn btn-outline btn-sm btn-edit-cycle" data-code="${c.code_cycle}" data-libelle="${escA(c.libelle_cycle)}">Modifier</button>
                <button class="btn btn-sm ${c.statut_cycle === 'actif' ? 'btn-danger' : 'btn-accent'} btn-toggle-cycle"
                  data-code="${c.code_cycle}" data-statut="${c.statut_cycle === 'actif' ? 'inactif' : 'actif'}">
                  ${c.statut_cycle === 'actif' ? 'Désactiver' : 'Activer'}
                </button>
              </div>
            </td>
          </tr>
          <tr class="tree-panel" id="${rowId}">
            <td colspan="5">
              <div class="tree-inner">
                <div class="sub-header">
                  <h4>Filières du cycle <strong>${esc(c.libelle_cycle)}</strong></h4>
                  <button class="btn btn-accent btn-sm btn-add-fil" data-cycle-code="${c.code_cycle}" data-cycle-libelle="${escA(c.libelle_cycle)}">+ Filière</button>
                </div>
                <div id="fil-list-${c.code_cycle}"><p class="text-sm text-muted">Cliquez pour charger…</p></div>
              </div>
            </td>
          </tr>
        `);
      });
    });
  }
  loadCycles();

  // Expand cycle → filières
  $(document).on('click', '.expandable', function () {
    const $row   = $(this);
    const target = $row.data('target');
    const code   = $row.data('code');
    const $panel = $('#' + target);

    if ($panel.hasClass('open')) {
      $panel.removeClass('open'); $row.removeClass('expanded'); return;
    }
    $('.tree-panel.open').removeClass('open');
    $('.expandable.expanded').removeClass('expanded');
    $row.addClass('expanded'); $panel.addClass('open');
    loadFilieres(code);
  });

  // CRUD Cycle
  $('#btn-add-cycle').on('click', function () {
    openModal('modal-cycle', 'cycle', false, { 'cycle-code': '', 'cycle-libelle': '' });
    $('#modal-cycle-title').text('Nouveau cycle');
  });

  $(document).on('click', '.btn-edit-cycle', function (e) {
    e.stopPropagation();
    openModal('modal-cycle', 'cycle', true, { 'cycle-code': $(this).data('code'), 'cycle-libelle': $(this).data('libelle') });
    $('#modal-cycle-title').text('Modifier le cycle');
  });

  $('#btn-save-cycle').on('click', function () {
    const libelle = $('#cycle-libelle').val().trim();
    const code    = $('#cycle-code').val();
    clearErr('cycle-libelle');
    if (!libelle) { showErr('cycle-libelle', 'Le libellé est obligatoire.'); return; }
    setSaving('cycle', true);
    const isEdit = !!code;
    const data   = { libelle_cycle: libelle };
    if (isEdit) data.code_cycle = code;
    $.ajax({
      url: isEdit ? '/api/cycles/modifier' : '/api/cycles/ajouter', method: 'POST', data,
      success: function (res) {
        setSaving('cycle', false);
        if (res.success) { showToast(res.message, 'success'); closeModal('modal-cycle'); loadCycles(); }
        else showToast(res.message, 'error');
      },
      error: function (xhr) { setSaving('cycle', false); apiError(xhr, { libelle_cycle: 'cycle-libelle' }); }
    });
  });

  $(document).on('click', '.btn-toggle-cycle', function (e) {
    e.stopPropagation();
    const $btn = $(this); const code = $btn.data('code'); const statut = $btn.data('statut');
    if (!confirm(`${statut === 'actif' ? 'Activer' : 'Désactiver'} ce cycle ?`)) return;
    $btn.prop('disabled', true);
    $.post('/api/cycles/statut', { code_cycle: code, statut_cycle: statut }, function (res) {
      if (res.success) { showToast(res.message, 'success'); loadCycles(); }
      else { showToast(res.message, 'error'); $btn.prop('disabled', false); }
    });
  });

  // ═══════════════════════════════════════════════════════════
  // FILIÈRES
  // ═══════════════════════════════════════════════════════════
  function loadFilieres(cycleCode) {
    const $c = $(`#fil-list-${cycleCode}`);
    $c.html('<p class="text-sm text-muted">Chargement…</p>');
    $.get(`/api/filieres/liste?cycle_code=${cycleCode}`, function (res) {
      const fils = res.data || [];
      $(`.fil-count-${cycleCode}`).text(`${fils.length} fil.`);
      if (!fils.length) { $c.html('<p class="text-sm text-muted">Aucune filière. Ajoutez-en une.</p>'); return; }

      let html = `<table class="sub-table"><thead><tr><th style="width:24px"></th><th>Filière</th><th>Description</th><th>Niveaux</th><th>Statut</th><th>Actions</th></tr></thead><tbody>`;
      fils.forEach(function (f) {
        const fRowId = `niv-row-${f.code_filiere}`;
        const badge  = f.statut_filiere === 'actif' ? 'badge-success' : 'badge-danger';
        html += `
          <tr class="expandable" data-target="${fRowId}" data-code="${f.code_filiere}" data-type="filiere">
            <td><span class="expand-icon">▶</span></td>
            <td class="fw-600">${esc(f.libelle_filiere)}</td>
            <td class="text-muted">${f.description_filiere ? esc(f.description_filiere) : '—'}</td>
            <td><span class="badge badge-info niv-count-${f.code_filiere}">— niv.</span></td>
            <td><span class="badge ${badge}">${f.statut_filiere === 'actif' ? 'Active' : 'Inactive'}</span></td>
            <td>
              <div class="d-flex gap-2" onclick="event.stopPropagation()">
                <button class="btn btn-outline btn-sm btn-edit-fil"
                  data-code="${f.code_filiere}" data-libelle="${escA(f.libelle_filiere)}"
                  data-description="${escA(f.description_filiere || '')}" data-cycle-code="${cycleCode}"
                  data-cycle-libelle="${escA($('.btn-add-fil[data-cycle-code=&quot;' + cycleCode + '&quot;]').data('cycle-libelle') || '')}">
                  Modifier
                </button>
                <button class="btn btn-sm ${f.statut_filiere === 'actif' ? 'btn-danger' : 'btn-accent'} btn-toggle-fil"
                  data-code="${f.code_filiere}" data-statut="${f.statut_filiere === 'actif' ? 'inactif' : 'actif'}" data-cycle="${cycleCode}">
                  ${f.statut_filiere === 'actif' ? 'Désactiver' : 'Activer'}
                </button>
              </div>
            </td>
          </tr>
          <tr class="niv-panel" id="${fRowId}">
            <td colspan="6">
              <div class="niv-inner">
                <div class="sub-header">
                  <h4>Niveaux de <strong>${esc(f.libelle_filiere)}</strong></h4>
                  <button class="btn btn-primary btn-sm btn-add-niv" data-fil-code="${f.code_filiere}" data-fil-libelle="${escA(f.libelle_filiere)}">+ Niveau</button>
                </div>
                <div id="niv-list-${f.code_filiere}"><p class="text-sm text-muted">Cliquez pour charger…</p></div>
              </div>
            </td>
          </tr>
        `;
      });
      html += '</tbody></table>';
      $c.html(html);
    });
  }

  // Expand filière → niveaux (délégué sur sous-tables)
  $(document).on('click', '.sub-table .expandable', function (e) {
    e.stopPropagation();
    const $row = $(this); const target = $row.data('target'); const code = $row.data('code');
    const $panel = $('#' + target);
    if ($panel.hasClass('open')) { $panel.removeClass('open'); $row.removeClass('expanded'); return; }
    $('.niv-panel.open').removeClass('open');
    $('.sub-table .expandable.expanded').removeClass('expanded');
    $row.addClass('expanded'); $panel.addClass('open');
    loadNiveaux(code);
  });

  // CRUD Filière
  $(document).on('click', '.btn-add-fil', function (e) {
    e.stopPropagation();
    openModal('modal-filiere', 'fil', false, { 'fil-code': '', 'fil-libelle': '', 'fil-description': '' });
    $('#fil-cycle-code').val($(this).data('cycle-code'));
    $('#fil-cycle-display').val($(this).data('cycle-libelle'));
    $('#modal-fil-title').text('Nouvelle filière');
  });

  $(document).on('click', '.btn-edit-fil', function (e) {
    e.stopPropagation();
    openModal('modal-filiere', 'fil', true, {
      'fil-code': $(this).data('code'), 'fil-libelle': $(this).data('libelle'),
      'fil-description': $(this).data('description')
    });
    $('#fil-cycle-code').val($(this).data('cycle-code'));
    $('#fil-cycle-display').val($(this).data('cycle-libelle'));
    $('#modal-fil-title').text('Modifier la filière');
  });

  $('#btn-save-fil').on('click', function () {
    const code    = $('#fil-code').val();
    const libelle = $('#fil-libelle').val().trim();
    clearErr('fil-libelle');
    if (!libelle) { showErr('fil-libelle', 'Le libellé est obligatoire.'); return; }
    setSaving('fil', true);
    const data = { libelle_filiere: libelle, cycle_code: $('#fil-cycle-code').val(), description_filiere: $('#fil-description').val().trim() };
    if (code) data.code_filiere = code;
    $.ajax({
      url: code ? '/api/filieres/modifier' : '/api/filieres/ajouter', method: 'POST', data,
      success: function (res) {
        setSaving('fil', false);
        if (res.success) { showToast(res.message, 'success'); closeModal('modal-filiere'); loadFilieres($('#fil-cycle-code').val()); }
        else showToast(res.message, 'error');
      },
      error: function (xhr) { setSaving('fil', false); apiError(xhr, { libelle_filiere: 'fil-libelle' }); }
    });
  });

  $(document).on('click', '.btn-toggle-fil', function (e) {
    e.stopPropagation();
    const $btn = $(this); const code = $btn.data('code'); const statut = $btn.data('statut'); const cycle = $btn.data('cycle');
    if (!confirm(`${statut === 'actif' ? 'Activer' : 'Désactiver'} cette filière ?`)) return;
    $btn.prop('disabled', true);
    $.post('/api/filieres/statut', { code_filiere: code, statut_filiere: statut }, function (res) {
      if (res.success) { showToast(res.message, 'success'); loadFilieres(cycle); }
      else { showToast(res.message, 'error'); $btn.prop('disabled', false); }
    });
  });

  // ═══════════════════════════════════════════════════════════
  // NIVEAUX
  // ═══════════════════════════════════════════════════════════
  function loadNiveaux(filiereCode) {
    const $c = $(`#niv-list-${filiereCode}`);
    $c.html('<p class="text-sm text-muted">Chargement…</p>');
    $.get(`/api/niveaux/liste?filiere_code=${filiereCode}`, function (res) {
      const nivs = res.data || [];
      $(`.niv-count-${filiereCode}`).text(`${nivs.length} niv.`);
      if (!nivs.length) { $c.html('<p class="text-sm text-muted">Aucun niveau. Ajoutez-en un.</p>'); return; }
      let html = `<table class="niv-table"><thead><tr><th>Niveau</th><th>Statut</th><th>Actions</th></tr></thead><tbody>`;
      nivs.forEach(function (n) {
        const badge = n.statut_niveau === 'actif' ? 'badge-success' : 'badge-danger';
        html += `
          <tr>
            <td class="fw-600">${esc(n.libelle_niveau)}</td>
            <td><span class="badge ${badge}">${n.statut_niveau === 'actif' ? 'Actif' : 'Inactif'}</span></td>
            <td>
              <div class="d-flex gap-2">
                <button class="btn btn-outline btn-sm btn-edit-niv"
                  data-code="${n.code_niveau}" data-libelle="${escA(n.libelle_niveau)}"
                  data-fil-code="${filiereCode}" data-fil-libelle="${escA(n.libelle_filiere || '')}">Modifier</button>
                <button class="btn btn-sm ${n.statut_niveau === 'actif' ? 'btn-danger' : 'btn-accent'} btn-toggle-niv"
                  data-code="${n.code_niveau}" data-statut="${n.statut_niveau === 'actif' ? 'inactif' : 'actif'}" data-fil="${filiereCode}">
                  ${n.statut_niveau === 'actif' ? 'Désactiver' : 'Activer'}
                </button>
              </div>
            </td>
          </tr>
        `;
      });
      html += '</tbody></table>';
      $c.html(html);
    });
  }

  $(document).on('click', '.btn-add-niv', function (e) {
    e.stopPropagation();
    openModal('modal-niveau', 'niv', false, { 'niv-code': '', 'niv-libelle': '' });
    $('#niv-filiere-code').val($(this).data('fil-code'));
    $('#niv-filiere-display').val($(this).data('fil-libelle'));
    $('#modal-niv-title').text('Nouveau niveau');
  });

  $(document).on('click', '.btn-edit-niv', function (e) {
    e.stopPropagation();
    openModal('modal-niveau', 'niv', true, { 'niv-code': $(this).data('code'), 'niv-libelle': $(this).data('libelle') });
    $('#niv-filiere-code').val($(this).data('fil-code'));
    $('#niv-filiere-display').val($(this).data('fil-libelle'));
    $('#modal-niv-title').text('Modifier le niveau');
  });

  $('#btn-save-niv').on('click', function () {
    const code = $('#niv-code').val(); const libelle = $('#niv-libelle').val().trim();
    clearErr('niv-libelle');
    if (!libelle) { showErr('niv-libelle', 'Le libellé est obligatoire.'); return; }
    setSaving('niv', true);
    const data = { libelle_niveau: libelle, filiere_code: $('#niv-filiere-code').val() };
    if (code) data.code_niveau = code;
    $.ajax({
      url: code ? '/api/niveaux/modifier' : '/api/niveaux/ajouter', method: 'POST', data,
      success: function (res) {
        setSaving('niv', false);
        if (res.success) { showToast(res.message, 'success'); closeModal('modal-niveau'); loadNiveaux($('#niv-filiere-code').val()); }
        else showToast(res.message, 'error');
      },
      error: function (xhr) { setSaving('niv', false); apiError(xhr, { libelle_niveau: 'niv-libelle' }); }
    });
  });

  $(document).on('click', '.btn-toggle-niv', function (e) {
    e.stopPropagation();
    const $btn = $(this); const code = $btn.data('code'); const statut = $btn.data('statut'); const fil = $btn.data('fil');
    if (!confirm(`${statut === 'actif' ? 'Activer' : 'Désactiver'} ce niveau ?`)) return;
    $btn.prop('disabled', true);
    $.post('/api/niveaux/statut', { code_niveau: code, statut_niveau: statut }, function (res) {
      if (res.success) { showToast(res.message, 'success'); loadNiveaux(fil); }
      else { showToast(res.message, 'error'); $btn.prop('disabled', false); }
    });
  });

  // ═══════════════════════════════════════════════════════════
  // SALLES
  // ═══════════════════════════════════════════════════════════
  function loadSalles() {
    $.get('/api/salles/liste', function (res) {
      const salles = res.data || [];
      const $tbody = $('#tbody-salles');
      $tbody.empty();
      if (!salles.length) { $tbody.html(`<tr><td colspan="4"><div class="empty-state"><p>Aucune salle enregistrée.</p></div></td></tr>`); return; }
      salles.forEach(function (s) {
        const badge = s.statut_salle === 'actif' ? 'badge-success' : 'badge-danger';
        $tbody.append(`
          <tr>
            <td class="fw-600">${esc(s.libelle_salle)}</td>
            <td class="text-muted text-sm">${esc(s.code_salle)}</td>
            <td><span class="badge ${badge}">${s.statut_salle === 'actif' ? 'Active' : 'Inactive'}</span></td>
            <td>
              <div class="d-flex gap-2">
                <button class="btn btn-outline btn-sm btn-edit-salle" data-code="${s.code_salle}" data-libelle="${escA(s.libelle_salle)}">Modifier</button>
                <button class="btn btn-sm ${s.statut_salle === 'actif' ? 'btn-danger' : 'btn-accent'} btn-toggle-salle"
                  data-code="${s.code_salle}" data-statut="${s.statut_salle === 'actif' ? 'inactif' : 'actif'}">
                  ${s.statut_salle === 'actif' ? 'Désactiver' : 'Activer'}
                </button>
              </div>
            </td>
          </tr>
        `);
      });
    });
  }

  $('#btn-add-salle').on('click', function () {
    openModal('modal-salle', 'salle', false, { 'salle-code': '', 'salle-libelle': '' });
    $('#modal-salle-title').text('Nouvelle salle');
  });
  $(document).on('click', '.btn-edit-salle', function () {
    openModal('modal-salle', 'salle', true, { 'salle-code': $(this).data('code'), 'salle-libelle': $(this).data('libelle') });
    $('#modal-salle-title').text('Modifier la salle');
  });
  $('#btn-save-salle').on('click', function () {
    const code = $('#salle-code').val(); const libelle = $('#salle-libelle').val().trim();
    clearErr('salle-libelle');
    if (!libelle) { showErr('salle-libelle', 'Le libellé est obligatoire.'); return; }
    setSaving('salle', true);
    const data = { libelle_salle: libelle };
    if (code) data.code_salle = code;
    $.ajax({
      url: code ? '/api/salles/modifier' : '/api/salles/ajouter', method: 'POST', data,
      success: function (res) {
        setSaving('salle', false);
        if (res.success) { showToast(res.message, 'success'); closeModal('modal-salle'); loadSalles(); }
        else showToast(res.message, 'error');
      },
      error: function (xhr) { setSaving('salle', false); apiError(xhr, { libelle_salle: 'salle-libelle' }); }
    });
  });
  $(document).on('click', '.btn-toggle-salle', function () {
    const $btn = $(this); const code = $btn.data('code'); const statut = $btn.data('statut');
    if (!confirm(`${statut === 'actif' ? 'Activer' : 'Désactiver'} cette salle ?`)) return;
    $btn.prop('disabled', true);
    $.post('/api/salles/statut', { code_salle: code, statut_salle: statut }, function (res) {
      if (res.success) { showToast(res.message, 'success'); loadSalles(); }
      else { showToast(res.message, 'error'); $btn.prop('disabled', false); }
    });
  });

  // ═══════════════════════════════════════════════════════════
  // HELPERS
  // ═══════════════════════════════════════════════════════════
  function openModal(modalId, prefix, isEdit, fields) {
    Object.entries(fields).forEach(([id, val]) => $(`#${id}`).val(val));
    clearErr(`${prefix}-libelle`);
    setSaving(prefix, false);
    $(`#${modalId}`).addClass('open');
  }
  function closeModal(id) { $(`#${id}`).removeClass('open'); }
  $(document).on('click', '[data-close]', function () { closeModal($(this).data('close')); });
  $(document).on('click', '.modal-backdrop', function (e) {
    if ($(e.target).hasClass('modal-backdrop')) $(this).removeClass('open');
  });
  function setSaving(p, s) {
    $(`#btn-save-${p}`).prop('disabled', s);
    $(`#save-${p}-text`).text(s ? 'Enregistrement…' : 'Enregistrer');
    $(`#save-${p}-spinner`).toggle(s);
  }
  function clearErr(id) { $(`#err-${id}`).removeClass('show').text(''); $(`#${id}`).removeClass('is-invalid'); }
  function showErr(id, msg) { $(`#err-${id}`).text(msg).addClass('show'); $(`#${id}`).addClass('is-invalid'); }
  function apiError(xhr, map) {
    const res = xhr.responseJSON;
    if (res?.errors) { $.each(res.errors, (f, m) => showErr(map[f] || f, m)); }
    else showToast(res?.message || 'Erreur.', 'error');
  }
  function esc(s) { return $('<div>').text(s || '').html(); }
  function escA(s) { return String(s || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }
});

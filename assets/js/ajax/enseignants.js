/**
 * enseignants.js — CRUD enseignants + affectation matières (AJAX)
 */
$(document).ready(function () {
  let editMode = false;

  // ─── Charger enseignants ──────────────────────────────────
  function loadEnseignants() {
    $.get('/appeicg/enseignants/liste', function (res) {
      const ens = res.data || [];
      $('#ens-count').text(`${ens.length} enseignant${ens.length > 1 ? 's' : ''}`);
      const $tbody = $('#tbody-ens');
      $tbody.empty();
      if (!ens.length) {
        $tbody.html(`<tr><td colspan="8"><div class="empty-state"><p>Aucun enseignant enregistré.</p></div></td></tr>`);
        return;
      }
      ens.forEach(function (e) {
        const initials = e.nom_enseignant.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
        const badge    = e.statut_enseignant === 'actif' ? 'badge-success' : 'badge-danger';
        const toggle   = e.statut_enseignant === 'actif' ? 'inactif' : 'actif';
        const matieres = e.matieres_libelles
          ? e.matieres_libelles.split(', ').map(m => `<span class="mat-tag">${esc(m)}</span>`).join('')
          : '<span class="text-muted text-sm">—</span>';

        $tbody.append(`
          <tr class="expandable" data-code="${e.code_enseignant}" data-target="mat-row-${e.code_enseignant}">
            <td><span class="expand-icon">▶</span></td>
            <td>
              <div class="d-flex align-center gap-2">
                <div class="ens-avatar">${initials}</div>
                <span class="fw-600">${esc(e.nom_enseignant)}</span>
              </div>
            </td>
            <td class="text-sm text-muted">${esc(e.matricule)}</td>
            <td>${esc(e.telephone)}</td>
            <td>${e.email ? esc(e.email) : '<span class="text-muted">—</span>'}</td>
            <td style="max-width:200px">${matieres}</td>
            <td><span class="badge ${badge}">${e.statut_enseignant === 'actif' ? 'Actif' : 'Inactif'}</span></td>
            <td>
              <div class="d-flex gap-2" onclick="event.stopPropagation()">
                <button class="btn btn-outline btn-sm btn-edit-ens"
                  data-code="${e.code_enseignant}"
                  data-nom="${escA(e.nom_enseignant)}"
                  data-telephone="${escA(e.telephone)}"
                  data-email="${escA(e.email || '')}"
                  data-sexe="${escA(e.sexe || '')}"
                  data-datenaissance="${e.date_naissance || ''}"
                  data-lieunaissance="${escA(e.lieu_naissance || '')}">
                  Modifier
                </button>
                <button class="btn btn-sm ${e.statut_enseignant === 'actif' ? 'btn-danger' : 'btn-accent'} btn-toggle-ens"
                  data-code="${e.code_enseignant}" data-statut="${toggle}">
                  ${e.statut_enseignant === 'actif' ? 'Désactiver' : 'Activer'}
                </button>
              </div>
            </td>
          </tr>
          <tr class="mat-panel" id="mat-row-${e.code_enseignant}">
            <td colspan="8">
              <div class="mat-inner">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                  <span class="fw-600 text-sm">Matières de ${esc(e.nom_enseignant)}</span>
                  <button class="btn btn-accent btn-sm btn-open-aff"
                    data-code="${e.code_enseignant}" data-nom="${escA(e.nom_enseignant)}">
                    Gérer les matières
                  </button>
                </div>
                <div id="matlist-${e.code_enseignant}">
                  <p class="text-sm text-muted">Cliquez pour charger…</p>
                </div>
              </div>
            </td>
          </tr>
        `);
      });
    });
  }
  loadEnseignants();

  // ─── Expand row → matières ────────────────────────────────
  $(document).on('click', '.expandable', function () {
    const $row = $(this); const target = $row.data('target'); const code = $row.data('code');
    const $panel = $('#' + target);
    if ($panel.hasClass('open')) { $panel.removeClass('open'); $row.removeClass('expanded'); return; }
    $('.mat-panel.open').removeClass('open'); $('.expandable.expanded').removeClass('expanded');
    $row.addClass('expanded'); $panel.addClass('open');
    loadMatieresList(code);
  });

  function loadMatieresList(ensCode) {
    const $c = $(`#matlist-${ensCode}`);
    $c.html('<p class="text-sm text-muted">Chargement…</p>');
    $.get(`/appeicg/enseignants/matieres?enseignant_code=${ensCode}`, function (res) {
      const mats = res.data || [];
      if (!mats.length) { $c.html('<p class="text-sm text-muted">Aucune matière affectée.</p>'); return; }
      const tags = mats.filter(m => m.statut_enseignant_matiere === 'actif').map(m =>
        `<span class="mat-tag">${esc(m.libelle_matiere)}
           <span class="remove-mat" data-ens="${ensCode}" data-mat="${m.matiere_code}" title="Retirer">×</span>
         </span>`
      ).join('');
      $c.html(tags || '<p class="text-sm text-muted">Aucune matière active.</p>');
    });
  }

  // ─── Retirer matière inline ───────────────────────────────
  $(document).on('click', '.remove-mat', function (e) {
    e.stopPropagation();
    const ensCode = $(this).data('ens'); const matCode = $(this).data('mat');
    if (!confirm('Retirer cette matière ?')) return;
    $.post('/appeicg/enseignants/retirer', { enseignant_code: ensCode, matiere_code: matCode }, function (res) {
      if (res.success) { showToast(res.message, 'success'); loadMatieresList(ensCode); loadEnseignants(); }
      else showToast(res.message, 'error');
    });
  });

  // ─── Modal Ajouter/Modifier ───────────────────────────────
  $('#btn-add-ens').on('click', function () {
    editMode = false; resetEns();
    $('#modal-ens-title').text('Nouvel enseignant');
    $('#modal-ens').addClass('open');
  });

  $(document).on('click', '.btn-edit-ens', function (e) {
    e.stopPropagation();
    editMode = true; resetEns();
    $('#modal-ens-title').text('Modifier l\'enseignant');
    $('#ens-code').val($(this).data('code'));
    $('#ens-nom').val($(this).data('nom'));
    $('#ens-telephone').val($(this).data('telephone'));
    $('#ens-email').val($(this).data('email'));
    $('#ens-sexe').val($(this).data('sexe'));
    $('#ens-datenaissance').val($(this).data('datenaissance'));
    $('#ens-lieunaissance').val($(this).data('lieunaissance'));
    $('#modal-ens').addClass('open');
  });

  $('#btn-save-ens').on('click', function () {
    clearAllErr();
    const code = $('#ens-code').val();
    const nom  = $('#ens-nom').val().trim();
    const tel  = $('#ens-telephone').val().trim();
    let ok = true;
    if (!nom) { showErr('ens-nom', 'Le nom est obligatoire.'); ok = false; }
    if (!tel) { showErr('ens-telephone', 'Le téléphone est obligatoire.'); ok = false; }
    if (!ok) return;
    setSaving(true);
    const data = {
      nom_enseignant: nom, telephone: tel,
      email: $('#ens-email').val().trim(), sexe: $('#ens-sexe').val(),
      date_naissance: $('#ens-datenaissance').val(), lieu_naissance: $('#ens-lieunaissance').val().trim()
    };
    if (code) data.code_enseignant = code;
    $.ajax({
      url: code ? '/appeicg/enseignants/modifier' : '/appeicg/enseignants/ajouter', method: 'POST', data,
      success: function (res) {
        setSaving(false);
        if (res.success) {
          showToast(res.message + (res.data?.matricule ? ` (Matricule : ${res.data.matricule})` : ''), 'success');
          closeEns(); loadEnseignants();
        } else showToast(res.message, 'error');
      },
      error: function (xhr) {
        setSaving(false);
        const res = xhr.responseJSON;
        if (res?.errors) {
          const map = { nom_enseignant: 'ens-nom', telephone: 'ens-telephone', email: 'ens-email' };
          $.each(res.errors, (f, m) => showErr(map[f] || f, m));
        } else showToast(res?.message || 'Erreur.', 'error');
      }
    });
  });

  $(document).on('click', '.btn-toggle-ens', function (e) {
    e.stopPropagation();
    const $btn = $(this); const code = $btn.data('code'); const statut = $btn.data('statut');
    if (!confirm(`${statut === 'actif' ? 'Activer' : 'Désactiver'} cet enseignant ?`)) return;
    $btn.prop('disabled', true);
    $.post('/appeicg/enseignants/statut', { code_enseignant: code, statut_enseignant: statut }, function (res) {
      if (res.success) { showToast(res.message, 'success'); loadEnseignants(); }
      else { showToast(res.message, 'error'); $btn.prop('disabled', false); }
    });
  });

  // ─── Modal Affectation Matières ───────────────────────────
  $(document).on('click', '.btn-open-aff', function (e) {
    e.stopPropagation();
    const code = $(this).data('code'); const nom = $(this).data('nom');
    $('#aff-ens-code').val(code);
    $('#aff-ens-nom').text(nom);
    loadAffMatieres(code);
    // Charger la liste des matières disponibles
    $.get('/appeicg/matieres/liste', function (res) {
      const $sel = $('#aff-matiere-select');
      $sel.find('option:not(:first)').remove();
      (res.data || []).filter(m => m.statut_matiere === 'actif').forEach(m => {
        $sel.append(`<option value="${m.code_matiere}">${esc(m.libelle_matiere)}</option>`);
      });
    });
    $('#modal-aff').addClass('open');
  });

  function loadAffMatieres(ensCode) {
    const $c = $('#aff-matieres-list');
    $.get(`/appeicg/enseignants/matieres?enseignant_code=${ensCode}`, function (res) {
      const mats = (res.data || []).filter(m => m.statut_enseignant_matiere === 'actif');
      if (!mats.length) { $c.html('<p class="text-sm text-muted">Aucune matière affectée.</p>'); return; }
      const tags = mats.map(m =>
        `<span class="mat-tag">${esc(m.libelle_matiere)}
           <span class="remove-aff" data-mat="${m.matiere_code}" title="Retirer">×</span>
         </span>`
      ).join('');
      $c.html(tags);
    });
  }

  $('#btn-affecter').on('click', function () {
    const ensCode = $('#aff-ens-code').val();
    const matCode = $('#aff-matiere-select').val();
    if (!matCode) { showToast('Sélectionnez une matière.', 'warning'); return; }
    $(this).prop('disabled', true);
    $.post('/appeicg/enseignants/affecter', { enseignant_code: ensCode, matiere_code: matCode }, function (res) {
      $('#btn-affecter').prop('disabled', false);
      if (res.success) { showToast(res.message, 'success'); loadAffMatieres(ensCode); loadEnseignants(); }
      else showToast(res.message, 'error');
    }).fail(function (xhr) {
      $('#btn-affecter').prop('disabled', false);
      showToast(xhr.responseJSON?.message || 'Erreur.', 'error');
    });
  });

  $(document).on('click', '.remove-aff', function () {
    const ensCode = $('#aff-ens-code').val(); const matCode = $(this).data('mat');
    if (!confirm('Retirer cette matière ?')) return;
    $.post('/appeicg/enseignants/retirer', { enseignant_code: ensCode, matiere_code: matCode }, function (res) {
      if (res.success) { showToast(res.message, 'success'); loadAffMatieres(ensCode); loadEnseignants(); }
      else showToast(res.message, 'error');
    });
  });

  // ─── Helpers ──────────────────────────────────────────────
  function resetEns() {
    $('#ens-code,#ens-nom,#ens-telephone,#ens-email,#ens-datenaissance,#ens-lieunaissance').val('');
    $('#ens-sexe').val(''); clearAllErr(); setSaving(false);
  }
  function closeEns() { $('#modal-ens').removeClass('open'); resetEns(); }
  function setSaving(s) { $('#btn-save-ens').prop('disabled', s); $('#save-ens-text').text(s ? 'Enregistrement…' : 'Enregistrer'); $('#save-ens-spinner').toggle(s); }
  function clearAllErr() { ['ens-nom','ens-telephone','ens-email'].forEach(id => { $(`#err-${id}`).removeClass('show').text(''); $(`#${id}`).removeClass('is-invalid'); }); }
  function showErr(id, msg) { $(`#err-${id}`).text(msg).addClass('show'); $(`#${id}`).addClass('is-invalid'); }
  function esc(s) { return $('<div>').text(s || '').html(); }
  function escA(s) { return String(s || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }

  $(document).on('click', '[data-close]', function () { $('#' + $(this).data('close')).removeClass('open'); });
  $(document).on('click', '.modal-backdrop', function (e) { if ($(e.target).hasClass('modal-backdrop')) $(this).removeClass('open'); });
});

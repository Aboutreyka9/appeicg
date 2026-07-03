/**
 * matieres.js — CRUD matières (AJAX)
 */
$(document).ready(function () {
  let editMode = false;

  function loadMatieres() {
    $.get('/appeicg/matieres/liste', function (res) {
      const mats = res.data || [];
      $('#mat-count').text(`${mats.length} matière${mats.length > 1 ? 's' : ''}`);
      const $tbody = $('#tbody-matieres');
      $tbody.empty();
      if (!mats.length) {
        $tbody.html(`<tr><td colspan="4"><div class="empty-state"><p>Aucune matière enregistrée.</p></div></td></tr>`);
        return;
      }
      mats.forEach(function (m) {
        const badge = m.statut_matiere === 'actif' ? 'badge-success' : 'badge-danger';
        $tbody.append(`
          <tr>
            <td class="fw-600">${esc(m.libelle_matiere)}</td>
            <td class="text-muted text-sm">${esc(m.code_matiere)}</td>
            <td><span class="badge ${badge}">${m.statut_matiere === 'actif' ? 'Active' : 'Inactive'}</span></td>
            <td>
              <div class="d-flex gap-2">
                <button class="btn btn-outline btn-sm btn-edit-mat"
                  data-code="${m.code_matiere}" data-libelle="${escA(m.libelle_matiere)}">Modifier</button>
                <button class="btn btn-sm ${m.statut_matiere === 'actif' ? 'btn-danger' : 'btn-accent'} btn-toggle-mat"
                  data-code="${m.code_matiere}" data-statut="${m.statut_matiere === 'actif' ? 'inactif' : 'actif'}">
                  ${m.statut_matiere === 'actif' ? 'Désactiver' : 'Activer'}
                </button>
              </div>
            </td>
          </tr>
        `);
      });
    });
  }
  loadMatieres();

  $('#btn-add-matiere').on('click', function () {
    editMode = false; reset();
    $('#modal-mat-title').text('Nouvelle matière');
    $('#modal-matiere').addClass('open');
  });

  $(document).on('click', '.btn-edit-mat', function () {
    editMode = true; reset();
    $('#modal-mat-title').text('Modifier la matière');
    $('#mat-code').val($(this).data('code'));
    $('#mat-libelle').val($(this).data('libelle'));
    $('#modal-matiere').addClass('open');
  });

  $('#btn-save-mat').on('click', function () {
    const code = $('#mat-code').val(); const libelle = $('#mat-libelle').val().trim();
    clearErr('mat-libelle');
    if (!libelle) { showErr('mat-libelle', 'Le libellé est obligatoire.'); return; }
    setSaving(true);
    const data = { libelle_matiere: libelle };
    if (code) data.code_matiere = code;
    $.ajax({
      url: code ? '/appeicg/matieres/modifier' : '/appeicg/matieres/ajouter', method: 'POST', data,
      success: function (res) {
        setSaving(false);
        if (res.success) { showToast(res.message, 'success'); close(); loadMatieres(); }
        else showToast(res.message, 'error');
      },
      error: function (xhr) { setSaving(false); showToast(xhr.responseJSON?.message || 'Erreur.', 'error'); }
    });
  });

  $(document).on('click', '.btn-toggle-mat', function () {
    const $btn = $(this); const code = $btn.data('code'); const statut = $btn.data('statut');
    if (!confirm(`${statut === 'actif' ? 'Activer' : 'Désactiver'} cette matière ?`)) return;
    $btn.prop('disabled', true);
    $.post('/appeicg/matieres/statut', { code_matiere: code, statut_matiere: statut }, function (res) {
      if (res.success) { showToast(res.message, 'success'); loadMatieres(); }
      else { showToast(res.message, 'error'); $btn.prop('disabled', false); }
    });
  });

  function reset() { $('#mat-code, #mat-libelle').val(''); clearErr('mat-libelle'); setSaving(false); }
  function close() { $('#modal-matiere').removeClass('open'); reset(); }
  function setSaving(s) { $('#btn-save-mat').prop('disabled', s); $('#save-mat-text').text(s ? 'Enregistrement…' : 'Enregistrer'); $('#save-mat-spinner').toggle(s); }
  function clearErr(id) { $(`#err-${id}`).removeClass('show').text(''); $(`#${id}`).removeClass('is-invalid'); }
  function showErr(id, msg) { $(`#err-${id}`).text(msg).addClass('show'); $(`#${id}`).addClass('is-invalid'); }
  function esc(s) { return $('<div>').text(s || '').html(); }
  function escA(s) { return String(s || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }

  $(document).on('click', '[data-close]', function () { $('#' + $(this).data('close')).removeClass('open'); });
  $(document).on('click', '.modal-backdrop', function (e) { if ($(e.target).hasClass('modal-backdrop')) $(this).removeClass('open'); });
});

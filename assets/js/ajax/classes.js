/**
 * classes.js — Gestion CRUD des classes (AJAX)
 * EICG Gestion Scolaire
 */

$(document).ready(function () {

  let editMode = false;

  // ─── Charger les selects (années + filières) ──────────────
  function loadSelects() {
    $.get('/api/annees/liste', function (res) {
      const $sel = $('#classe-annee, #filter-annee');
      $sel.find('option:not(:first)').remove();
      (res.data || []).filter(a => a.statut_annee === 'actif').forEach(function (a) {
        $sel.append(`<option value="${a.id_annee}">${esc(a.libelle_annee)}</option>`);
      });
    });

    $.get('/api/filieres/liste', function (res) {
      const $sel = $('#classe-filiere, #filter-filiere');
      $sel.find('option:not(:first)').remove();
      (res.data || []).filter(f => f.statut_filiere === 'actif').forEach(function (f) {
        $sel.append(`<option value="${f.code_filiere}">${esc(f.libelle_filiere)}</option>`);
      });
    });
  }
  loadSelects();

  // ─── Charger niveaux selon filière sélectionnée ───────────
  $('#classe-filiere').on('change', function () {
    const filiereCode = $(this).val();
    const $niv = $('#classe-niveau');
    $niv.find('option:not(:first)').remove();
    $niv.prop('disabled', !filiereCode);
    if (!filiereCode) return;

    $.get(`/api/niveaux/liste?filiere_code=${filiereCode}`, function (res) {
      (res.data || []).filter(n => n.statut_niveau === 'actif').forEach(function (n) {
        $niv.append(`<option value="${n.code_niveau}">${esc(n.libelle_niveau)}</option>`);
      });
    });
  });

  // ─── Charger la liste des classes ────────────────────────
  function loadClasses() {
    const anneeId  = $('#filter-annee').val();
    const filiere  = $('#filter-filiere').val();
    let url = '/api/classes/liste?';
    if (anneeId)  url += `annee_code=${anneeId}&`;
    if (filiere)  url += `niveau_code=${filiere}`;

    $.get(url, function (res) {
      const classes = res.data || [];
      $('#classe-count').text(`${classes.length} classe${classes.length > 1 ? 's' : ''}`);
      const $tbody = $('#tbody-classes');
      $tbody.empty();

      if (!classes.length) {
        $tbody.html(`<tr><td colspan="8"><div class="empty-state"><p>Aucune classe trouvée.</p></div></td></tr>`);
        return;
      }

      classes.forEach(function (c) {
        const badge     = c.statut_classe === 'actif' ? 'badge-success' : 'badge-danger';
        const capacite  = c.capacite_max_classe ?? '—';
        const nbEtud    = c.nb_etudiants ?? 0;
        const ratio     = c.capacite_max_classe ? `${nbEtud}/${c.capacite_max_classe}` : nbEtud;
        const toggleStat = c.statut_classe === 'actif' ? 'inactif' : 'actif';

        $tbody.append(`
          <tr>
            <td class="fw-600">${esc(c.libelle_classe)}</td>
            <td>${esc(c.libelle_filiere || '—')}</td>
            <td>${esc(c.libelle_niveau  || '—')}</td>
            <td>${esc(c.libelle_annee   || c.annee_code || '—')}</td>
            <td>${capacite}</td>
            <td>
              <span class="badge ${nbEtud > 0 ? 'badge-info' : 'badge-warning'}">${ratio}</span>
            </td>
            <td><span class="badge ${badge}">${c.statut_classe === 'actif' ? 'Active' : 'Inactive'}</span></td>
            <td>
              <div class="d-flex gap-2">
                <button class="btn btn-outline btn-sm btn-edit-classe"
                  data-code="${c.code_classe}"
                  data-libelle="${escA(c.libelle_classe)}"
                  data-niveau="${c.niveau_code}"
                  data-filiere="${esc(c.code_filiere || '')}"
                  data-annee="${c.id_annee || ''}"
                  data-capacite="${c.capacite_max_classe || ''}">
                  Modifier
                </button>
                <button class="btn btn-sm ${c.statut_classe === 'actif' ? 'btn-danger' : 'btn-accent'} btn-toggle-classe"
                  data-code="${c.code_classe}" data-statut="${toggleStat}">
                  ${c.statut_classe === 'actif' ? 'Désactiver' : 'Activer'}
                </button>
              </div>
            </td>
          </tr>
        `);
      });
    });
  }
  loadClasses();

  $('#btn-filter').on('click', loadClasses);

  // ─── Modal Ajouter ────────────────────────────────────────
  $('#btn-add-classe').on('click', function () {
    editMode = false;
    resetModal();
    $('#modal-classe-title').text('Nouvelle classe');
    $('#modal-classe').addClass('open');
  });

  // ─── Modal Modifier ───────────────────────────────────────
  $(document).on('click', '.btn-edit-classe', function () {
    editMode = true;
    resetModal();
    $('#modal-classe-title').text('Modifier la classe');
    $('#classe-code').val($(this).data('code'));
    $('#classe-libelle').val($(this).data('libelle'));
    $('#classe-annee').val($(this).data('annee'));
    $('#classe-capacite').val($(this).data('capacite'));

    // Charger filière puis niveau
    const filiereCode = $(this).data('filiere');
    const niveauCode  = $(this).data('niveau');

    if (filiereCode) {
      $('#classe-filiere').val(filiereCode).trigger('change');
      // Attendre le chargement des niveaux avant de sélectionner
      setTimeout(() => $('#classe-niveau').val(niveauCode), 600);
    }

    $('#modal-classe').addClass('open');
  });

  // ─── Enregistrer ──────────────────────────────────────────
  $('#btn-save-classe').on('click', function () {
    clearAllErrors();
    const libelle  = $('#classe-libelle').val().trim();
    const anneeId  = $('#classe-annee').val();
    const filiere  = $('#classe-filiere').val();
    const niveau   = $('#classe-niveau').val();
    const capacite = $('#classe-capacite').val();
    let ok = true;

    if (!libelle) { showErr('classe-libelle', 'Le libellé est obligatoire.');    ok = false; }
    if (!anneeId) { showErr('classe-annee',   'L\'année est obligatoire.');      ok = false; }
    if (!filiere) { showErr('classe-filiere', 'La filière est obligatoire.');    ok = false; }
    if (!niveau)  { showErr('classe-niveau',  'Le niveau est obligatoire.');     ok = false; }
    if (!ok) return;

    setSaving(true);
    const url  = editMode ? '/api/classes/modifier' : '/api/classes/ajouter';
    const data = {
      libelle_classe:      libelle,
      annee_code:          anneeId,
      niveau_code:         niveau,
      capacite_max_classe: capacite || '',
    };
    if (editMode) data.code_classe = $('#classe-code').val();

    $.ajax({
      url, method: 'POST', data,
      success: function (res) {
        setSaving(false);
        if (res.success) { showToast(res.message, 'success'); closeModal(); loadClasses(); }
        else showToast(res.message || 'Erreur.', 'error');
      },
      error: function (xhr) {
        setSaving(false);
        const res = xhr.responseJSON;
        if (res?.errors) {
          const map = { libelle_classe: 'classe-libelle', annee_code: 'classe-annee', niveau_code: 'classe-niveau' };
          $.each(res.errors, (f, m) => showErr(map[f] || f, m));
        } else showToast(res?.message || 'Erreur.', 'error');
      }
    });
  });

  // ─── Changer statut ───────────────────────────────────────
  $(document).on('click', '.btn-toggle-classe', function () {
    const $btn = $(this); const code = $btn.data('code'); const statut = $btn.data('statut');
    if (!confirm(`${statut === 'actif' ? 'Activer' : 'Désactiver'} cette classe ?`)) return;
    $btn.prop('disabled', true);
    $.post('/api/classes/statut', { code_classe: code, statut_classe: statut }, function (res) {
      if (res.success) { showToast(res.message, 'success'); loadClasses(); }
      else { showToast(res.message, 'error'); $btn.prop('disabled', false); }
    });
  });

  // ─── Fermer modal ─────────────────────────────────────────
  function closeModal() { $('#modal-classe').removeClass('open'); resetModal(); }
  $(document).on('click', '[data-close]', function () { closeModal(); });
  $(document).on('click', '.modal-backdrop', function (e) {
    if ($(e.target).hasClass('modal-backdrop')) closeModal();
  });

  function resetModal() {
    $('#classe-code, #classe-libelle, #classe-capacite').val('');
    $('#classe-annee, #classe-filiere').val('');
    $('#classe-niveau').find('option:not(:first)').remove().end().prop('disabled', true);
    clearAllErrors(); setSaving(false);
  }
  function clearAllErrors() {
    ['classe-libelle','classe-annee','classe-filiere','classe-niveau'].forEach(id => {
      $(`#err-${id}`).removeClass('show').text(''); $(`#${id}`).removeClass('is-invalid');
    });
  }
  function showErr(id, msg) { $(`#err-${id}`).text(msg).addClass('show'); $(`#${id}`).addClass('is-invalid'); }
  function setSaving(s) {
    $('#btn-save-classe').prop('disabled', s);
    $('#save-classe-text').text(s ? 'Enregistrement…' : 'Enregistrer');
    $('#save-classe-spinner').toggle(s);
  }
  function esc(s) { return $('<div>').text(s || '').html(); }
  function escA(s) { return String(s || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }
});

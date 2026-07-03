/**
 * annees.js — Gestion CRUD années scolaires & semestres (AJAX)
 * EICG Gestion Scolaire
 */

$(document).ready(function () {

  let anneeEditMode = false;
  let semEditMode   = false;

  // ═══════════════════════════════════════════════════════════
  // ANNÉES SCOLAIRES
  // ═══════════════════════════════════════════════════════════

  function loadAnnees() {
    $.ajax({
      url: '/appeicg/annees/liste',
      method: 'GET',
      success: function (res) {
        if (!res.success) { showToast('Erreur lors du chargement.', 'error'); return; }

        const annees = res.data || [];
        $('#annee-count').text(`${annees.length} année${annees.length > 1 ? 's' : ''}`);
        const $tbody = $('#tbody-annees');
        $tbody.empty();

        if (annees.length === 0) {
          $tbody.html(`<tr><td colspan="7"><div class="empty-state"><p>Aucune année scolaire enregistrée.</p></div></td></tr>`);
          return;
        }

        annees.forEach(function (a) {
          const badgeClass  = a.statut_annee === 'actif' ? 'badge-success' : 'badge-danger';
          const badgeLabel  = a.statut_annee === 'actif' ? 'Active' : 'Inactive';
          const toggleLabel = a.statut_annee === 'actif' ? 'Désactiver' : 'Activer';
          const toggleStat  = a.statut_annee === 'actif' ? 'inactif' : 'actif';
          const rowId       = `sem-row-${a.id_annee}`;

          $tbody.append(`
            <tr class="annee-row" data-id="${a.id_annee}" data-target="${rowId}">
              <td><span class="expand-icon" style="color:var(--text-muted); font-size:.75rem;">▶</span></td>
              <td><span class="fw-600">${escHtml(a.libelle_annee)}</span></td>
              <td>${formatDate(a.date_debut_annee)}</td>
              <td>${formatDate(a.date_fin_annee)}</td>
              <td><span class="sem-count-${a.id_annee} badge badge-info">—</span></td>
              <td><span class="badge ${badgeClass}">${badgeLabel}</span></td>
              <td>
                <div class="d-flex gap-2" onclick="event.stopPropagation()">
                  <button class="btn btn-outline btn-sm btn-edit-annee"
                    data-id="${a.id_annee}"
                    data-libelle="${escAttr(a.libelle_annee)}"
                    data-debut="${a.date_debut_annee}"
                    data-fin="${a.date_fin_annee}">Modifier</button>
                  <button class="btn btn-sm ${a.statut_annee === 'actif' ? 'btn-danger' : 'btn-accent'} btn-toggle-annee"
                    data-id="${a.id_annee}"
                    data-statut="${toggleStat}">${toggleLabel}</button>
                </div>
              </td>
            </tr>
            <tr class="semestres-panel" id="${rowId}">
              <td colspan="7">
                <div class="semestres-inner">
                  <div class="semestres-header">
                    <h4>Semestres de l'année <strong>${escHtml(a.libelle_annee)}</strong></h4>
                    <button class="btn btn-accent btn-sm btn-add-sem"
                      data-annee-id="${a.id_annee}"
                      data-annee-libelle="${escAttr(a.libelle_annee)}">
                      + Ajouter un semestre
                    </button>
                  </div>
                  <div id="sem-list-${a.id_annee}">
                    <p class="text-sm text-muted">Cliquez sur la ligne pour charger les semestres.</p>
                  </div>
                </div>
              </td>
            </tr>
          `);
        });
      },
      error: function () { showToast('Impossible de charger les années.', 'error'); }
    });
  }

  loadAnnees();

  // ── Expand / collapse row ──────────────────────────────────
  $(document).on('click', '.annee-row', function () {
    const $row   = $(this);
    const target = $row.data('target');
    const anneeId = $row.data('id');
    const $panel = $('#' + target);

    if ($panel.hasClass('open')) {
      $panel.removeClass('open');
      $row.removeClass('expanded');
      return;
    }

    // Fermer les autres
    $('.semestres-panel.open').removeClass('open');
    $('.annee-row.expanded').removeClass('expanded');

    $row.addClass('expanded');
    $panel.addClass('open');
    loadSemestres(anneeId);
  });

  // ── Ouvrir modal Ajouter Année ─────────────────────────────
  $('#btn-add-annee').on('click', function () {
    anneeEditMode = false;
    resetAnneeModal();
    $('#modal-annee-title').text('Nouvelle année scolaire');
    $('#modal-annee').addClass('open');
  });

  // ── Ouvrir modal Modifier Année ────────────────────────────
  $(document).on('click', '.btn-edit-annee', function (e) {
    e.stopPropagation();
    anneeEditMode = true;
    resetAnneeModal();
    $('#modal-annee-title').text('Modifier l\'année scolaire');
    $('#annee-id').val($(this).data('id'));
    $('#annee-libelle').val($(this).data('libelle'));
    $('#annee-debut').val($(this).data('debut'));
    $('#annee-fin').val($(this).data('fin'));
    $('#modal-annee').addClass('open');
  });

  // ── Enregistrer Année ──────────────────────────────────────
  $('#btn-save-annee').on('click', function () {
    clearErrors(['annee-libelle', 'annee-debut', 'annee-fin']);

    const libelle = $('#annee-libelle').val().trim();
    const debut   = $('#annee-debut').val();
    const fin     = $('#annee-fin').val();
    let ok = true;

    if (!libelle) { showFieldError('annee-libelle', 'Le libellé est obligatoire.'); ok = false; }
    if (!debut)   { showFieldError('annee-debut',   'La date de début est obligatoire.'); ok = false; }
    if (!fin)     { showFieldError('annee-fin',     'La date de fin est obligatoire.'); ok = false; }
    if (debut && fin && debut >= fin) { showFieldError('annee-fin', 'La date de fin doit être après la date de début.'); ok = false; }
    if (!ok) return;

    setSaving('annee', true);

    const url  = anneeEditMode ? '/appeicg/annees/modifier' : '/appeicg/annees/ajouter';
    const data = { libelle_annee: libelle, date_debut_annee: debut, date_fin_annee: fin };
    if (anneeEditMode) data.id_annee = $('#annee-id').val();

    $.ajax({
      url, method: 'POST', data,
      success: function (res) {
        setSaving('annee', false);
        if (res.success) {
          showToast(res.message, 'success');
          closeModal('modal-annee');
          loadAnnees();
        } else {
          showToast(res.message || 'Erreur.', 'error');
        }
      },
      error: function (xhr) {
        setSaving('annee', false);
        handleErrors(xhr, { libelle_annee: 'annee-libelle', date_debut_annee: 'annee-debut', date_fin_annee: 'annee-fin' });
      }
    });
  });

  // ── Changer statut Année ───────────────────────────────────
  $(document).on('click', '.btn-toggle-annee', function (e) {
    e.stopPropagation();
    const $btn   = $(this);
    const id     = $btn.data('id');
    const statut = $btn.data('statut');
    if (!confirm(`Voulez-vous vraiment ${statut === 'actif' ? 'activer' : 'désactiver'} cette année ?`)) return;
    $btn.prop('disabled', true);
    $.ajax({
      url: '/appeicg/annees/statut', method: 'POST',
      data: { id_annee: id, statut_annee: statut },
      success: function (res) {
        if (res.success) { showToast(res.message, 'success'); loadAnnees(); }
        else { showToast(res.message, 'error'); $btn.prop('disabled', false); }
      },
      error: function () { showToast('Erreur.', 'error'); $btn.prop('disabled', false); }
    });
  });

  // ═══════════════════════════════════════════════════════════
  // SEMESTRES
  // ═══════════════════════════════════════════════════════════

  function loadSemestres(anneeId) {
    const $container = $(`#sem-list-${anneeId}`);
    $container.html('<p class="text-sm text-muted">Chargement…</p>');

    // On a besoin du libelle_annee — on le lit depuis le bouton d'ajout
    const anneeLibelle = $(`.btn-add-sem[data-annee-id="${anneeId}"]`).data('annee-libelle');

    $.ajax({
      url: `/appeicg/semestres/liste?annee_code=${encodeURIComponent(anneeLibelle)}`,
      method: 'GET',
      success: function (res) {
        const sems = res.data || [];
        $(`.sem-count-${anneeId}`).text(`${sems.length} sem.`);

        if (sems.length === 0) {
          $container.html('<p class="text-sm text-muted">Aucun semestre pour cette année. Ajoutez-en un.</p>');
          return;
        }

        let html = `
          <table class="sem-table">
            <thead>
              <tr>
                <th>Libellé</th>
                <th>Date début</th>
                <th>Date fin</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
        `;

        sems.forEach(function (s) {
          html += `
            <tr>
              <td class="fw-600">${escHtml(s.libelle_semestre)}</td>
              <td>${s.date_debut_semestre ? formatDate(s.date_debut_semestre) : '<span class="text-muted">—</span>'}</td>
              <td>${s.date_fin_semestre   ? formatDate(s.date_fin_semestre)   : '<span class="text-muted">—</span>'}</td>
              <td>
                <div class="d-flex gap-2">
                  <button class="btn btn-outline btn-sm btn-edit-sem"
                    data-code="${s.code_semestre}"
                    data-libelle="${escAttr(s.libelle_semestre)}"
                    data-debut="${s.date_debut_semestre || ''}"
                    data-fin="${s.date_fin_semestre || ''}"
                    data-annee-id="${anneeId}"
                    data-annee-libelle="${escAttr(anneeLibelle)}">
                    Modifier
                  </button>
                  <button class="btn btn-danger btn-sm btn-del-sem"
                    data-code="${s.code_semestre}"
                    data-annee-id="${anneeId}">
                    Supprimer
                  </button>
                </div>
              </td>
            </tr>
          `;
        });

        html += '</tbody></table>';
        $container.html(html);
      },
      error: function () {
        $container.html('<p class="text-sm" style="color:var(--danger);">Erreur lors du chargement des semestres.</p>');
      }
    });
  }

  // ── Ouvrir modal Ajouter Semestre ──────────────────────────
  $(document).on('click', '.btn-add-sem', function (e) {
    e.stopPropagation();
    semEditMode = false;
    resetSemModal();
    $('#modal-sem-title').text('Nouveau semestre');
    $('#sem-annee-id').val($(this).data('annee-id'));
    $('#sem-annee-libelle').val($(this).data('annee-libelle'));
    $('#sem-annee-display').val($(this).data('annee-libelle'));
    $('#modal-semestre').addClass('open');
  });

  // ── Ouvrir modal Modifier Semestre ─────────────────────────
  $(document).on('click', '.btn-edit-sem', function (e) {
    e.stopPropagation();
    semEditMode = true;
    resetSemModal();
    $('#modal-sem-title').text('Modifier le semestre');
    $('#sem-code').val($(this).data('code'));
    $('#sem-annee-id').val($(this).data('annee-id'));
    $('#sem-annee-libelle').val($(this).data('annee-libelle'));
    $('#sem-annee-display').val($(this).data('annee-libelle'));
    $('#sem-libelle').val($(this).data('libelle'));
    $('#sem-debut').val($(this).data('debut'));
    $('#sem-fin').val($(this).data('fin'));
    $('#modal-semestre').addClass('open');
  });

  // ── Enregistrer Semestre ───────────────────────────────────
  $('#btn-save-sem').on('click', function () {
    clearErrors(['sem-libelle']);

    const libelle  = $('#sem-libelle').val().trim();
    const anneeId  = $('#sem-annee-id').val();
    const debut    = $('#sem-debut').val();
    const fin      = $('#sem-fin').val();

    if (!libelle) { showFieldError('sem-libelle', 'Le libellé est obligatoire.'); return; }
    if (debut && fin && debut >= fin) { showFieldError('sem-libelle', 'Dates incohérentes (fin doit être après début).'); return; }

    setSaving('sem', true);

    const url  = semEditMode ? '/appeicg/semestres/modifier' : '/appeicg/semestres/ajouter';
    const data = {
      libelle_semestre:    libelle,
      annee_code:          anneeId,
      date_debut_semestre: debut,
      date_fin_semestre:   fin,
    };
    if (semEditMode) data.code_semestre = $('#sem-code').val();

    $.ajax({
      url, method: 'POST', data,
      success: function (res) {
        setSaving('sem', false);
        if (res.success) {
          showToast(res.message, 'success');
          closeModal('modal-semestre');
          loadSemestres(anneeId);
        } else {
          showToast(res.message || 'Erreur.', 'error');
        }
      },
      error: function (xhr) {
        setSaving('sem', false);
        const res = xhr.responseJSON;
        showToast(res?.message || 'Erreur lors de l\'enregistrement.', 'error');
      }
    });
  });

  // ── Supprimer Semestre ─────────────────────────────────────
  $(document).on('click', '.btn-del-sem', function (e) {
    e.stopPropagation();
    const code    = $(this).data('code');
    const anneeId = $(this).data('annee-id');
    if (!confirm('Supprimer ce semestre ? Cette action est irréversible.')) return;

    $.ajax({
      url: '/appeicg/semestres/supprimer', method: 'POST',
      data: { code_semestre: code },
      success: function (res) {
        if (res.success) { showToast(res.message, 'success'); loadSemestres(anneeId); }
        else { showToast(res.message, 'error'); }
      },
      error: function (xhr) {
        showToast(xhr.responseJSON?.message || 'Erreur lors de la suppression.', 'error');
      }
    });
  });

  // ═══════════════════════════════════════════════════════════
  // MODAL HELPERS
  // ═══════════════════════════════════════════════════════════

  function closeModal(id) { $('#' + id).removeClass('open'); }

  $(document).on('click', '[data-close]', function () {
    closeModal($(this).data('close'));
  });
  $(document).on('click', '.modal-backdrop', function (e) {
    if ($(e.target).hasClass('modal-backdrop')) {
      $(this).removeClass('open');
    }
  });

  function resetAnneeModal() {
    $('#annee-id, #annee-libelle, #annee-debut, #annee-fin').val('');
    clearErrors(['annee-libelle', 'annee-debut', 'annee-fin']);
    setSaving('annee', false);
  }

  function resetSemModal() {
    $('#sem-code, #sem-libelle, #sem-debut, #sem-fin').val('');
    clearErrors(['sem-libelle']);
    setSaving('sem', false);
  }

  function setSaving(prefix, saving) {
    $(`#btn-save-${prefix}`).prop('disabled', saving);
    $(`#save-${prefix}-text`).text(saving ? 'Enregistrement…' : 'Enregistrer');
    $(`#save-${prefix}-spinner`).toggle(saving);
  }

  // ═══════════════════════════════════════════════════════════
  // UTILS
  // ═══════════════════════════════════════════════════════════

  function clearErrors(fields) {
    fields.forEach(function (f) {
      $(`#err-${f}`).removeClass('show').text('');
      $(`#${f}`).removeClass('is-invalid');
    });
  }

  function showFieldError(field, msg) {
    $(`#err-${field}`).text(msg).addClass('show');
    $(`#${field}`).addClass('is-invalid');
  }

  function handleErrors(xhr, fieldMap) {
    const res = xhr.responseJSON;
    if (res && res.errors) {
      $.each(res.errors, function (field, msg) {
        const domId = fieldMap[field] || field;
        showFieldError(domId, msg);
      });
    } else {
      showToast(res?.message || 'Erreur lors de l\'enregistrement.', 'error');
    }
  }

  function formatDate(dateStr) {
    if (!dateStr) return '—';
    const [y, m, d] = dateStr.split('-');
    return `${d}/${m}/${y}`;
  }

  function escHtml(str) { return $('<div>').text(str || '').html(); }
  function escAttr(str) { return String(str || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }

});

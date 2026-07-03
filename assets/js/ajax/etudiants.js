/**
 * etudiants.js — Gestion étudiants, parents, dossiers (AJAX)
 * EICG Gestion Scolaire
 */

$(document).ready(function () {

  let editMode = false;
  let currentSearch = '';
  let currentStatut = '';

  // ═══════════════════════════════════════════════════════════
  // LISTE ÉTUDIANTS
  // ═══════════════════════════════════════════════════════════
  function loadEtudiants() {
    let url = '/appeicg/etudiants/liste?';
    if (currentSearch) url += `search=${encodeURIComponent(currentSearch)}&`;
    if (currentStatut) url += `statut=${currentStatut}`;

    $.get(url, function (res) {
      const etus = res.data || [];
      $('#etu-count').text(`${etus.length} étudiant${etus.length > 1 ? 's' : ''}`);
      const $tbody = $('#tbody-etu');
      $tbody.empty();

      if (!etus.length) {
        $tbody.html(`<tr><td colspan="7"><div class="empty-state"><p>Aucun étudiant trouvé.</p></div></td></tr>`);
        return;
      }

      etus.forEach(function (e) {
        const initials = (e.nom_etudiant[0] + (e.prenom_etudiant[0] || '')).toUpperCase();
        const badge    = e.statut_etudiant === 'actif' ? 'badge-success' : 'badge-danger';
        const toggle   = e.statut_etudiant === 'actif' ? 'inactif' : 'actif';
        const sexeLabel = e.sexe_etudiant === 'M' ? 'Masculin' : (e.sexe_etudiant === 'F' ? 'Féminin' : '—');
        const rowId    = `detail-row-${e.code_etudiant}`;

        $tbody.append(`
          <tr class="expandable" data-code="${e.code_etudiant}" data-target="${rowId}">
            <td><span class="expand-icon">▶</span></td>
            <td>
              <div class="d-flex align-center gap-2">
                <div class="etu-avatar">${initials}</div>
                <div>
                  <div class="fw-600">${esc(e.nom_etudiant)} ${esc(e.prenom_etudiant)}</div>
                  ${e.email_etudiant ? `<div class="text-sm text-muted">${esc(e.email_etudiant)}</div>` : ''}
                </div>
              </div>
            </td>
            <td class="text-sm text-muted">${esc(e.matricule_etudiant)}</td>
            <td>${e.telephone_etudiant ? esc(e.telephone_etudiant) : '<span class="text-muted">—</span>'}</td>
            <td class="text-sm">${sexeLabel}</td>
            <td><span class="badge ${badge}">${e.statut_etudiant === 'actif' ? 'Actif' : 'Inactif'}</span></td>
            <td>
              <div class="d-flex gap-2" onclick="event.stopPropagation()">
                <button class="btn btn-outline btn-sm btn-edit-etu" data-code="${e.code_etudiant}"
                  data-nom="${escA(e.nom_etudiant)}" data-prenom="${escA(e.prenom_etudiant)}"
                  data-sexe="${escA(e.sexe_etudiant || '')}" data-datenaissance="${e.date_naissance_etudiant || ''}"
                  data-lieunaissance="${escA(e.lieu_naissance_etudiant || '')}" data-nationalite="${escA(e.nationalite_etudiant || '')}"
                  data-telephone="${escA(e.telephone_etudiant || '')}" data-email="${escA(e.email_etudiant || '')}"
                  data-residence="${escA(e.lieu_residence_etudiant || '')}" data-cni="${escA(e.numero_cni || '')}">
                  Modifier
                </button>
                <button class="btn btn-sm ${e.statut_etudiant === 'actif' ? 'btn-danger' : 'btn-accent'} btn-toggle-etu"
                  data-code="${e.code_etudiant}" data-statut="${toggle}">
                  ${e.statut_etudiant === 'actif' ? 'Désactiver' : 'Activer'}
                </button>
              </div>
            </td>
          </tr>
          <tr class="detail-panel" id="${rowId}">
            <td colspan="7">
              <div class="detail-inner">
                <div class="detail-tabs">
                  <button class="dtab active" data-dtab="info-${e.code_etudiant}">Informations</button>
                  <button class="dtab" data-dtab="parent-${e.code_etudiant}">Parent / Tuteur</button>
                  <button class="dtab" data-dtab="dossier-${e.code_etudiant}">Dossier</button>
                </div>

                <div class="dtab-content active" id="info-${e.code_etudiant}">
                  <div class="info-grid">
                    <div class="info-item"><label>Date de naissance</label><span>${e.date_naissance_etudiant ? formatDate(e.date_naissance_etudiant) : '—'}</span></div>
                    <div class="info-item"><label>Lieu de naissance</label><span>${e.lieu_naissance_etudiant ? esc(e.lieu_naissance_etudiant) : '—'}</span></div>
                    <div class="info-item"><label>Nationalité</label><span>${e.nationalite_etudiant ? esc(e.nationalite_etudiant) : '—'}</span></div>
                    <div class="info-item"><label>Résidence</label><span>${e.lieu_residence_etudiant ? esc(e.lieu_residence_etudiant) : '—'}</span></div>
                    <div class="info-item"><label>N° CNI</label><span>${e.numero_cni ? esc(e.numero_cni) : '—'}</span></div>
                    <div class="info-item"><label>Inscrit le</label><span>${formatDate(e.created_at_etudiant)}</span></div>
                  </div>
                </div>

                <div class="dtab-content" id="parent-${e.code_etudiant}">
                  <div id="parent-view-${e.code_etudiant}"><p class="text-sm text-muted">Chargement…</p></div>
                </div>

                <div class="dtab-content" id="dossier-${e.code_etudiant}">
                  <div style="display:flex;justify-content:flex-end;margin-bottom:12px;">
                    <button class="btn btn-accent btn-sm btn-add-doc" data-code="${e.code_etudiant}" data-nom="${escA(e.nom_etudiant + ' ' + e.prenom_etudiant)}">+ Ajouter un document</button>
                  </div>
                  <div id="dossier-view-${e.code_etudiant}"><p class="text-sm text-muted">Chargement…</p></div>
                </div>
              </div>
            </td>
          </tr>
        `);
      });
    });
  }
  loadEtudiants();

  // ─── Recherche ─────────────────────────────────────────────
  $('#btn-search').on('click', function () {
    currentSearch = $('#search-etu').val().trim();
    currentStatut = $('#filter-statut').val();
    loadEtudiants();
  });
  $('#search-etu').on('keypress', function (e) { if (e.which === 13) $('#btn-search').trigger('click'); });

  // ─── Expand row ────────────────────────────────────────────
  $(document).on('click', '.expandable', function () {
    const $row = $(this); const target = $row.data('target'); const code = $row.data('code');
    const $panel = $('#' + target);
    if ($panel.hasClass('open')) { $panel.removeClass('open'); $row.removeClass('expanded'); return; }
    $('.detail-panel.open').removeClass('open'); $('.expandable.expanded').removeClass('expanded');
    $row.addClass('expanded'); $panel.addClass('open');
    loadParentView(code);
    loadDossierView(code);
  });

  // ─── Sous-onglets (info / parent / dossier) ──────────────
  $(document).on('click', '.dtab', function () {
    const target = $(this).data('dtab');
    const $parentTabs = $(this).closest('.detail-inner');
    $parentTabs.find('.dtab').removeClass('active');
    $parentTabs.find('.dtab-content').removeClass('active');
    $(this).addClass('active');
    $('#' + target).addClass('active');
  });

  // ═══════════════════════════════════════════════════════════
  // CRUD ÉTUDIANT
  // ═══════════════════════════════════════════════════════════
  $('#btn-add-etu').on('click', function () {
    editMode = false; resetEtu();
    $('#modal-etu-title').text('Nouvel étudiant');
    $('#modal-etu').addClass('open');
  });

  $(document).on('click', '.btn-edit-etu', function (e) {
    e.stopPropagation();
    editMode = true; resetEtu();
    $('#modal-etu-title').text('Modifier l\'étudiant');
    $('#etu-code').val($(this).data('code'));
    $('#etu-nom').val($(this).data('nom'));
    $('#etu-prenom').val($(this).data('prenom'));
    $('#etu-sexe').val($(this).data('sexe'));
    $('#etu-datenaissance').val($(this).data('datenaissance'));
    $('#etu-lieunaissance').val($(this).data('lieunaissance'));
    $('#etu-nationalite').val($(this).data('nationalite'));
    $('#etu-telephone').val($(this).data('telephone'));
    $('#etu-email').val($(this).data('email'));
    $('#etu-residence').val($(this).data('residence'));
    $('#etu-cni').val($(this).data('cni'));
    $('#modal-etu').addClass('open');
  });

  $('#btn-save-etu').on('click', function () {
    clearErr('etu-nom'); clearErr('etu-prenom'); clearErr('etu-email');
    const nom = $('#etu-nom').val().trim(); const prenom = $('#etu-prenom').val().trim();
    let ok = true;
    if (!nom)    { showErr('etu-nom', 'Le nom est obligatoire.'); ok = false; }
    if (!prenom) { showErr('etu-prenom', 'Le prénom est obligatoire.'); ok = false; }
    if (!ok) return;

    setSaving('etu', true);
    const code = $('#etu-code').val();
    const data = {
      nom_etudiant: nom, prenom_etudiant: prenom,
      sexe_etudiant: $('#etu-sexe').val(),
      date_naissance_etudiant: $('#etu-datenaissance').val(),
      lieu_naissance_etudiant: $('#etu-lieunaissance').val().trim(),
      nationalite_etudiant: $('#etu-nationalite').val().trim(),
      telephone_etudiant: $('#etu-telephone').val().trim(),
      email_etudiant: $('#etu-email').val().trim(),
      lieu_residence_etudiant: $('#etu-residence').val().trim(),
      numero_cni: $('#etu-cni').val().trim(),
    };
    if (code) data.code_etudiant = code;

    $.ajax({
      url: code ? '/appeicg/etudiants/modifier' : '/appeicg/etudiants/ajouter', method: 'POST', data,
      success: function (res) {
        setSaving('etu', false);
        if (res.success) {
          showToast(res.message + (res.data?.matricule_etudiant ? ` (Matricule : ${res.data.matricule_etudiant})` : ''), 'success');
          closeModal('modal-etu'); loadEtudiants();
        } else showToast(res.message, 'error');
      },
      error: function (xhr) {
        setSaving('etu', false);
        const res = xhr.responseJSON;
        if (res?.errors) {
          const map = { nom_etudiant: 'etu-nom', prenom_etudiant: 'etu-prenom', email_etudiant: 'etu-email' };
          $.each(res.errors, (f, m) => showErr(map[f] || f, m));
        } else showToast(res?.message || 'Erreur.', 'error');
      }
    });
  });

  $(document).on('click', '.btn-toggle-etu', function (e) {
    e.stopPropagation();
    const $btn = $(this); const code = $btn.data('code'); const statut = $btn.data('statut');
    if (!confirm(`${statut === 'actif' ? 'Activer' : 'Désactiver'} cet étudiant ?`)) return;
    $btn.prop('disabled', true);
    $.post('/appeicg/etudiants/statut', { code_etudiant: code, statut_etudiant: statut }, function (res) {
      if (res.success) { showToast(res.message, 'success'); loadEtudiants(); }
      else { showToast(res.message, 'error'); $btn.prop('disabled', false); }
    });
  });

  function resetEtu() {
    ['etu-code','etu-nom','etu-prenom','etu-datenaissance','etu-lieunaissance','etu-nationalite',
     'etu-telephone','etu-email','etu-residence','etu-cni'].forEach(id => $(`#${id}`).val(''));
    $('#etu-sexe').val('');
    clearErr('etu-nom'); clearErr('etu-prenom'); clearErr('etu-email');
    setSaving('etu', false);
  }

  // ═══════════════════════════════════════════════════════════
  // PARENT / TUTEUR
  // ═══════════════════════════════════════════════════════════
  function loadParentView(etuCode) {
    const $c = $(`#parent-view-${etuCode}`);
    $.get(`/appeicg/etudiants/parent?etudiant_code=${etuCode}`, function (res) {
      const p = res.data || {};
      const hasData = p.nom_pere || p.nom_mere || p.nom_tuteur;

      let html = '<div class="info-grid">';
      if (p.nom_pere) {
        html += `<div class="info-item"><label>Père</label><span>${esc(p.nom_pere)} ${p.telephone_pere ? '— ' + esc(p.telephone_pere) : ''}</span></div>`;
        if (p.profession_pere) html += `<div class="info-item"><label>Profession (père)</label><span>${esc(p.profession_pere)}</span></div>`;
      }
      if (p.nom_mere) {
        html += `<div class="info-item"><label>Mère</label><span>${esc(p.nom_mere)} ${p.telephone_mere ? '— ' + esc(p.telephone_mere) : ''}</span></div>`;
        if (p.profession_mere) html += `<div class="info-item"><label>Profession (mère)</label><span>${esc(p.profession_mere)}</span></div>`;
      }
      if (p.nom_tuteur) {
        html += `<div class="info-item"><label>Tuteur</label><span>${esc(p.nom_tuteur)} ${p.telephone_tuteur ? '— ' + esc(p.telephone_tuteur) : ''}</span></div>`;
      }
      html += '</div>';

      if (!hasData) html = '<p class="text-sm text-muted">Aucune information renseignée.</p>';

      html += `<button class="btn btn-outline btn-sm mt-3 btn-edit-parent" data-code="${etuCode}">
                 ${hasData ? 'Modifier' : 'Renseigner'} les informations
               </button>`;
      $c.html(html);
    });
  }

  $(document).on('click', '.btn-edit-parent', function () {
    const code = $(this).data('code');
    $('#parent-etu-code').val(code);
    $('#parent-etu-nom').text($(`.btn-edit-etu[data-code="${code}"]`).data('nom') + ' ' + $(`.btn-edit-etu[data-code="${code}"]`).data('prenom'));

    $.get(`/appeicg/etudiants/parent?etudiant_code=${code}`, function (res) {
      const p = res.data || {};
      $('#p-nom-pere').val(p.nom_pere || '');
      $('#p-tel-pere').val(p.telephone_pere || '');
      $('#p-prof-pere').val(p.profession_pere || '');
      $('#p-nom-mere').val(p.nom_mere || '');
      $('#p-tel-mere').val(p.telephone_mere || '');
      $('#p-prof-mere').val(p.profession_mere || '');
      $('#p-nom-tuteur').val(p.nom_tuteur || '');
      $('#p-tel-tuteur').val(p.telephone_tuteur || '');
      $('#modal-parent').addClass('open');
    });
  });

  $('#btn-save-parent').on('click', function () {
    setSaving('parent', true);
    const data = {
      etudiant_code: $('#parent-etu-code').val(),
      nom_pere: $('#p-nom-pere').val().trim(), telephone_pere: $('#p-tel-pere').val().trim(), profession_pere: $('#p-prof-pere').val().trim(),
      nom_mere: $('#p-nom-mere').val().trim(), telephone_mere: $('#p-tel-mere').val().trim(), profession_mere: $('#p-prof-mere').val().trim(),
      nom_tuteur: $('#p-nom-tuteur').val().trim(), telephone_tuteur: $('#p-tel-tuteur').val().trim(),
    };
    $.post('/appeicg/etudiants/parent/sauver', data, function (res) {
      setSaving('parent', false);
      if (res.success) {
        showToast(res.message, 'success');
        closeModal('modal-parent');
        loadParentView(data.etudiant_code);
      } else showToast(res.message, 'error');
    }).fail(function (xhr) {
      setSaving('parent', false);
      showToast(xhr.responseJSON?.message || 'Erreur.', 'error');
    });
  });

  // ═══════════════════════════════════════════════════════════
  // DOSSIER ÉTUDIANT
  // ═══════════════════════════════════════════════════════════
  function loadDossierView(etuCode) {
    const $c = $(`#dossier-view-${etuCode}`);
    $.get(`/appeicg/etudiants/dossiers?etudiant_code=${etuCode}`, function (res) {
      const docs = res.data || [];
      if (!docs.length) { $c.html('<p class="text-sm text-muted">Aucun document dans le dossier.</p>'); return; }
      let html = '';
      docs.forEach(function (d) {
        html += `
          <div class="doc-item">
            <div>
              <div class="doc-label">${esc(d.libelle_dossier)}</div>
              <div class="doc-meta">${d.annee_code ? esc(d.annee_code) + ' · ' : ''}Ajouté le ${formatDate(d.created_at_dossier_etudiant)}</div>
            </div>
            <button class="btn btn-danger btn-sm btn-del-doc" data-code="${d.code_dossier_etudiant}" data-etu="${etuCode}">Supprimer</button>
          </div>
        `;
      });
      $c.html(html);
    });
  }

  $(document).on('click', '.btn-add-doc', function () {
    const code = $(this).data('code'); const nom = $(this).data('nom');
    $('#dos-etu-code').val(code);
    $('#dos-libelle').val('');
    clearErr('dos-libelle');

    // Charger les années
    $.get('/appeicg/annees/liste', function (res) {
      const $sel = $('#dos-annee');
      $sel.find('option:not(:first)').remove();
      (res.data || []).forEach(a => $sel.append(`<option value="${a.id_annee}">${esc(a.libelle_annee)}</option>`));
    });

    $('#modal-dossier').addClass('open');
  });

  $('#btn-save-dos').on('click', function () {
    clearErr('dos-libelle');
    const libelle = $('#dos-libelle').val().trim();
    if (!libelle) { showErr('dos-libelle', 'Le libellé est obligatoire.'); return; }
    setSaving('dos', true);

    const etuCode = $('#dos-etu-code').val();
    $.post('/appeicg/etudiants/dossiers/ajouter', {
      etudiant_code: etuCode, libelle_dossier: libelle, annee_id: $('#dos-annee').val()
    }, function (res) {
      setSaving('dos', false);
      if (res.success) {
        showToast(res.message, 'success');
        closeModal('modal-dossier');
        loadDossierView(etuCode);
      } else showToast(res.message, 'error');
    }).fail(function (xhr) {
      setSaving('dos', false);
      showToast(xhr.responseJSON?.message || 'Erreur.', 'error');
    });
  });

  $(document).on('click', '.btn-del-doc', function () {
    const code = $(this).data('code'); const etu = $(this).data('etu');
    if (!confirm('Supprimer ce document du dossier ?')) return;
    $.post('/appeicg/etudiants/dossiers/supprimer', { code_dossier_etudiant: code }, function (res) {
      if (res.success) { showToast(res.message, 'success'); loadDossierView(etu); }
      else showToast(res.message, 'error');
    });
  });

  // ═══════════════════════════════════════════════════════════
  // HELPERS
  // ═══════════════════════════════════════════════════════════
  function closeModal(id) { $('#' + id).removeClass('open'); }
  $(document).on('click', '[data-close]', function () { closeModal($(this).data('close')); });
  $(document).on('click', '.modal-backdrop', function (e) { if ($(e.target).hasClass('modal-backdrop')) $(this).removeClass('open'); });

  function setSaving(p, s) {
    $(`#btn-save-${p}`).prop('disabled', s);
    $(`#save-${p}-text`).text(s ? 'Enregistrement…' : (p === 'dos' ? 'Ajouter' : 'Enregistrer'));
    $(`#save-${p}-spinner`).toggle(s);
  }
  function clearErr(id) { $(`#err-${id}`).removeClass('show').text(''); $(`#${id}`).removeClass('is-invalid'); }
  function showErr(id, msg) { $(`#err-${id}`).text(msg).addClass('show'); $(`#${id}`).addClass('is-invalid'); }
  function formatDate(dt) {
    if (!dt) return '—';
    const d = new Date(dt.replace(' ', 'T'));
    if (isNaN(d)) return dt.split(' ')[0].split('-').reverse().join('/');
    return d.toLocaleDateString('fr-FR');
  }
  function esc(s) { return $('<div>').text(s || '').html(); }
  function escA(s) { return String(s || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }
});

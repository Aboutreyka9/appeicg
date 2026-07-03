/**
 * inscriptions.js — Inscriptions & Accessoires (AJAX)
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
    if (target === 'tab-accessoires') loadAccessoires();
  });

  // ═══════════════════════════════════════════════════════════
  // CHARGEMENT DES SELECTS (filtres + modal)
  // ═══════════════════════════════════════════════════════════
  function loadSelectsGlobal() {
    // Années
    $.get('/api/annees/liste', function (res) {
      const opts = (res.data || []).map(a => `<option value="${a.libelle_annee}">${esc(a.libelle_annee)}</option>`).join('');
      $('#f-annee').find('option:not(:first)').remove().end().append(opts);
      const optsModal = (res.data || []).map(a => `<option value="${a.id_annee}">${esc(a.libelle_annee)}</option>`).join('');
      $('#ins-annee').find('option:not(:first)').remove().end().append(optsModal);
    });

    // Classes
    $.get('/api/classes/liste', function (res) {
      const opts = (res.data || []).map(c => `<option value="${c.code_classe}">${esc(c.libelle_classe)}</option>`).join('');
      $('#f-classe').find('option:not(:first)').remove().end().append(opts);
    });

    // Étudiants actifs
    $.get('/api/etudiants/liste?statut=actif', function (res) {
      const opts = (res.data || []).map(e =>
        `<option value="${e.code_etudiant}">${esc(e.nom_etudiant)} ${esc(e.prenom_etudiant)} (${esc(e.matricule_etudiant)})</option>`
      ).join('');
      $('#ins-etudiant').find('option:not(:first)').remove().end().append(opts);
    });

    // Filières
    $.get('/api/filieres/liste', function (res) {
      const opts = (res.data || []).filter(f => f.statut_filiere === 'actif')
        .map(f => `<option value="${f.code_filiere}">${esc(f.libelle_filiere)}</option>`).join('');
      $('#ins-filiere').find('option:not(:first)').remove().end().append(opts);
    });
  }
  loadSelectsGlobal();

  // Chargement dynamique classes selon filière dans le modal
  $('#ins-filiere').on('change', function () {
    const filiere = $(this).val();
    const anneeId = $('#ins-annee').val();
    const $cls    = $('#ins-classe');
    $cls.find('option:not(:first)').remove().prop('disabled', !filiere);
    if (!filiere) return;
    let url = `/api/classes/liste?`;
    if (anneeId) url += `annee_code=${anneeId}&`;
    $.get(url, function (res) {
      (res.data || []).filter(c => c.code_filiere === filiere && c.statut_classe === 'actif').forEach(c => {
        $cls.append(`<option value="${c.code_classe}">${esc(c.libelle_classe)}</option>`);
      });
    });
  });

  // ═══════════════════════════════════════════════════════════
  // INSCRIPTIONS
  // ═══════════════════════════════════════════════════════════
  function loadInscriptions() {
    const params = new URLSearchParams({
      annee_code:  $('#f-annee').val(),
      classe_code: $('#f-classe').val(),
      statut:      $('#f-statut').val(),
      search:      $('#f-search').val().trim(),
    });

    $.get('/api/inscriptions/liste?' + params.toString(), function (res) {
      const ins = res.data || [];
      $('#ins-count').text(`${ins.length} inscription${ins.length > 1 ? 's' : ''}`);
      const $tbody = $('#tbody-ins');
      $tbody.empty();

      if (!ins.length) {
        $tbody.html(`<tr><td colspan="9"><div class="empty-state"><p>Aucune inscription trouvée.</p></div></td></tr>`);
        return;
      }

      ins.forEach(function (i) {
        const rowId = `ins-row-${i.code_inscription}`;
        const statClass = { valide: 'statut-badge-valide', solde: 'statut-badge-solde', annule: 'statut-badge-annule' }[i.statut_inscription] || '';
        const statLabel = { valide: 'Valide', solde: 'Soldé', annule: 'Annulé' }[i.statut_inscription] || i.statut_inscription;
        const montant   = parseFloat(i.montant_scolarite_inscription || 0).toLocaleString('fr-FR') + ' FCFA';

        $tbody.append(`
          <tr class="expandable ins-row" data-code="${i.code_inscription}" data-target="${rowId}">
            <td><span class="expand-icon">▶</span></td>
            <td>
              <div class="fw-600">${esc(i.nom_etudiant)} ${esc(i.prenom_etudiant)}</div>
            </td>
            <td class="text-sm text-muted">${esc(i.matricule_etudiant)}</td>
            <td>${esc(i.libelle_classe)}</td>
            <td class="text-sm">${esc(i.libelle_filiere)}</td>
            <td class="text-sm">${esc(i.annee_code)}</td>
            <td class="fw-600">${montant}</td>
            <td><span class="badge ${statClass}">${statLabel}</span></td>
            <td>
              <div class="d-flex gap-2" onclick="event.stopPropagation()">
                <button class="btn btn-outline btn-sm btn-edit-montant"
                  data-code="${i.code_inscription}" data-montant="${i.montant_scolarite_inscription}">
                  Montant
                </button>
                ${i.statut_inscription !== 'annule' ? `
                <div class="d-flex gap-2">
                  ${i.statut_inscription !== 'solde'  ? `<button class="btn btn-sm btn-primary btn-statut-ins" data-code="${i.code_inscription}" data-statut="solde">Soldé</button>` : ''}
                  <button class="btn btn-sm btn-danger btn-statut-ins" data-code="${i.code_inscription}" data-statut="annule">Annuler</button>
                </div>` : ''}
              </div>
            </td>
          </tr>
          <tr class="ins-panel" id="${rowId}">
            <td colspan="9">
              <div class="ins-inner">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                  <span class="fw-600 text-sm">Accessoires — ${esc(i.nom_etudiant)} ${esc(i.prenom_etudiant)}</span>
                  <button class="btn btn-accent btn-sm btn-add-ins-acc"
                    data-ins-code="${i.code_inscription}" data-annee="${esc(i.annee_code)}">
                    + Ajouter un accessoire
                  </button>
                </div>
                <div id="acc-list-${i.code_inscription}"><p class="text-sm text-muted">Cliquez pour charger…</p></div>
              </div>
            </td>
          </tr>
        `);
      });
    });
  }
  loadInscriptions();

  $('#btn-filter-ins').on('click', loadInscriptions);
  $('#f-search').on('keypress', function (e) { if (e.which === 13) loadInscriptions(); });

  // Expand → accessoires
  $(document).on('click', '.expandable', function () {
    const $row = $(this); const target = $row.data('target'); const code = $row.data('code');
    const $panel = $('#' + target);
    if ($panel.hasClass('open')) { $panel.removeClass('open'); $row.removeClass('expanded'); return; }
    $('.ins-panel.open').removeClass('open'); $('.expandable.expanded').removeClass('expanded');
    $row.addClass('expanded'); $panel.addClass('open');
    loadInsAccessoires(code);
  });

  // ─── Modal Nouvelle inscription ────────────────────────────
  $('#btn-add-ins').on('click', function () {
    resetIns();
    $('#modal-ins').addClass('open');
  });

  $('#btn-save-ins').on('click', function () {
    clearErr('ins-annee'); clearErr('ins-etudiant'); clearErr('ins-classe');
    const anneeId  = $('#ins-annee').val();
    const etu      = $('#ins-etudiant').val();
    const classe   = $('#ins-classe').val();
    let ok = true;
    if (!anneeId) { showErr('ins-annee',   'L\'année est obligatoire.'); ok = false; }
    if (!etu)     { showErr('ins-etudiant','L\'étudiant est obligatoire.'); ok = false; }
    if (!classe)  { showErr('ins-classe',  'La classe est obligatoire.'); ok = false; }
    if (!ok) return;

    setSaving('ins', true);
    $.post('/api/inscriptions/ajouter', {
      etudiant_code: etu, classe_code: classe, annee_id: anneeId,
      montant_scolarite_inscription: $('#ins-montant').val() || 0,
    }, function (res) {
      setSaving('ins', false);
      if (res.success) { showToast(res.message, 'success'); closeModal('modal-ins'); loadInscriptions(); }
      else showToast(res.message, 'error');
    }).fail(function (xhr) {
      setSaving('ins', false);
      const res = xhr.responseJSON;
      if (res?.errors) { $.each(res.errors, (f, m) => showErr('ins-' + f.replace('_code', ''), m)); }
      else showToast(res?.message || 'Erreur.', 'error');
    });
  });

  // ─── Modifier montant ──────────────────────────────────────
  $(document).on('click', '.btn-edit-montant', function (e) {
    e.stopPropagation();
    $('#mont-ins-code').val($(this).data('code'));
    $('#mont-valeur').val($(this).data('montant'));
    $('#modal-montant').addClass('open');
  });

  $('#btn-save-mont').on('click', function () {
    setSaving('mont', true);
    $.post('/api/inscriptions/modifier-montant', {
      code_inscription: $('#mont-ins-code').val(),
      montant_scolarite_inscription: $('#mont-valeur').val(),
    }, function (res) {
      setSaving('mont', false);
      if (res.success) { showToast(res.message, 'success'); closeModal('modal-montant'); loadInscriptions(); }
      else showToast(res.message, 'error');
    }).fail(function (xhr) { setSaving('mont', false); showToast(xhr.responseJSON?.message || 'Erreur.', 'error'); });
  });

  // ─── Changer statut inscription ────────────────────────────
  $(document).on('click', '.btn-statut-ins', function (e) {
    e.stopPropagation();
    const code   = $(this).data('code');
    const statut = $(this).data('statut');
    const labels = { solde: 'marquer comme soldé', annule: 'annuler' };
    if (!confirm(`Voulez-vous vraiment ${labels[statut] || statut} cette inscription ?`)) return;
    $(this).prop('disabled', true);
    $.post('/api/inscriptions/statut', { code_inscription: code, statut_inscription: statut }, function (res) {
      if (res.success) { showToast(res.message, 'success'); loadInscriptions(); }
      else showToast(res.message, 'error');
    });
  });

  // ═══════════════════════════════════════════════════════════
  // ACCESSOIRES D'UNE INSCRIPTION
  // ═══════════════════════════════════════════════════════════
  function loadInsAccessoires(insCode) {
    const $c = $(`#acc-list-${insCode}`);
    $.get(`/api/inscriptions/accessoires?inscription_code=${insCode}`, function (res) {
      const accs = res.data || [];
      if (!accs.length) { $c.html('<p class="text-sm text-muted">Aucun accessoire associé.</p>'); return; }
      let html = '<div style="display:flex;flex-wrap:wrap;gap:8px;">';
      accs.forEach(function (a) {
        html += `<span class="badge badge-info" style="font-size:.8rem;padding:5px 12px;">
                   ${esc(a.libelle_accessoire)}
                   <span class="btn-ret-acc" data-code="${a.code_accessoire_inscription}" data-ins="${insCode}"
                         style="cursor:pointer;margin-left:6px;color:var(--danger);font-weight:700;" title="Retirer">×</span>
                 </span>`;
      });
      html += '</div>';
      $c.html(html);
    });
  }

  $(document).on('click', '.btn-add-ins-acc', function (e) {
    e.stopPropagation();
    const insCode = $(this).data('ins-code');
    // Ouvrir un mini-modal via confirm-like avec select
    $.get('/api/accessoires/liste', function (res) {
      const actifs = (res.data || []).filter(a => a.statut_accessoire === 'actif');
      if (!actifs.length) { showToast('Aucun accessoire disponible. Créez-en d\'abord dans l\'onglet Accessoires.', 'warning'); return; }

      const opts = actifs.map(a => `<option value="${a.code_accessoire}">${esc(a.libelle_accessoire)}</option>`).join('');
      // Utiliser un petit modal inline
      const chosen = window.prompt(
        'Choisissez un accessoire (entrez son numéro) :\n' +
        actifs.map((a, i) => `${i + 1}. ${a.libelle_accessoire}`).join('\n')
      );
      if (!chosen) return;
      const idx = parseInt(chosen, 10) - 1;
      if (isNaN(idx) || idx < 0 || idx >= actifs.length) { showToast('Sélection invalide.', 'error'); return; }

      $.post('/api/inscriptions/accessoires/ajouter', {
        inscription_code: insCode, accessoire_code: actifs[idx].code_accessoire
      }, function (res) {
        if (res.success) { showToast(res.message, 'success'); loadInsAccessoires(insCode); }
        else showToast(res.message, 'error');
      }).fail(function (xhr) { showToast(xhr.responseJSON?.message || 'Erreur.', 'error'); });
    });
  });

  $(document).on('click', '.btn-ret-acc', function (e) {
    e.stopPropagation();
    const code = $(this).data('code'); const ins = $(this).data('ins');
    if (!confirm('Retirer cet accessoire ?')) return;
    $.post('/api/inscriptions/accessoires/retirer', { code_accessoire_inscription: code }, function (res) {
      if (res.success) { showToast(res.message, 'success'); loadInsAccessoires(ins); }
      else showToast(res.message, 'error');
    });
  });

  // ═══════════════════════════════════════════════════════════
  // ACCESSOIRES (référentiel)
  // ═══════════════════════════════════════════════════════════
  function loadAccessoires() {
    $.get('/api/accessoires/liste', function (res) {
      const accs = res.data || [];
      const $tbody = $('#tbody-acc');
      $tbody.empty();
      if (!accs.length) { $tbody.html(`<tr><td colspan="4"><div class="empty-state"><p>Aucun accessoire.</p></div></td></tr>`); return; }
      accs.forEach(function (a) {
        const badge = a.statut_accessoire === 'actif' ? 'badge-success' : 'badge-danger';
        $tbody.append(`
          <tr>
            <td class="fw-600">${esc(a.libelle_accessoire)}</td>
            <td class="text-sm text-muted">${esc(a.code_accessoire)}</td>
            <td><span class="badge ${badge}">${a.statut_accessoire === 'actif' ? 'Actif' : 'Inactif'}</span></td>
            <td>
              <div class="d-flex gap-2">
                <button class="btn btn-outline btn-sm btn-edit-acc" data-code="${a.code_accessoire}" data-libelle="${escA(a.libelle_accessoire)}">Modifier</button>
                <button class="btn btn-sm ${a.statut_accessoire === 'actif' ? 'btn-danger' : 'btn-accent'} btn-toggle-acc"
                  data-code="${a.code_accessoire}" data-statut="${a.statut_accessoire === 'actif' ? 'inactif' : 'actif'}">
                  ${a.statut_accessoire === 'actif' ? 'Désactiver' : 'Activer'}
                </button>
              </div>
            </td>
          </tr>
        `);
      });
    });
  }

  $('#btn-add-acc').on('click', function () {
    $('#acc-code, #acc-libelle').val(''); clearErr('acc-libelle');
    $('#modal-acc-title').text('Nouvel accessoire');
    $('#modal-acc').addClass('open');
  });

  $(document).on('click', '.btn-edit-acc', function () {
    $('#acc-code').val($(this).data('code'));
    $('#acc-libelle').val($(this).data('libelle'));
    $('#modal-acc-title').text('Modifier l\'accessoire');
    $('#modal-acc').addClass('open');
  });

  $('#btn-save-acc').on('click', function () {
    const code = $('#acc-code').val(); const libelle = $('#acc-libelle').val().trim();
    clearErr('acc-libelle');
    if (!libelle) { showErr('acc-libelle', 'Le libellé est obligatoire.'); return; }
    setSaving('acc', true);
    const data = { libelle_accessoire: libelle };
    if (code) data.code_accessoire = code;
    $.ajax({
      url: code ? '/api/accessoires/modifier' : '/api/accessoires/ajouter', method: 'POST', data,
      success: function (res) {
        setSaving('acc', false);
        if (res.success) { showToast(res.message, 'success'); closeModal('modal-acc'); loadAccessoires(); }
        else showToast(res.message, 'error');
      },
      error: function (xhr) { setSaving('acc', false); showToast(xhr.responseJSON?.message || 'Erreur.', 'error'); }
    });
  });

  $(document).on('click', '.btn-toggle-acc', function () {
    const $btn = $(this); const code = $btn.data('code'); const statut = $btn.data('statut');
    if (!confirm(`${statut === 'actif' ? 'Activer' : 'Désactiver'} cet accessoire ?`)) return;
    $btn.prop('disabled', true);
    $.post('/api/accessoires/statut', { code_accessoire: code, statut_accessoire: statut }, function (res) {
      if (res.success) { showToast(res.message, 'success'); loadAccessoires(); }
      else { showToast(res.message, 'error'); $btn.prop('disabled', false); }
    });
  });

  // ═══════════════════════════════════════════════════════════
  // HELPERS
  // ═══════════════════════════════════════════════════════════
  function resetIns() {
    ['ins-annee','ins-etudiant','ins-filiere','ins-montant'].forEach(id => $(`#${id}`).val(''));
    $('#ins-classe').find('option:not(:first)').remove().val('');
    clearErr('ins-annee'); clearErr('ins-etudiant'); clearErr('ins-classe');
    setSaving('ins', false);
  }
  function closeModal(id) { $('#' + id).removeClass('open'); }
  $(document).on('click', '[data-close]', function () { closeModal($(this).data('close')); });
  $(document).on('click', '.modal-backdrop', function (e) { if ($(e.target).hasClass('modal-backdrop')) $(this).removeClass('open'); });
  function setSaving(p, s) {
    $(`#btn-save-${p}`).prop('disabled', s);
    const txt = p === 'ins' ? (s ? 'Inscription…' : 'Inscrire') : (s ? 'Enregistrement…' : 'Enregistrer');
    $(`#save-${p}-text`).text(txt);
    $(`#save-${p}-spinner`).toggle(s);
  }
  function clearErr(id) { $(`#err-${id}`).removeClass('show').text(''); $(`#${id}`).removeClass('is-invalid'); }
  function showErr(id, msg) { $(`#err-${id}`).text(msg).addClass('show'); $(`#${id}`).addClass('is-invalid'); }
  function esc(s) { return $('<div>').text(s || '').html(); }
  function escA(s) { return String(s || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }
});

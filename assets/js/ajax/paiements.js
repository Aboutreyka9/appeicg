/**
 * paiements.js — Paiements & Scolarité (AJAX)
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
    if (target === 'tab-scolarites') loadScolarites();
  });

  // ═══════════════════════════════════════════════════════════
  // CHARGEMENT SELECTS GLOBAUX
  // ═══════════════════════════════════════════════════════════
  function loadSelects() {
    $.get('/api/annees/liste', function (res) {
      const annees = res.data || [];
      const opts   = annees.map(a => `<option value="${a.libelle_annee}">${esc(a.libelle_annee)}</option>`).join('');
      const optsId = annees.map(a => `<option value="${a.libelle_annee}">${esc(a.libelle_annee)}</option>`).join('');
      $('#f-pay-annee, #pay-annee, #f-sco-annee').find('option:not(:first)').remove().end().append(opts);
      $('#sco-annee').find('option:not(:first)').remove().end().append(optsId);

      // Sélectionner la première année active par défaut dans les stats
      const actif = annees.find(a => a.statut_annee === 'actif');
      if (actif) {
        $('#f-pay-annee').val(actif.libelle_annee);
        loadStats(actif.libelle_annee);
      }
    });

    // Inscriptions pour le modal paiement
    $.get('/api/inscriptions/liste', function (res) {
      const ins = res.data || [];
      const opts = ins.filter(i => i.statut_inscription !== 'annule').map(i =>
        `<option value="${i.code_inscription}" data-annee="${escA(i.annee_code)}" data-montant="${i.montant_scolarite_inscription}">
           ${esc(i.nom_etudiant)} ${esc(i.prenom_etudiant)} — ${esc(i.libelle_classe)} (${esc(i.annee_code)})
         </option>`
      ).join('');
      $('#pay-inscription').find('option:not(:first)').remove().end().append(opts);
    });

    // Filières pour grille tarifaire
    $.get('/api/filieres/liste', function (res) {
      const opts = (res.data || []).filter(f => f.statut_filiere === 'actif')
        .map(f => `<option value="${f.code_filiere}">${esc(f.libelle_filiere)}</option>`).join('');
      $('#sco-filiere').find('option:not(:first)').remove().end().append(opts);
    });
  }
  loadSelects();

  // Chargement dynamique niveaux selon filière
  $('#sco-filiere').on('change', function () {
    const fil = $(this).val();
    const $niv = $('#sco-niveau');
    $niv.find('option:not(:first)').remove().prop('disabled', !fil);
    if (!fil) return;
    $.get(`/api/niveaux/liste?filiere_code=${fil}`, function (res) {
      (res.data || []).filter(n => n.statut_niveau === 'actif').forEach(n => {
        $niv.append(`<option value="${n.code_niveau}">${esc(n.libelle_niveau)}</option>`);
      });
    });
  });

  // Afficher infos inscription sélectionnée
  $('#pay-inscription').on('change', function () {
    const $opt     = $(this).find(':selected');
    const annee    = $opt.data('annee') || '';
    const montant  = parseFloat($opt.data('montant') || 0);
    const insCode  = $(this).val();

    if (insCode) {
      $('#pay-annee').val(annee);
      // Afficher le reste à payer
      $.get(`/api/paiements/by-inscription?inscription_code=${insCode}`, function (res) {
        const total    = parseFloat(res.data?.total || 0);
        const restant  = Math.max(montant - total, 0);
        const info     = montant > 0
          ? `Scolarité : <strong>${fmt(montant)} FCFA</strong> · Payé : <strong>${fmt(total)} FCFA</strong> · Restant : <strong style="color:${restant > 0 ? 'var(--danger)' : 'var(--success)'}">${fmt(restant)} FCFA</strong>`
          : '';
        $('#pay-ins-info').html(info);
        if (restant > 0) $('#pay-montant').val(restant);
      });
    } else {
      $('#pay-ins-info').html('');
    }
  });

  // ═══════════════════════════════════════════════════════════
  // KPIs
  // ═══════════════════════════════════════════════════════════
  function loadStats(anneeCode) {
    $.get(`/api/paiements/stats?annee_code=${encodeURIComponent(anneeCode || '')}`, function (res) {
      const total = parseFloat(res.data?.total_confirme || 0);
      $('#kpi-total').text(fmt(total) + ' FCFA');
    });
  }

  // ═══════════════════════════════════════════════════════════
  // PAIEMENTS
  // ═══════════════════════════════════════════════════════════
  function loadPaiements() {
    const params = new URLSearchParams({
      annee_code:    $('#f-pay-annee').val(),
      statut:        $('#f-pay-statut').val(),
      type_paiement: $('#f-pay-type').val(),
      search:        $('#f-pay-search').val().trim(),
    });

    $.get('/api/paiements/liste?' + params.toString(), function (res) {
      const pays = res.data || [];
      $('#pay-count').text(`${pays.length} paiement${pays.length > 1 ? 's' : ''}`);
      $('#kpi-nb').text(pays.filter(p => p.statut_paiement === 'confirme').length);

      const $tbody = $('#tbody-pay');
      $tbody.empty();

      if (!pays.length) {
        $tbody.html(`<tr><td colspan="9"><div class="empty-state"><p>Aucun paiement trouvé.</p></div></td></tr>`);
        return;
      }

      pays.forEach(function (p) {
        const statutClass = p.statut_paiement === 'confirme' ? 'badge-success' : 'badge-danger';
        const statutLabel = { confirme: 'Confirmé', annule: 'Annulé', en_attente: 'En attente', rembourse: 'Remboursé', echoue: 'Échoué' }[p.statut_paiement] || p.statut_paiement;
        const etudiant    = p.nom_etudiant ? `${esc(p.nom_etudiant)} ${esc(p.prenom_etudiant)}` : '<span class="text-muted">—</span>';
        const typeLabel   = { scolarite: 'Scolarité', inscription: 'Inscription', accessoire: 'Accessoire', autre: 'Autre' }[p.type_paiement] || esc(p.type_paiement);
        const modeLabel   = { especes: 'Espèces', mobile_money: 'Mobile Money', virement: 'Virement', cheque: 'Chèque', carte: 'Carte' }[p.mode_paiement] || esc(p.mode_paiement);
        const date        = new Date(p.date_paiement).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });

        $tbody.append(`
          <tr>
            <td class="text-sm text-muted">${esc(p.code_paiement)}</td>
            <td>
              <div>${etudiant}</div>
              ${p.matricule_etudiant ? `<div class="text-sm text-muted">${esc(p.matricule_etudiant)}</div>` : ''}
            </td>
            <td class="fw-600" style="color:var(--success)">${fmt(p.montant_paiement)} FCFA</td>
            <td class="text-sm">${typeLabel}</td>
            <td class="text-sm">${modeLabel}</td>
            <td class="text-sm">${esc(p.annee_code || '—')}</td>
            <td class="text-sm">${date}</td>
            <td><span class="badge ${statutClass}">${statutLabel}</span></td>
            <td>
              ${p.statut_paiement === 'confirme'
                ? `<button class="btn btn-danger btn-sm btn-annuler-pay" data-code="${p.code_paiement}">Annuler</button>`
                : ''}
            </td>
          </tr>
        `);
      });

      // Mettre à jour les KPIs
      const annee = $('#f-pay-annee').val();
      if (annee) loadStats(annee);
    });
  }
  loadPaiements();

  $('#btn-filter-pay').on('click', loadPaiements);
  $('#f-pay-search').on('keypress', function (e) { if (e.which === 13) loadPaiements(); });
  $('#f-pay-annee').on('change', function () { loadStats($(this).val()); loadPaiements(); });

  // ─── Enregistrer paiement ─────────────────────────────────
  $('#btn-add-pay').on('click', function () {
    resetPay();
    $('#modal-pay').addClass('open');
  });

  $('#btn-save-pay').on('click', function () {
    clearErr('pay-montant'); clearErr('pay-type'); clearErr('pay-mode');
    const montant = $('#pay-montant').val();
    const type    = $('#pay-type').val();
    const mode    = $('#pay-mode').val();
    let ok = true;
    if (!montant || parseFloat(montant) <= 0) { showErr('pay-montant', 'Montant invalide.'); ok = false; }
    if (!type)    { showErr('pay-type', 'Le type est obligatoire.'); ok = false; }
    if (!mode)    { showErr('pay-mode', 'Le mode est obligatoire.'); ok = false; }
    if (!ok) return;

    setSaving('pay', true);
    $.post('/api/paiements/enregistrer', {
      montant_paiement:  montant,
      type_paiement:     type,
      mode_paiement:     mode,
      inscription_code:  $('#pay-inscription').val(),
      annee_code:        $('#pay-annee').val(),
      observations:      $('#pay-obs').val().trim(),
    }, function (res) {
      setSaving('pay', false);
      if (res.success) {
        showToast(res.message, 'success');
        closeModal('modal-pay');
        loadPaiements();
      } else showToast(res.message, 'error');
    }).fail(function (xhr) {
      setSaving('pay', false);
      const res = xhr.responseJSON;
      if (res?.errors) { $.each(res.errors, (f, m) => showErr('pay-' + f.replace('_paiement', ''), m)); }
      else showToast(res?.message || 'Erreur.', 'error');
    });
  });

  // ─── Annuler paiement ─────────────────────────────────────
  $(document).on('click', '.btn-annuler-pay', function () {
    const code = $(this).data('code');
    if (!confirm('Annuler ce paiement ? Cette action est irréversible.')) return;
    $(this).prop('disabled', true);
    $.post('/api/paiements/annuler', { code_paiement: code }, function (res) {
      if (res.success) { showToast(res.message, 'success'); loadPaiements(); }
      else showToast(res.message, 'error');
    });
  });

  // ═══════════════════════════════════════════════════════════
  // GRILLE TARIFAIRE
  // ═══════════════════════════════════════════════════════════
  function loadScolarites() {
    const annee = $('#f-sco-annee').val();
    const url   = '/api/scolarites/liste' + (annee ? `?annee_code=${encodeURIComponent(annee)}` : '');
    $.get(url, function (res) {
      const scos   = res.data || [];
      const $tbody = $('#tbody-sco');
      $tbody.empty();
      if (!scos.length) {
        $tbody.html(`<tr><td colspan="5"><div class="empty-state"><p>Aucun tarif défini.</p></div></td></tr>`);
        return;
      }
      scos.forEach(function (s) {
        $tbody.append(`
          <tr>
            <td class="fw-600">${esc(s.libelle_filiere || '—')}</td>
            <td>${esc(s.libelle_niveau || '—')}</td>
            <td class="text-sm text-muted">${esc(s.annee_code || '—')}</td>
            <td class="fw-600" style="color:var(--primary)">${fmt(s.montant_scolarite)} FCFA</td>
            <td>
              <div class="d-flex gap-2">
                <button class="btn btn-outline btn-sm btn-edit-sco"
                  data-code="${s.code_scolarite}"
                  data-montant="${s.montant_scolarite}"
                  data-filiere="${s.filiere_code}"
                  data-niveau="${s.niveau_code}"
                  data-annee="${escA(s.annee_code || '')}">
                  Modifier
                </button>
                <button class="btn btn-danger btn-sm btn-del-sco" data-code="${s.code_scolarite}">Supprimer</button>
              </div>
            </td>
          </tr>
        `);
      });
    });
  }

  $('#f-sco-annee').on('change', loadScolarites);

  // ─── Ajouter tarif ────────────────────────────────────────
  $('#btn-add-sco').on('click', function () {
    resetSco();
    $('#modal-sco-title').text('Nouveau tarif');
    $('#modal-sco').addClass('open');
  });

  $(document).on('click', '.btn-edit-sco', function () {
    resetSco();
    $('#modal-sco-title').text('Modifier le tarif');
    $('#sco-code').val($(this).data('code'));
    $('#sco-montant').val($(this).data('montant'));
    $('#sco-annee').val($(this).data('annee'));

    const filiere = $(this).data('filiere');
    const niveau  = $(this).data('niveau');
    if (filiere) {
      $('#sco-filiere').val(filiere).trigger('change');
      setTimeout(() => $('#sco-niveau').val(niveau), 500);
    }
    $('#modal-sco').addClass('open');
  });

  $('#btn-save-sco').on('click', function () {
    ['sco-filiere','sco-niveau','sco-annee','sco-montant'].forEach(id => clearErr(id));
    const code    = $('#sco-code').val();
    const filiere = $('#sco-filiere').val();
    const niveau  = $('#sco-niveau').val();
    const annee   = $('#sco-annee').val();
    const montant = $('#sco-montant').val();
    let ok = true;
    if (!filiere) { showErr('sco-filiere', 'La filière est obligatoire.'); ok = false; }
    if (!niveau)  { showErr('sco-niveau',  'Le niveau est obligatoire.');  ok = false; }
    if (!annee)   { showErr('sco-annee',   'L\'année est obligatoire.');   ok = false; }
    if (!montant || parseFloat(montant) <= 0) { showErr('sco-montant', 'Montant invalide.'); ok = false; }
    if (!ok) return;

    setSaving('sco', true);
    const data = { filiere_code: filiere, niveau_code: niveau, annee_code: annee, montant_scolarite: montant };
    if (code) data.code_scolarite = code;
    $.ajax({
      url: code ? '/api/scolarites/modifier' : '/api/scolarites/ajouter', method: 'POST', data,
      success: function (res) {
        setSaving('sco', false);
        if (res.success) { showToast(res.message, 'success'); closeModal('modal-sco'); loadScolarites(); }
        else showToast(res.message, 'error');
      },
      error: function (xhr) {
        setSaving('sco', false);
        const res = xhr.responseJSON;
        if (res?.errors) {
          const map = { filiere_code: 'sco-filiere', niveau_code: 'sco-niveau', annee_code: 'sco-annee', montant_scolarite: 'sco-montant' };
          $.each(res.errors, (f, m) => showErr(map[f] || f, m));
        } else showToast(res?.message || 'Erreur.', 'error');
      }
    });
  });

  $(document).on('click', '.btn-del-sco', function () {
    const code = $(this).data('code');
    if (!confirm('Supprimer ce tarif ?')) return;
    $(this).prop('disabled', true);
    $.post('/api/scolarites/supprimer', { code_scolarite: code }, function (res) {
      if (res.success) { showToast(res.message, 'success'); loadScolarites(); }
      else showToast(res.message, 'error');
    });
  });

  // ═══════════════════════════════════════════════════════════
  // HELPERS
  // ═══════════════════════════════════════════════════════════
  function resetPay() {
    ['pay-inscription','pay-annee','pay-type','pay-mode'].forEach(id => $(`#${id}`).val(''));
    $('#pay-montant, #pay-obs').val('');
    $('#pay-ins-info').html('');
    clearErr('pay-montant'); clearErr('pay-type'); clearErr('pay-mode');
    setSaving('pay', false);
  }
  function resetSco() {
    $('#sco-code, #sco-montant').val('');
    $('#sco-filiere, #sco-annee').val('');
    $('#sco-niveau').find('option:not(:first)').remove().val('').prop('disabled', true);
    ['sco-filiere','sco-niveau','sco-annee','sco-montant'].forEach(id => clearErr(id));
    setSaving('sco', false);
  }
  function closeModal(id) { $('#' + id).removeClass('open'); }
  $(document).on('click', '[data-close]', function () { closeModal($(this).data('close')); });
  $(document).on('click', '.modal-backdrop', function (e) { if ($(e.target).hasClass('modal-backdrop')) $(this).removeClass('open'); });
  function setSaving(p, s) {
    $(`#btn-save-${p}`).prop('disabled', s);
    $(`#save-${p}-text`).text(s ? 'Enregistrement…' : 'Enregistrer');
    $(`#save-${p}-spinner`).toggle(s);
  }
  function clearErr(id) { $(`#err-${id}`).removeClass('show').text(''); $(`#${id}`).removeClass('is-invalid'); }
  function showErr(id, msg) { $(`#err-${id}`).text(msg).addClass('show'); $(`#${id}`).addClass('is-invalid'); }
  function fmt(n) { return parseFloat(n || 0).toLocaleString('fr-FR'); }
  function esc(s) { return $('<div>').text(s || '').html(); }
  function escA(s) { return String(s || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }
});

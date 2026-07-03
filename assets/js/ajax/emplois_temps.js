/**
 * emplois_temps.js — Emplois du temps (AJAX)
 * EICG Gestion Scolaire
 */

$(document).ready(function () {

  let currentView  = 'grille';
  let currentData  = [];

  const JOURS      = ['lundi','mardi','mercredi','jeudi','vendredi','samedi'];
  const JOURS_LAB  = { lundi:'Lundi', mardi:'Mardi', mercredi:'Mercredi', jeudi:'Jeudi', vendredi:'Vendredi', samedi:'Samedi', dimanche:'Dimanche' };
  // Créneaux horaires de référence
  const HEURES     = ['07:00','08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00','19:00'];

  // ═══════════════════════════════════════════════════════════
  // VUES
  // ═══════════════════════════════════════════════════════════
  $('.vtab').on('click', function () {
    currentView = $(this).data('view');
    $('.vtab').removeClass('active');
    $(this).addClass('active');
    if (currentView === 'grille') {
      $('#view-grille').show(); $('#view-liste').hide();
      renderGrille(currentData);
    } else {
      $('#view-grille').hide(); $('#view-liste').show();
      renderListe(currentData);
    }
  });

  // ═══════════════════════════════════════════════════════════
  // CHARGEMENT SELECTS
  // ═══════════════════════════════════════════════════════════
  function loadSelects() {
    $.get('/appeicg/annees/liste', function (res) {
      const opts = (res.data || []).map(a => `<option value="${a.libelle_annee}" data-id="${a.id_annee}">${esc(a.libelle_annee)}</option>`).join('');
      const optsId = (res.data || []).map(a => `<option value="${a.id_annee}">${esc(a.libelle_annee)}</option>`).join('');
      $('#f-emp-annee').find('option:not(:first)').remove().end().append(opts);
      $('#emp-annee').find('option:not(:first)').remove().end().append(optsId);

      // Sélectionner année active par défaut
      const actif = (res.data || []).find(a => a.statut_annee === 'actif');
      if (actif) {
        $('#f-emp-annee').val(actif.libelle_annee);
        $('#emp-annee').val(actif.id_annee);
      }
    });

    $.get('/appeicg/classes/liste', function (res) {
      const opts = (res.data || []).filter(c => c.statut_classe === 'actif')
        .map(c => `<option value="${c.code_classe}">${esc(c.libelle_classe)}</option>`).join('');
      $('#f-emp-classe, #emp-classe').find('option:not(:first)').remove().end().append(opts);
    });

    $.get('/appeicg/enseignants/liste', function (res) {
      const opts = (res.data || []).filter(e => e.statut_enseignant === 'actif')
        .map(e => `<option value="${e.code_enseignant}">${esc(e.nom_enseignant)}</option>`).join('');
      $('#f-emp-ens, #emp-enseignant').find('option:not(:first)').remove().end().append(opts);
    });

    $.get('/appeicg/matieres/liste', function (res) {
      const opts = (res.data || []).filter(m => m.statut_matiere === 'actif')
        .map(m => `<option value="${m.code_matiere}">${esc(m.libelle_matiere)}</option>`).join('');
      $('#emp-matiere').find('option:not(:first)').remove().end().append(opts);
    });

    $.get('/appeicg/salles/liste', function (res) {
      const opts = (res.data || []).filter(s => s.statut_salle === 'actif')
        .map(s => `<option value="${s.code_salle}">${esc(s.libelle_salle)}</option>`).join('');
      $('#emp-salle').find('option:not(:first)').remove().end().append(opts);
    });
  }
  loadSelects();

  // ═══════════════════════════════════════════════════════════
  // CHARGEMENT DONNÉES
  // ═══════════════════════════════════════════════════════════
  function loadEmplois() {
    const params = new URLSearchParams({
      annee_code:      $('#f-emp-annee').val(),
      classe_code:     $('#f-emp-classe').val(),
      enseignant_code: $('#f-emp-ens').val(),
      jour:            $('#f-emp-jour').val(),
    });

    $.get('/appeicg/emplois-temps/liste?' + params.toString(), function (res) {
      currentData = res.data || [];
      $('#emp-count').text(`${currentData.length} créneau${currentData.length > 1 ? 'x' : ''}`);

      const classeLib = $('#f-emp-classe option:selected').text();
      const ensLib    = $('#f-emp-ens option:selected').text();
      let title = 'Grille hebdomadaire';
      if ($('#f-emp-classe').val()) title += ` — ${classeLib}`;
      else if ($('#f-emp-ens').val()) title += ` — ${ensLib}`;
      $('#grille-title').text(title);

      if (currentView === 'grille') renderGrille(currentData);
      else renderListe(currentData);
    });
  }

  // Charger au démarrage si une classe est pré-sélectionnée
  $('#btn-filter-emp').on('click', loadEmplois);

  // ═══════════════════════════════════════════════════════════
  // VUE GRILLE
  // ═══════════════════════════════════════════════════════════
  function renderGrille(data) {
    if (!data.length) {
      $('#tbody-grille').html(`<tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted)">Aucun créneau trouvé. Utilisez les filtres pour afficher l'emploi du temps.</td></tr>`);
      return;
    }

    // Organiser par jour et heure
    const grid = {};
    JOURS.forEach(j => { grid[j] = {}; });
    data.forEach(function (e) {
      if (!grid[e.jour]) grid[e.jour] = {};
      const key = e.heure_debut;
      if (!grid[e.jour][key]) grid[e.jour][key] = [];
      grid[e.jour][key].push(e);
    });

    // Collecter toutes les heures utilisées
    const heuresUsed = new Set();
    data.forEach(e => heuresUsed.add(e.heure_debut));
    const heures = HEURES.filter(h => heuresUsed.has(h));
    if (!heures.length) { heures.push(...[...heuresUsed].sort()); }

    let html = '';
    heures.forEach(function (h) {
      html += `<tr><td class="time-col">${h}</td>`;
      JOURS.forEach(function (j) {
        const slots = grid[j][h] || [];
        html += '<td>';
        slots.forEach(function (e) {
          html += `
            <div class="slot" style="background:${jourColor(j)}">
              <div class="slot-mat">${esc(e.libelle_matiere)}</div>
              <div class="slot-meta">${esc(e.libelle_classe)} · ${esc(e.libelle_salle)}</div>
              <div class="slot-meta">${esc(e.nom_enseignant)}</div>
              <div class="slot-meta">${e.heure_debut.slice(0,5)} – ${e.heure_fin.slice(0,5)}</div>
              <div class="slot-actions">
                <button class="slot-btn btn-edit-slot" data-code="${e.code_emploi}">✎</button>
                <button class="slot-btn del btn-del-slot" data-code="${e.code_emploi}">✕</button>
              </div>
            </div>`;
        });
        html += '</td>';
      });
      html += '</tr>';
    });

    $('#tbody-grille').html(html);
  }

  function jourColor(jour) {
    const colors = { lundi:'#1B3A6B', mardi:'#2E5BA8', mercredi:'#C8861A', jeudi:'#27AE60', vendredi:'#8E44AD', samedi:'#2E86C1' };
    return colors[jour] || '#1B3A6B';
  }

  // ═══════════════════════════════════════════════════════════
  // VUE LISTE
  // ═══════════════════════════════════════════════════════════
  function renderListe(data) {
    const $tbody = $('#tbody-liste');
    $tbody.empty();
    if (!data.length) {
      $tbody.html(`<tr><td colspan="7"><div class="empty-state"><p>Aucun créneau trouvé.</p></div></td></tr>`);
      return;
    }
    data.forEach(function (e) {
      $tbody.append(`
        <tr>
          <td class="fw-600">${JOURS_LAB[e.jour] || esc(e.jour)}</td>
          <td class="text-sm">${e.heure_debut.slice(0,5)} – ${e.heure_fin.slice(0,5)}</td>
          <td>${esc(e.libelle_matiere)}</td>
          <td>${esc(e.nom_enseignant)}</td>
          <td>${esc(e.libelle_classe)}</td>
          <td>${esc(e.libelle_salle)}</td>
          <td>
            <div class="d-flex gap-2">
              <button class="btn btn-outline btn-sm btn-edit-slot" data-code="${e.code_emploi}">Modifier</button>
              <button class="btn btn-danger btn-sm btn-del-slot" data-code="${e.code_emploi}">Supprimer</button>
            </div>
          </td>
        </tr>
      `);
    });
  }

  // ═══════════════════════════════════════════════════════════
  // CRUD CRÉNEAUX
  // ═══════════════════════════════════════════════════════════
  $('#btn-add-emp').on('click', function () {
    resetEmp();
    $('#modal-emp-title').text('Nouveau créneau');
    $('#modal-emp').addClass('open');
  });

  // Modifier depuis grille ou liste
  $(document).on('click', '.btn-edit-slot', function (e) {
    e.stopPropagation();
    const code    = $(this).data('code');
    const emploi  = currentData.find(d => d.code_emploi === code);
    if (!emploi) return;

    resetEmp();
    $('#modal-emp-title').text('Modifier le créneau');
    $('#emp-code').val(code);
    $('#emp-jour').val(emploi.jour);
    $('#emp-debut').val(emploi.heure_debut.slice(0, 5));
    $('#emp-fin').val(emploi.heure_fin.slice(0, 5));
    $('#emp-classe').val(emploi.classe_code);
    $('#emp-matiere').val(emploi.matiere_code);
    $('#emp-enseignant').val(emploi.enseignant_code);
    $('#emp-salle').val(emploi.salle_code);

    // Retrouver l'id_annee depuis le libelle
    $.get('/appeicg/annees/liste', function (res) {
      const annee = (res.data || []).find(a => a.libelle_annee === emploi.annee_code);
      if (annee) $('#emp-annee').val(annee.id_annee);
    });

    $('#modal-emp').addClass('open');
  });

  // Supprimer
  $(document).on('click', '.btn-del-slot', function (e) {
    e.stopPropagation();
    const code = $(this).data('code');
    if (!confirm('Supprimer ce créneau ?')) return;
    $(this).prop('disabled', true);
    $.post('/appeicg/emplois-temps/supprimer', { code_emploi: code }, function (res) {
      if (res.success) { showToast(res.message, 'success'); loadEmplois(); }
      else showToast(res.message, 'error');
    });
  });

  // Enregistrer
  $('#btn-save-emp').on('click', function () {
    const fields = ['emp-annee','emp-jour','emp-debut','emp-fin','emp-classe','emp-matiere','emp-enseignant','emp-salle'];
    fields.forEach(id => clearErr(id));

    const code    = $('#emp-code').val();
    const anneeId = $('#emp-annee').val();
    const jour    = $('#emp-jour').val();
    const debut   = $('#emp-debut').val();
    const fin     = $('#emp-fin').val();
    const classe  = $('#emp-classe').val();
    const matiere = $('#emp-matiere').val();
    const ens     = $('#emp-enseignant').val();
    const salle   = $('#emp-salle').val();

    let ok = true;
    if (!anneeId) { showErr('emp-annee',      'L\'année est obligatoire.');      ok = false; }
    if (!jour)    { showErr('emp-jour',        'Le jour est obligatoire.');       ok = false; }
    if (!debut)   { showErr('emp-debut',       'L\'heure de début est requise.'); ok = false; }
    if (!fin)     { showErr('emp-fin',         'L\'heure de fin est requise.');   ok = false; }
    if (!classe)  { showErr('emp-classe',      'La classe est obligatoire.');     ok = false; }
    if (!matiere) { showErr('emp-matiere',     'La matière est obligatoire.');    ok = false; }
    if (!ens)     { showErr('emp-enseignant',  'L\'enseignant est obligatoire.'); ok = false; }
    if (!salle)   { showErr('emp-salle',       'La salle est obligatoire.');      ok = false; }
    if (debut && fin && debut >= fin) { showErr('emp-fin', 'L\'heure de fin doit être après le début.'); ok = false; }
    if (!ok) return;

    setSaving(true);
    const data = { annee_id: anneeId, jour, heure_debut: debut, heure_fin: fin, classe_code: classe, matiere_code: matiere, enseignant_code: ens, salle_code: salle };
    if (code) data.code_emploi = code;

    $.ajax({
      url: code ? '/appeicg/emplois-temps/modifier' : '/appeicg/emplois-temps/ajouter', method: 'POST', data,
      success: function (res) {
        setSaving(false);
        if (res.success) { showToast(res.message, 'success'); closeModal('modal-emp'); loadEmplois(); }
        else showToast(res.message, 'error');
      },
      error: function (xhr) {
        setSaving(false);
        const res = xhr.responseJSON;
        if (res?.errors) {
          const map = { classe_code:'emp-classe', matiere_code:'emp-matiere', enseignant_code:'emp-enseignant', salle_code:'emp-salle', annee_id:'emp-annee', jour:'emp-jour', heure_debut:'emp-debut', heure_fin:'emp-fin' };
          $.each(res.errors, (f, m) => showErr(map[f] || f, m));
        } else showToast(res?.message || 'Erreur.', 'error');
      }
    });
  });

  // ═══════════════════════════════════════════════════════════
  // HELPERS
  // ═══════════════════════════════════════════════════════════
  function resetEmp() {
    $('#emp-code').val('');
    ['emp-annee','emp-jour','emp-debut','emp-fin','emp-classe','emp-matiere','emp-enseignant','emp-salle'].forEach(id => {
      $(`#${id}`).val(''); clearErr(id);
    });
    setSaving(false);
  }
  function closeModal(id) { $('#' + id).removeClass('open'); }
  $(document).on('click', '[data-close]', function () { closeModal($(this).data('close')); });
  $(document).on('click', '.modal-backdrop', function (e) { if ($(e.target).hasClass('modal-backdrop')) $(this).removeClass('open'); });
  function setSaving(s) {
    $('#btn-save-emp').prop('disabled', s);
    $('#save-emp-text').text(s ? 'Enregistrement…' : 'Enregistrer');
    $('#save-emp-spinner').toggle(s);
  }
  function clearErr(id) { $(`#err-${id}`).removeClass('show').text(''); $(`#${id}`).removeClass('is-invalid'); }
  function showErr(id, msg) { $(`#err-${id}`).text(msg).addClass('show'); $(`#${id}`).addClass('is-invalid'); }
  function esc(s) { return $('<div>').text(s || '').html(); }
});

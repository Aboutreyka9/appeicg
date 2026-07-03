/**
 * notes.js — Notes, Moyennes, Bulletins, Classement (AJAX)
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
  });

  // ═══════════════════════════════════════════════════════════
  // CHARGEMENT SELECTS
  // ═══════════════════════════════════════════════════════════
  function loadSelects() {
    // Années
    $.get('/api/annees/liste', function (res) {
      const annees = res.data || [];
      const opts   = annees.map(a => `<option value="${a.libelle_annee}" data-id="${a.id_annee}">${esc(a.libelle_annee)}</option>`).join('');
      $('#s-annee').find('option:not(:first)').remove().end().append(opts);
      const actif = annees.find(a => a.statut_annee === 'actif');
      if (actif) { $('#s-annee').val(actif.libelle_annee).trigger('change'); }
    });

    // Classes
    $.get('/api/classes/liste', function (res) {
      const opts = (res.data || []).filter(c => c.statut_classe === 'actif')
        .map(c => `<option value="${c.code_classe}">${esc(c.libelle_classe)}</option>`).join('');
      $('#s-classe, #c-classe').find('option:not(:first)').remove().end().append(opts);
    });

    // Matières
    $.get('/api/matieres/liste', function (res) {
      const opts = (res.data || []).filter(m => m.statut_matiere === 'actif')
        .map(m => `<option value="${m.code_matiere}">${esc(m.libelle_matiere)}</option>`).join('');
      $('#s-matiere').find('option:not(:first)').remove().end().append(opts);
    });

    // Inscriptions (pour bulletin)
    $.get('/api/inscriptions/liste', function (res) {
      const opts = (res.data || []).map(i =>
        `<option value="${i.code_inscription}">${esc(i.nom_etudiant)} ${esc(i.prenom_etudiant)} — ${esc(i.libelle_classe)} (${esc(i.annee_code)})</option>`
      ).join('');
      $('#b-inscription').find('option:not(:first)').remove().end().append(opts);
    });

    // Tous les semestres pour bulletin + classement
    $.get('/api/annees/liste', function (res) {
      const annees = res.data || [];
      if (!annees.length) return;
      // Charger les semestres de la première année active
      const actif = annees.find(a => a.statut_annee === 'actif') || annees[0];
      $.get(`/api/semestres/liste?annee_code=${encodeURIComponent(actif.libelle_annee)}`, function (sr) {
        const opts = (sr.data || []).map(s =>
          `<option value="${s.code_semestre}">${esc(s.libelle_semestre)} — ${esc(actif.libelle_annee)}</option>`
        ).join('');
        $('#b-semestre, #c-semestre').find('option:not(:first)').remove().end().append(opts);
      });
    });
  }
  loadSelects();

  // Chargement dynamique des semestres selon l'année (saisie)
  $('#s-annee').on('change', function () {
    const annee = $(this).val();
    const $sem  = $('#s-semestre');
    $sem.find('option:not(:first)').remove().prop('disabled', !annee);
    if (!annee) return;
    $.get(`/api/semestres/liste?annee_code=${encodeURIComponent(annee)}`, function (res) {
      (res.data || []).forEach(s => $sem.append(`<option value="${s.code_semestre}">${esc(s.libelle_semestre)}</option>`));
      $sem.prop('disabled', false);
    });
  });

  // ═══════════════════════════════════════════════════════════
  // SAISIE DES NOTES (par classe + matière + semestre)
  // ═══════════════════════════════════════════════════════════
  let saisieContext = {}; // { semestreCode, matiereCode, typeEval, inscriptions }

  $('#btn-charger-saisie').on('click', function () {
    const semestreCode = $('#s-semestre').val();
    const classeCode   = $('#s-classe').val();
    const matiereCode  = $('#s-matiere').val();
    const typeEval     = $('#s-type').val();

    if (!semestreCode || !classeCode || !matiereCode || !typeEval) {
      showToast('Veuillez remplir tous les filtres.', 'warning'); return;
    }

    saisieContext = { semestreCode, classeCode, matiereCode, typeEval };

    // Charger les inscriptions de la classe
    $.get(`/api/inscriptions/liste?classe_code=${classeCode}`, function (res) {
      const inscriptions = (res.data || []).filter(i => i.statut_inscription !== 'annule');
      if (!inscriptions.length) {
        showToast('Aucun étudiant inscrit dans cette classe.', 'warning'); return;
      }
      saisieContext.inscriptions = inscriptions;

      // Charger les notes existantes
      $.get(`/api/notes/liste?classe_code=${classeCode}&semestre_code=${semestreCode}&matiere_code=${matiereCode}`, function (nr) {
        const notesExist = {};
        (nr.data || []).filter(n => n.type_evaluation_code === typeEval).forEach(n => {
          notesExist[n.inscription_code] = n;
        });

        const $tbody = $('#tbody-saisie');
        $tbody.empty();
        inscriptions.forEach(function (ins) {
          const note = notesExist[ins.code_inscription];
          const valeur = note ? note.valeur_note : '';
          const obs    = note ? (note.observations || '') : '';
          const noteBadge = note ? noteBadgeHtml(parseFloat(note.valeur_note)) : '<span class="text-muted text-sm">—</span>';
          $tbody.append(`
            <tr data-ins="${ins.code_inscription}" data-note-code="${note ? note.code_note : ''}">
              <td class="fw-600">${esc(ins.nom_etudiant)} ${esc(ins.prenom_etudiant)}</td>
              <td class="text-sm text-muted">${esc(ins.matricule_etudiant)}</td>
              <td style="text-align:center">
                <input type="number" class="note-input" value="${valeur}" min="0" max="20" step="0.25"
                  placeholder="—" data-original="${valeur}">
              </td>
              <td><input type="text" class="form-control" value="${escA(obs)}" placeholder="Observation optionnelle…" style="font-size:.8rem;padding:4px 8px;"></td>
              <td>${noteBadge}</td>
            </tr>
          `);
        });

        const matiereLib = $('#s-matiere option:selected').text();
        const semLib     = $('#s-semestre option:selected').text();
        const typeLib    = $('#s-type option:selected').text();
        $('#saisie-title').text(`${matiereLib} — ${typeLib}`);
        $('#saisie-info').text(`${semLib} · ${inscriptions.length} étudiants`);
        $('#saisie-panel').show();
      });
    });
  });

  function noteBadgeHtml(val) {
    const cls = val >= 10 ? 'note-ok' : (val >= 8 ? 'note-med' : 'note-fail');
    return `<span class="note-badge ${cls}">${val.toFixed(2)}/20</span>`;
  }

  // Enregistrer toutes les notes
  $('#btn-enregistrer-notes').on('click', function () {
    const rows    = [];
    let hasError  = false;

    $('#tbody-saisie tr').each(function () {
      const insCode  = $(this).data('ins');
      const noteCode = $(this).data('note-code');
      const valInput = $(this).find('.note-input');
      const val      = valInput.val().trim();
      const obs      = $(this).find('.form-control').val().trim();

      if (val === '') return; // Ignorer les lignes vides

      const valNum = parseFloat(val);
      if (isNaN(valNum) || valNum < 0 || valNum > 20) {
        valInput.css('border-color', 'var(--danger)');
        hasError = true;
        return;
      }
      valInput.css('border-color', '');
      rows.push({ insCode, noteCode, valeur: valNum, obs });
    });

    if (hasError) { showToast('Certaines notes sont invalides (0-20 requis).', 'error'); return; }
    if (!rows.length) { showToast('Aucune note à enregistrer.', 'warning'); return; }

    $(this).prop('disabled', true).text('Enregistrement…');
    const promises = rows.map(r => {
      const data = {
        valeur_note: r.valeur, type_evaluation_code: saisieContext.typeEval,
        inscription_code: r.insCode, matiere_code: saisieContext.matiereCode,
        semestre_code: saisieContext.semestreCode, observations: r.obs,
      };
      if (r.noteCode) {
        return $.post('/api/notes/modifier', { code_note: r.noteCode, valeur_note: r.valeur, type_evaluation_code: saisieContext.typeEval, observations: r.obs });
      } else {
        return $.post('/api/notes/ajouter', data);
      }
    });

    $.when.apply($, promises).then(function () {
      showToast('Notes enregistrées avec succès.', 'success');
      $('#btn-enregistrer-notes').prop('disabled', false).text('Enregistrer tout');
      $('#btn-charger-saisie').trigger('click'); // Recharger
    }).fail(function (xhr) {
      showToast(xhr.responseJSON?.message || 'Erreur lors de l\'enregistrement.', 'error');
      $('#btn-enregistrer-notes').prop('disabled', false).text('Enregistrer tout');
    });
  });

  // ═══════════════════════════════════════════════════════════
  // BULLETIN
  // ═══════════════════════════════════════════════════════════
  $('#btn-afficher-bulletin').on('click', function () {
    const insCode  = $('#b-inscription').val();
    const semCode  = $('#b-semestre').val();
    if (!insCode || !semCode) { showToast('Choisissez un étudiant et un semestre.', 'warning'); return; }

    $('#bulletin-view').html('<p class="text-sm text-muted" style="text-align:center;padding:30px">Chargement du bulletin…</p>');

    $.get(`/api/notes/bulletin?inscription_code=${insCode}&semestre_code=${semCode}`, function (res) {
      const d  = res.data;
      const et = d.etudiant || {};
      const bl = d.bulletin || [];

      if (!bl.length) {
        $('#bulletin-view').html('<div class="card"><div class="card-body"><p class="text-muted" style="text-align:center;padding:20px">Aucune note disponible pour ce semestre.</p></div></div>');
        return;
      }

      let rows = '';
      bl.forEach(function (b) {
        const moy = parseFloat(b.moyenne);
        const cls = moy >= 10 ? 'note-ok' : (moy >= 8 ? 'note-med' : 'note-fail');
        rows += `
          <tr>
            <td class="fw-600">${esc(b.libelle_matiere)}</td>
            <td>${b.nb_notes}</td>
            <td>${parseFloat(b.min_note).toFixed(2)}</td>
            <td>${parseFloat(b.max_note).toFixed(2)}</td>
            <td><span class="note-badge ${cls}">${moy.toFixed(2)}</span></td>
          </tr>`;
      });

      const moyGen = parseFloat(d.moyenne_generale);
      const mentionLabel = moyGen >= 16 ? 'Très Bien' : moyGen >= 14 ? 'Bien' : moyGen >= 12 ? 'Assez Bien' : moyGen >= 10 ? 'Passable' : 'Insuffisant';
      const mentionColor = moyGen >= 10 ? 'var(--success)' : 'var(--danger)';

      $('#bulletin-view').html(`
        <div class="bulletin-card">
          <div class="bulletin-header">
            <div>
              <div class="bulletin-school">EICG — Gestion Scolaire</div>
              <div class="text-sm text-muted mt-1">Bulletin de notes</div>
            </div>
            <div class="bulletin-title">
              <div class="fw-600">${esc(et.libelle_semestre || '')}</div>
              <div>${esc(et.annee_code || '')}</div>
            </div>
          </div>
          <div class="bulletin-student">
            <div class="bul-field"><label>Étudiant</label><span>${esc(et.nom_etudiant || '')} ${esc(et.prenom_etudiant || '')}</span></div>
            <div class="bul-field"><label>Matricule</label><span>${esc(et.matricule_etudiant || '')}</span></div>
            <div class="bul-field"><label>Classe</label><span>${esc(et.libelle_classe || '')}</span></div>
          </div>
          <table class="bul-table">
            <thead><tr><th>Matière</th><th>Évals.</th><th>Note min</th><th>Note max</th><th>Moyenne</th></tr></thead>
            <tbody>${rows}</tbody>
          </table>
          <div class="bul-total">
            <div>
              <div style="font-size:.8rem;opacity:.8">Moyenne générale</div>
              <div class="bul-avg">${moyGen.toFixed(2)} / 20</div>
            </div>
            <div style="text-align:right">
              <div style="font-size:.8rem;opacity:.8">Mention</div>
              <div style="font-size:1.1rem;font-weight:700;color:${mentionColor}">${mentionLabel}</div>
            </div>
          </div>
        </div>
      `);
    }).fail(function (xhr) {
      $('#bulletin-view').html(`<div class="alert alert-danger">${xhr.responseJSON?.message || 'Erreur lors du chargement.'}</div>`);
    });
  });

  // ═══════════════════════════════════════════════════════════
  // CLASSEMENT
  // ═══════════════════════════════════════════════════════════
  $('#btn-afficher-classement').on('click', function () {
    const classeCode  = $('#c-classe').val();
    const semestreCode = $('#c-semestre').val();
    if (!classeCode || !semestreCode) { showToast('Choisissez une classe et un semestre.', 'warning'); return; }

    $.get(`/api/notes/classement?classe_code=${classeCode}&semestre_code=${semestreCode}`, function (res) {
      const data = res.data || [];
      const $tbody = $('#tbody-classement');
      $tbody.empty();

      if (!data.length) {
        $tbody.html(`<tr><td colspan="6"><div class="empty-state"><p>Aucune donnée disponible.</p></div></td></tr>`);
        $('#classement-panel').show(); return;
      }

      const classeLib  = $('#c-classe option:selected').text();
      const semestreLib = $('#c-semestre option:selected').text();
      $('#classement-title').text(`Classement — ${classeLib}`);
      $('#classement-info').text(semestreLib + ' · ' + data.length + ' étudiants');

      data.forEach(function (row, idx) {
        const rang     = row.rang === '—' ? '—' : row.rang;
        const rangCls  = rang === 1 ? 'rank-1' : rang === 2 ? 'rank-2' : rang === 3 ? 'rank-3' : '';
        const moy      = row.moyenne_generale !== null ? parseFloat(row.moyenne_generale) : null;
        const moyStr   = moy !== null ? moy.toFixed(2) + '/20' : '—';
        const moyBadge = moy !== null ? `<span class="note-badge ${moy >= 10 ? 'note-ok' : 'note-fail'}">${moyStr}</span>` : '<span class="text-muted">—</span>';
        const mention  = moy !== null ? (moy >= 16 ? 'Très Bien' : moy >= 14 ? 'Bien' : moy >= 12 ? 'Assez Bien' : moy >= 10 ? 'Passable' : 'Insuffisant') : '—';

        $tbody.append(`
          <tr>
            <td class="fw-600 ${rangCls}" style="text-align:center;font-size:1rem">${rang}</td>
            <td><span class="fw-600">${esc(row.nom_etudiant)} ${esc(row.prenom_etudiant)}</span></td>
            <td class="text-sm text-muted">${esc(row.matricule_etudiant)}</td>
            <td style="text-align:center">${row.nb_matieres}</td>
            <td>${moyBadge}</td>
            <td class="text-sm">${mention}</td>
          </tr>
        `);
      });
      $('#classement-panel').show();
    }).fail(function (xhr) {
      showToast(xhr.responseJSON?.message || 'Erreur.', 'error');
    });
  });

  // ═══════════════════════════════════════════════════════════
  // HELPERS
  // ═══════════════════════════════════════════════════════════
  $(document).on('click', '[data-close]', function () { $('#' + $(this).data('close')).removeClass('open'); });
  $(document).on('click', '.modal-backdrop', function (e) { if ($(e.target).hasClass('modal-backdrop')) $(this).removeClass('open'); });
  function clearErr(id) { $(`#err-${id}`).removeClass('show').text(''); $(`#${id}`).removeClass('is-invalid'); }
  function showErr(id, msg) { $(`#err-${id}`).text(msg).addClass('show'); $(`#${id}`).addClass('is-invalid'); }
  function esc(s) { return $('<div>').text(s || '').html(); }
  function escA(s) { return String(s || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }
});

/**
 * communication.js — Documents, Messagerie, Événements (AJAX)
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
    if (target === 'tab-messages')   loadMessages();
    if (target === 'tab-evenements') loadEvenements();
  });

  // ═══════════════════════════════════════════════════════════
  // SELECTS GLOBAUX
  // ═══════════════════════════════════════════════════════════
  function loadSelects() {
    $.get('/appeicg/filieres/liste', function (res) {
      const opts = (res.data || []).filter(f => f.statut_filiere === 'actif')
        .map(f => `<option value="${f.code_filiere}">${esc(f.libelle_filiere)}</option>`).join('');
      $('#d-filiere, #doc-filiere').find('option:not(:first)').remove().end().append(opts);
    });

    $.get('/appeicg/niveaux/liste', function (res) {
      const opts = (res.data || []).filter(n => n.statut_niveau === 'actif')
        .map(n => `<option value="${n.code_niveau}">${esc(n.libelle_niveau)}</option>`).join('');
      $('#d-niveau').find('option:not(:first)').remove().end().append(opts);
    });

    $.get('/appeicg/annees/liste', function (res) {
      const opts = (res.data || []).map(a => `<option value="${a.libelle_annee}">${esc(a.libelle_annee)}</option>`).join('');
      $('#d-annee, #doc-annee').find('option:not(:first)').remove().end().append(opts);
    });
  }
  loadSelects();

  // Chargement dynamique niveaux selon filière (modal doc)
  $('#doc-filiere').on('change', function () {
    const fil  = $(this).val();
    const $niv = $('#doc-niveau');
    $niv.find('option:not(:first)').remove().prop('disabled', !fil);
    if (!fil) return;
    $.get(`/appeicg/niveaux/liste?filiere_code=${fil}`, function (res) {
      (res.data || []).filter(n => n.statut_niveau === 'actif').forEach(n => {
        $niv.append(`<option value="${n.code_niveau}">${esc(n.libelle_niveau)}</option>`);
      });
      $niv.prop('disabled', false);
    });
  });

  // ═══════════════════════════════════════════════════════════
  // DOCUMENTS
  // ═══════════════════════════════════════════════════════════
  function loadDocuments() {
    const params = new URLSearchParams({
      filiere_code: $('#d-filiere').val(),
      niveaux_code: $('#d-niveau').val(),
      annee_code:   $('#d-annee').val(),
    });
    $.get('/appeicg/documents/liste?' + params.toString(), function (res) {
      const docs = res.data || [];
      $('#docs-count').text(`${docs.length} document${docs.length > 1 ? 's' : ''}`);
      const $tbody = $('#tbody-docs');
      $tbody.empty();
      if (!docs.length) {
        $tbody.html(`<tr><td colspan="6"><div class="empty-state"><p>Aucun document disponible.</p></div></td></tr>`);
        return;
      }
      docs.forEach(function (d) {
        $tbody.append(`
          <tr>
            <td class="fw-600">${esc(d.libelle_document)}</td>
            <td class="text-sm">${esc(d.filiere_code)}</td>
            <td class="text-sm">${esc(d.niveaux_code)}</td>
            <td class="text-sm text-muted">${esc(d.annee_code)}</td>
            <td>
              <a href="${escA(d.lien_document)}" target="_blank" class="btn btn-outline btn-sm">
                Ouvrir ↗
              </a>
            </td>
            <td>
              <div class="d-flex gap-2">
                <button class="btn btn-outline btn-sm btn-edit-doc"
                  data-id="${d.id_document}"
                  data-libelle="${escA(d.libelle_document)}"
                  data-lien="${escA(d.lien_document)}"
                  data-filiere="${d.filiere_code}"
                  data-niveau="${d.niveaux_code}"
                  data-annee="${escA(d.annee_code)}">
                  Modifier
                </button>
                <button class="btn btn-danger btn-sm btn-del-doc" data-id="${d.id_document}">Supprimer</button>
              </div>
            </td>
          </tr>
        `);
      });
    });
  }
  loadDocuments();

  $('#btn-filter-docs').on('click', loadDocuments);

  $('#btn-add-doc').on('click', function () {
    resetDoc();
    $('#modal-doc-title').text('Ajouter un document');
    $('#modal-doc').addClass('open');
  });

  $(document).on('click', '.btn-edit-doc', function () {
    resetDoc();
    $('#modal-doc-title').text('Modifier le document');
    $('#doc-id').val($(this).data('id'));
    $('#doc-libelle').val($(this).data('libelle'));
    $('#doc-lien').val($(this).data('lien'));
    $('#doc-annee').val($(this).data('annee'));
    const filiere = $(this).data('filiere');
    const niveau  = $(this).data('niveau');
    if (filiere) {
      $('#doc-filiere').val(filiere).trigger('change');
      setTimeout(() => $('#doc-niveau').val(niveau), 500);
    }
    $('#modal-doc').addClass('open');
  });

  $('#btn-save-doc').on('click', function () {
    ['doc-libelle','doc-lien','doc-filiere','doc-niveau','doc-annee'].forEach(id => clearErr(id));
    const id      = $('#doc-id').val();
    const libelle = $('#doc-libelle').val().trim();
    const lien    = $('#doc-lien').val().trim();
    const filiere = $('#doc-filiere').val();
    const niveau  = $('#doc-niveau').val();
    const annee   = $('#doc-annee').val();
    let ok = true;
    if (!libelle) { showErr('doc-libelle',  'Le libellé est obligatoire.');  ok = false; }
    if (!lien)    { showErr('doc-lien',     'Le lien est obligatoire.');     ok = false; }
    if (!filiere) { showErr('doc-filiere',  'La filière est obligatoire.');  ok = false; }
    if (!niveau)  { showErr('doc-niveau',   'Le niveau est obligatoire.');   ok = false; }
    if (!annee)   { showErr('doc-annee',    'L\'année est obligatoire.');    ok = false; }
    if (!ok) return;

    setSaving('doc', true);
    const data = { libelle_document: libelle, lien_document: lien, filiere_code: filiere, niveaux_code: niveau, annee_code: annee };
    if (id) data.id_document = id;
    $.ajax({
      url: id ? '/appeicg/documents/modifier' : '/appeicg/documents/ajouter', method: 'POST', data,
      success: function (res) {
        setSaving('doc', false);
        if (res.success) { showToast(res.message, 'success'); closeModal('modal-doc'); loadDocuments(); }
        else showToast(res.message, 'error');
      },
      error: function (xhr) { setSaving('doc', false); apiError(xhr, { libelle_document:'doc-libelle', lien_document:'doc-lien', filiere_code:'doc-filiere', niveaux_code:'doc-niveau', annee_code:'doc-annee' }); }
    });
  });

  $(document).on('click', '.btn-del-doc', function () {
    const id = $(this).data('id');
    if (!confirm('Supprimer ce document ?')) return;
    $(this).prop('disabled', true);
    $.post('/appeicg/documents/supprimer', { id_document: id }, function (res) {
      if (res.success) { showToast(res.message, 'success'); loadDocuments(); }
      else showToast(res.message, 'error');
    });
  });

  // ═══════════════════════════════════════════════════════════
  // MESSAGES
  // ═══════════════════════════════════════════════════════════
  const statutLabels = { en_attente:'En attente', envoye:'Envoyé', vue:'Vu', archive:'Archivé' };
  const statutIcons  = { en_attente:'⏳', envoye:'📤', vue:'👁', archive:'🗃️' };

  function loadMessages() {
    const statut = $('#f-msg-statut').val();
    $.get('/appeicg/messages/liste' + (statut ? `?statut=${statut}` : ''), function (res) {
      const msgs = res.data || [];
      const $list = $('#messages-list');
      $list.empty();
      if (!msgs.length) {
        $list.html('<div class="empty-state"><p>Aucun message trouvé.</p></div>'); return;
      }
      msgs.forEach(function (m) {
        const lab  = statutLabels[m.statut_message] || m.statut_message;
        const icon = statutIcons[m.statut_message]  || '📩';
        const date = m.created_at_message ? new Date(m.created_at_message).toLocaleDateString('fr-FR') : '—';
        const nextActions = {
          en_attente: [{ label:'Marquer envoyé', statut:'envoye', cls:'btn-primary' }],
          envoye:     [{ label:'Marquer vu',     statut:'vue',    cls:'btn-accent'  }],
          vue:        [{ label:'Archiver',        statut:'archive',cls:'btn-outline' }],
          archive:    [],
        }[m.statut_message] || [];

        const btnStatut = nextActions.map(a =>
          `<button class="btn ${a.cls} btn-sm btn-msg-statut" data-id="${m.id_message}" data-statut="${a.statut}">${a.label}</button>`
        ).join('');

        $list.append(`
          <div class="msg-card">
            <div class="msg-icon ${m.statut_message}">${icon}</div>
            <div class="msg-body">
              <div class="msg-objet">${esc(m.objet_message)}</div>
              <div class="msg-desc">${esc(m.description_message)}</div>
              <div class="msg-meta">
                <span class="badge statut-${m.statut_message}">${lab}</span>
                · Créé le ${date}
              </div>
            </div>
            <div class="msg-actions">
              ${btnStatut}
              <button class="btn btn-danger btn-sm btn-del-msg" data-id="${m.id_message}">Supprimer</button>
            </div>
          </div>
        `);
      });
    });
  }

  $('#btn-filter-msgs').on('click', loadMessages);

  $('#btn-add-msg').on('click', function () {
    $('#msg-objet, #msg-desc').val('');
    clearErr('msg-objet'); clearErr('msg-desc');
    setSaving('msg', false);
    $('#modal-msg').addClass('open');
  });

  $('#btn-save-msg').on('click', function () {
    clearErr('msg-objet'); clearErr('msg-desc');
    const objet = $('#msg-objet').val().trim();
    const desc  = $('#msg-desc').val().trim();
    let ok = true;
    if (!objet) { showErr('msg-objet', 'L\'objet est obligatoire.'); ok = false; }
    if (!desc)  { showErr('msg-desc',  'Le contenu est obligatoire.'); ok = false; }
    if (!ok) return;
    setSaving('msg', true);
    $.post('/appeicg/messages/creer', { objet_message: objet, description_message: desc }, function (res) {
      setSaving('msg', false);
      if (res.success) { showToast(res.message, 'success'); closeModal('modal-msg'); loadMessages(); }
      else showToast(res.message, 'error');
    }).fail(function (xhr) { setSaving('msg', false); showToast(xhr.responseJSON?.message || 'Erreur.', 'error'); });
  });

  $(document).on('click', '.btn-msg-statut', function () {
    const id = $(this).data('id'); const statut = $(this).data('statut');
    $(this).prop('disabled', true);
    $.post('/appeicg/messages/statut', { id_message: id, statut_message: statut }, function (res) {
      if (res.success) { showToast(res.message, 'success'); loadMessages(); }
      else showToast(res.message, 'error');
    });
  });

  $(document).on('click', '.btn-del-msg', function () {
    const id = $(this).data('id');
    if (!confirm('Supprimer ce message ?')) return;
    $(this).prop('disabled', true);
    $.post('/appeicg/messages/supprimer', { id_message: id }, function (res) {
      if (res.success) { showToast(res.message, 'success'); loadMessages(); }
      else showToast(res.message, 'error');
    });
  });

  // ═══════════════════════════════════════════════════════════
  // ÉVÉNEMENTS
  // ═══════════════════════════════════════════════════════════
  function loadEvenements() {
    $.get('/appeicg/evenements/liste', function (res) {
      const evs  = res.data || [];
      const $list = $('#evenements-list');
      $list.empty();
      if (!evs.length) {
        $list.html('<div class="empty-state"><p>Aucun événement enregistré.</p></div>'); return;
      }
      evs.forEach(function (e) {
        const badge  = e.statut_evenement === 'actif' ? 'badge-success' : 'badge-danger';
        const label  = e.statut_evenement === 'actif' ? 'Actif' : 'Inactif';
        const toggle = e.statut_evenement === 'actif' ? 'inactif' : 'actif';
        const date   = e.date_creation_evenement ? new Date(e.date_creation_evenement).toLocaleDateString('fr-FR') : '—';
        const principal = e.is_principal_evenement ? '<span class="badge badge-warning" style="margin-left:6px">À la une</span>' : '';

        $list.append(`
          <div class="ev-card">
            <div class="ev-body">
              <div class="ev-titre">${esc(e.titre_evenement)} ${principal}</div>
              ${e.description_evenement ? `<div class="ev-desc">${esc(e.description_evenement)}</div>` : ''}
              <div class="ev-meta">
                <span class="badge ${badge}">${label}</span>
                · Créé le ${date}
                ${e.image_evenement ? ` · <a href="${escA(e.image_evenement)}" target="_blank" class="text-sm">Voir l'image ↗</a>` : ''}
              </div>
            </div>
            <div class="ev-actions">
              <button class="btn btn-outline btn-sm btn-edit-ev"
                data-id="${e.id_evenement}"
                data-titre="${escA(e.titre_evenement)}"
                data-desc="${escA(e.description_evenement || '')}"
                data-image="${escA(e.image_evenement || '')}"
                data-principal="${e.is_principal_evenement ? '1' : '0'}">
                Modifier
              </button>
              <button class="btn btn-sm ${e.statut_evenement === 'actif' ? 'btn-danger' : 'btn-accent'} btn-toggle-ev"
                data-id="${e.id_evenement}" data-statut="${toggle}">
                ${e.statut_evenement === 'actif' ? 'Désactiver' : 'Activer'}
              </button>
              <button class="btn btn-danger btn-sm btn-del-ev" data-id="${e.id_evenement}">Supprimer</button>
            </div>
          </div>
        `);
      });
    });
  }

  $('#btn-add-ev').on('click', function () {
    resetEv();
    $('#modal-ev-title').text('Nouvel événement');
    $('#modal-ev').addClass('open');
  });

  $(document).on('click', '.btn-edit-ev', function () {
    resetEv();
    $('#modal-ev-title').text('Modifier l\'événement');
    $('#ev-id').val($(this).data('id'));
    $('#ev-titre').val($(this).data('titre'));
    $('#ev-desc').val($(this).data('desc'));
    $('#ev-image').val($(this).data('image'));
    $('#ev-principal').prop('checked', $(this).data('principal') === '1' || $(this).data('principal') === 1);
    $('#modal-ev').addClass('open');
  });

  $('#btn-save-ev').on('click', function () {
    clearErr('ev-titre');
    const id    = $('#ev-id').val();
    const titre = $('#ev-titre').val().trim();
    if (!titre) { showErr('ev-titre', 'Le titre est obligatoire.'); return; }
    setSaving('ev', true);
    const data = {
      titre_evenement: titre,
      description_evenement: $('#ev-desc').val().trim(),
      image_evenement: $('#ev-image').val().trim(),
      is_principal_evenement: $('#ev-principal').is(':checked') ? 1 : 0,
    };
    if (id) data.id_evenement = id;
    $.ajax({
      url: id ? '/appeicg/evenements/modifier' : '/appeicg/evenements/ajouter', method: 'POST', data,
      success: function (res) {
        setSaving('ev', false);
        if (res.success) { showToast(res.message, 'success'); closeModal('modal-ev'); loadEvenements(); }
        else showToast(res.message, 'error');
      },
      error: function (xhr) { setSaving('ev', false); showToast(xhr.responseJSON?.message || 'Erreur.', 'error'); }
    });
  });

  $(document).on('click', '.btn-toggle-ev', function () {
    const $btn = $(this); const id = $btn.data('id'); const statut = $btn.data('statut');
    if (!confirm(`${statut === 'actif' ? 'Activer' : 'Désactiver'} cet événement ?`)) return;
    $btn.prop('disabled', true);
    $.post('/appeicg/evenements/statut', { id_evenement: id, statut_evenement: statut }, function (res) {
      if (res.success) { showToast(res.message, 'success'); loadEvenements(); }
      else { showToast(res.message, 'error'); $btn.prop('disabled', false); }
    });
  });

  $(document).on('click', '.btn-del-ev', function () {
    const id = $(this).data('id');
    if (!confirm('Supprimer cet événement ?')) return;
    $(this).prop('disabled', true);
    $.post('/appeicg/evenements/supprimer', { id_evenement: id }, function (res) {
      if (res.success) { showToast(res.message, 'success'); loadEvenements(); }
      else showToast(res.message, 'error');
    });
  });

  // ═══════════════════════════════════════════════════════════
  // HELPERS
  // ═══════════════════════════════════════════════════════════
  function resetDoc() {
    $('#doc-id, #doc-libelle, #doc-lien').val('');
    $('#doc-filiere, #doc-annee').val('');
    $('#doc-niveau').find('option:not(:first)').remove().val('').prop('disabled', true);
    ['doc-libelle','doc-lien','doc-filiere','doc-niveau','doc-annee'].forEach(id => clearErr(id));
    setSaving('doc', false);
  }
  function resetEv() {
    $('#ev-id, #ev-titre, #ev-desc, #ev-image').val('');
    $('#ev-principal').prop('checked', false);
    clearErr('ev-titre'); setSaving('ev', false);
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
  function apiError(xhr, map) {
    const res = xhr.responseJSON;
    if (res?.errors) { $.each(res.errors, (f, m) => showErr(map[f] || f, m)); }
    else showToast(res?.message || 'Erreur.', 'error');
  }
  function esc(s) { return $('<div>').text(s || '').html(); }
  function escA(s) { return String(s || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }
});

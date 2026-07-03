/**
 * etablissements.js — Gestion CRUD des établissements (AJAX)
 * EICG Gestion Scolaire
 */

$(document).ready(function () {

  let editMode = false;

  // ─── Charger la liste ─────────────────────────────────────────────────────
  function loadEtablissements() {
    $.ajax({
      url: '/appeicg/etablissements/liste',
      method: 'GET',
      success: function (res) {
        if (!res.success) {
          showToast('Erreur lors du chargement.', 'error');
          return;
        }

        const etabs = res.data || [];
        $('#etab-count').text(`${etabs.length} établissement${etabs.length > 1 ? 's' : ''}`);

        const $tbody = $('#tbody-etab');
        $tbody.empty();

        if (etabs.length === 0) {
          $tbody.html(`
            <tr>
              <td colspan="6">
                <div class="empty-state">
                  <p>Aucun établissement enregistré.</p>
                  <button class="btn btn-primary btn-sm mt-3" id="btn-add-etab-empty">Ajouter le premier</button>
                </div>
              </td>
            </tr>
          `);
          return;
        }

        etabs.forEach(function (e) {
          const badgeClass = e.statut_etablissement === 'actif' ? 'badge-success' : 'badge-danger';
          const badgeLabel = e.statut_etablissement === 'actif' ? 'Actif' : 'Inactif';
          const toggleLabel = e.statut_etablissement === 'actif' ? 'Désactiver' : 'Activer';
          const toggleStatut = e.statut_etablissement === 'actif' ? 'inactif' : 'actif';

          $tbody.append(`
            <tr>
              <td>
                <div class="fw-600">${escHtml(e.libelle_etablissement)}</div>
                ${e.slogan_etablissement ? `<div class="text-sm text-muted">${escHtml(e.slogan_etablissement)}</div>` : ''}
              </td>
              <td>${e.email_etablissement ? escHtml(e.email_etablissement) : '<span class="text-muted">—</span>'}</td>
              <td>${e.telephone_etablissement ? escHtml(e.telephone_etablissement) : '<span class="text-muted">—</span>'}</td>
              <td>${e.adresse_etablissement ? escHtml(e.adresse_etablissement) : '<span class="text-muted">—</span>'}</td>
              <td><span class="badge ${badgeClass}">${badgeLabel}</span></td>
              <td>
                <div class="d-flex gap-2">
                  <button class="btn btn-outline btn-sm btn-edit-etab"
                    data-code="${e.code_etablissement}"
                    data-libelle="${escAttr(e.libelle_etablissement)}"
                    data-email="${escAttr(e.email_etablissement || '')}"
                    data-tel1="${escAttr(e.telephone_etablissement || '')}"
                    data-tel2="${escAttr(e.telephone_etablissement2 || '')}"
                    data-adresse="${escAttr(e.adresse_etablissement || '')}"
                    data-slogan="${escAttr(e.slogan_etablissement || '')}">
                    Modifier
                  </button>
                  <button class="btn btn-sm ${e.statut_etablissement === 'actif' ? 'btn-danger' : 'btn-accent'} btn-toggle-etab"
                    data-code="${e.code_etablissement}"
                    data-statut="${toggleStatut}">
                    ${toggleLabel}
                  </button>
                </div>
              </td>
            </tr>
          `);
        });
      },
      error: function () {
        showToast('Impossible de charger les établissements.', 'error');
      }
    });
  }

  loadEtablissements();

  // ─── Ouvrir modal Ajouter ─────────────────────────────────────────────────
  function openAddModal() {
    editMode = false;
    resetModal();
    $('#modal-etab-title').text('Ajouter un établissement');
    $('#modal-etab').addClass('open');
  }

  $('#btn-add-etab').on('click', openAddModal);
  $(document).on('click', '#btn-add-etab-empty', openAddModal);

  // ─── Ouvrir modal Modifier ────────────────────────────────────────────────
  $(document).on('click', '.btn-edit-etab', function () {
    editMode = true;
    resetModal();
    $('#modal-etab-title').text('Modifier l\'établissement');
    $('#etab-code').val($(this).data('code'));
    $('#etab-libelle').val($(this).data('libelle'));
    $('#etab-email').val($(this).data('email'));
    $('#etab-tel1').val($(this).data('tel1'));
    $('#etab-tel2').val($(this).data('tel2'));
    $('#etab-adresse').val($(this).data('adresse'));
    $('#etab-slogan').val($(this).data('slogan'));
    $('#modal-etab').addClass('open');
  });

  // ─── Fermer modal ─────────────────────────────────────────────────────────
  function closeModal() {
    $('#modal-etab').removeClass('open');
    resetModal();
  }

  $('#btn-close-modal, #btn-cancel-modal').on('click', closeModal);
  $('#modal-etab').on('click', function (e) {
    if ($(e.target).is('#modal-etab')) closeModal();
  });

  function resetModal() {
    $('#etab-code, #etab-libelle, #etab-email, #etab-tel1, #etab-tel2, #etab-adresse, #etab-slogan').val('');
    $('.form-error').removeClass('show').text('');
    $('.form-control').removeClass('is-invalid');
    setSaving(false);
  }

  // ─── Enregistrer ─────────────────────────────────────────────────────────
  $('#btn-save-etab').on('click', function () {
    // Clear erreurs
    $('.form-error').removeClass('show').text('');
    $('.form-control').removeClass('is-invalid');

    const libelle = $('#etab-libelle').val().trim();
    const email   = $('#etab-email').val().trim();

    if (!libelle) {
      $('#err-libelle').text('Le nom est obligatoire.').addClass('show');
      $('#etab-libelle').addClass('is-invalid');
      return;
    }

    setSaving(true);

    const url  = editMode ? '/appeicg/etablissements/modifier' : '/appeicg/etablissements/ajouter';
    const data = {
      libelle_etablissement:    libelle,
      email_etablissement:      email,
      telephone_etablissement:  $('#etab-tel1').val().trim(),
      telephone_etablissement2: $('#etab-tel2').val().trim(),
      adresse_etablissement:    $('#etab-adresse').val().trim(),
      slogan_etablissement:     $('#etab-slogan').val().trim(),
    };
    if (editMode) data.code_etablissement = $('#etab-code').val();

    $.ajax({
      url,
      method: 'POST',
      data,
      success: function (res) {
        setSaving(false);
        if (res.success) {
          showToast(res.message, 'success');
          closeModal();
          loadEtablissements();
        } else {
          showToast(res.message || 'Erreur.', 'error');
        }
      },
      error: function (xhr) {
        setSaving(false);
        const res = xhr.responseJSON;
        if (res && res.errors) {
          $.each(res.errors, function (field, msg) {
            const id = field.replace('_etablissement', '').replace('libelle', 'libelle').replace('email_', 'email');
            $(`#err-${id}`).text(msg).addClass('show');
          });
        } else {
          showToast(res?.message || 'Erreur lors de l\'enregistrement.', 'error');
        }
      }
    });
  });

  // ─── Changer statut ───────────────────────────────────────────────────────
  $(document).on('click', '.btn-toggle-etab', function () {
    const $btn   = $(this);
    const code   = $btn.data('code');
    const statut = $btn.data('statut');
    const label  = statut === 'actif' ? 'activer' : 'désactiver';

    if (!confirm(`Voulez-vous vraiment ${label} cet établissement ?`)) return;

    $btn.prop('disabled', true);

    $.ajax({
      url: '/appeicg/etablissements/statut',
      method: 'POST',
      data: { code_etablissement: code, statut_etablissement: statut },
      success: function (res) {
        if (res.success) {
          showToast(res.message, 'success');
          loadEtablissements();
        } else {
          showToast(res.message, 'error');
          $btn.prop('disabled', false);
        }
      },
      error: function () {
        showToast('Erreur lors du changement de statut.', 'error');
        $btn.prop('disabled', false);
      }
    });
  });

  // ─── Helpers ──────────────────────────────────────────────────────────────
  function setSaving(saving) {
    $('#btn-save-etab').prop('disabled', saving);
    $('#save-text').text(saving ? 'Enregistrement…' : 'Enregistrer');
    $('#save-spinner').toggle(saving);
  }

  function escHtml(str) {
    return $('<div>').text(str).html();
  }

  function escAttr(str) {
    return String(str).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

});

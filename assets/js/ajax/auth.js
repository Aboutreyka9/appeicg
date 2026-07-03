/**
 * auth.js — Gestion de l'authentification (AJAX)
 * EICG Gestion Scolaire
 */

// ─── Utilitaires ─────────────────────────────────────────────────────────────

function showToast(message, type = 'success') {
  const icons = {
    success: `<svg width="18" height="18" fill="none" stroke="#27AE60" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
    error:   `<svg width="18" height="18" fill="none" stroke="#E03E3E" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
    warning: `<svg width="18" height="18" fill="none" stroke="#F59E0B" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`,
  };

  const $toast = $(`
    <div class="toast ${type}">
      <span class="toast-icon">${icons[type] || icons.success}</span>
      <span class="toast-msg">${message}</span>
    </div>
  `);

  $('#toast-container').append($toast);

  setTimeout(() => {
    $toast.css({ opacity: 0, transform: 'translateX(40px)', transition: 'all .3s ease' });
    setTimeout(() => $toast.remove(), 300);
  }, 3500);
}

// ─── Vérification de session (pages protégées) ───────────────────────────────

function checkAuthAndSetUser() {
  const isLoginPage = window.location.pathname === '/' || window.location.pathname === '/login';

  $.ajax({
    url: '/appeicg/auth/check',
    method: 'GET',
    success: function (res) {
      if (res.success && res.data) {
        const user = res.data;
        // Remplir les infos user dans la sidebar
        const nom    = user.nom_user    || '';
        const prenom = user.prenom_user || '';
        const initials = (nom.charAt(0) + prenom.charAt(0)).toUpperCase();
        $('#sidebar-user-name').text(`${nom} ${prenom}`);
        $('#user-avatar-initials').text(initials || '??');

        // Rediriger si sur la page login
        if (isLoginPage) {
          window.location.href = '/dashboard';
        }
      } else {
        // Non authentifié
        if (!isLoginPage) {
          window.location.href = '/login';
        }
      }
    },
    error: function () {
      if (!isLoginPage) {
        window.location.href = '/login';
      }
    }
  });
}

// ─── Login ───────────────────────────────────────────────────────────────────

$(document).ready(function () {

  // Vérifier la session sur toutes les pages
  checkAuthAndSetUser();

  // Formulaire de connexion
  if ($('#btn-login').length) {

    // Soumettre avec Entrée
    $('#email, #password').on('keypress', function (e) {
      if (e.which === 13) $('#btn-login').trigger('click');
    });

    $('#btn-login').on('click', function () {
      // Effacer les erreurs
      $('.form-error').removeClass('show').text('');
      $('.form-control').removeClass('is-invalid');
      $('#login-alert').hide();

      const email    = $('#email').val().trim();
      const password = $('#password').val().trim();

      // Validation basique côté client
      let hasError = false;
      if (!email) {
        $('#err-email').text("L'email est obligatoire.").addClass('show');
        $('#email').addClass('is-invalid');
        hasError = true;
      }
      if (!password) {
        $('#err-password').text("Le mot de passe est obligatoire.").addClass('show');
        $('#password').addClass('is-invalid');
        hasError = true;
      }
      if (hasError) return;

      // Désactiver le bouton
      const $btn = $(this);
      $btn.prop('disabled', true).html('<span class="spinner"></span> Connexion…');

      $.ajax({
        url: '/appeicg/auth/login',
        method: 'POST',
        data: { email, password },
        success: function (res) {
          if (res.success) {
            showToast('Connexion réussie ! Redirection…', 'success');
            setTimeout(() => { window.location.href = '/dashboard'; }, 800);
          } else {
            $('#login-alert').text(res.message || 'Erreur de connexion.').show();
            $btn.prop('disabled', false).text('Se connecter');
          }
        },
        error: function (xhr) {
          const res = xhr.responseJSON;
          if (res && res.errors) {
            $.each(res.errors, function (field, msg) {
              $(`#err-${field}`).text(msg).addClass('show');
              $(`#${field}`).addClass('is-invalid');
            });
          } else {
            $('#login-alert').text(res?.message || 'Erreur de connexion.').show();
          }
          $btn.prop('disabled', false).text('Se connecter');
        }
      });
    });
  }

  // ─── Déconnexion ───────────────────────────────────────────────────────────
  $(document).on('click', '#btn-logout', function () {
    $.ajax({
      url: '/appeicg/auth/logout',
      method: 'POST',
      success: function () {
        window.location.href = '/login';
      },
      error: function () {
        window.location.href = '/login';
      }
    });
  });

});

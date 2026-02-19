/**
 * Gestion de la modal de connexion avec iframe
 */
export default class LoginModal {
  constructor() {
    this.modal = document.querySelector('[data-login-modal]');
    this.iframe = document.querySelector('[data-login-iframe]');
    this.openButtons = document.querySelectorAll('[data-login-modal-open]');
    this.closeButtons = document.querySelectorAll('[data-login-modal-close]');

    if (!this.modal || !this.iframe) return;

    this.isOpen = false;
    this.init();
  }

  init() {
    // Ouvrir la modal
    this.openButtons.forEach(button => {
      button.addEventListener('click', e => {
        e.preventDefault();
        const loginUrl = button.getAttribute('href') || button.dataset.loginUrl;
        this.open(loginUrl);
      });
    });

    // Fermer la modal
    this.closeButtons.forEach(button => {
      button.addEventListener('click', e => {
        e.preventDefault();
        this.close();
      });
    });

    // Fermer avec Escape
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && this.isOpen) {
        this.close();
      }
    });

    // Écouter les messages de l'iframe (connexion réussie)
    window.addEventListener('message', e => {
      // Vérifier que le message vient de WordPress
      if (e.data === 'wp-login-success' || (e.data && e.data.type === 'wp-login-success')) {
        this.onLoginSuccess();
      }
    });
  }

  /**
   * Gestion de la connexion réussie
   */
  onLoginSuccess() {
    this.close();
    // Recharger la page après une courte pause pour que l'animation de fermeture soit visible
    setTimeout(() => {
      window.location.reload();
    }, 300);
  }

  open(loginUrl) {
    if (!loginUrl) {
      console.error('URL de connexion non fournie');
      return;
    }

    // Charger l'URL dans l'iframe
    this.iframe.src = loginUrl;

    // Afficher la modal
    this.modal.classList.add('is-open');
    this.isOpen = true;

    // Empêcher le scroll du body
    document.body.style.overflow = 'hidden';

    // Surveiller les changements d'URL dans l'iframe pour détecter la connexion réussie
    this.startIframeMonitoring();
  }

  /**
   * Surveille l'iframe pour détecter une redirection après connexion
   */
  startIframeMonitoring() {
    // Arrêter la surveillance de l'iframe
    if (this.iframeCheckInterval) {
      clearInterval(this.iframeCheckInterval);
    }

    // Nettoyer l'interval précédent si existe
    if (this.iframeCheckInterval) {
      clearInterval(this.iframeCheckInterval);
    }

    this.iframeCheckInterval = setInterval(() => {
      try {
        // Essayer d'accéder à l'URL de l'iframe
        // Si on est redirigé vers wp-admin ou une autre page après connexion,
        // on peut le détecter (à condition que ce soit sur le même domaine)
        const iframeUrl = this.iframe.contentWindow.location.href;

        // Si l'URL contient wp-admin ou si on n'est plus sur wp-login.php
        if (
          iframeUrl.includes('wp-admin') ||
          (!iframeUrl.includes('wp-login.php') && !iframeUrl.includes('about:blank'))
        ) {
          // Connexion réussie détectée
          clearInterval(this.iframeCheckInterval);
          this.onLoginSuccess();
        }
      } catch (e) {
        // Erreur CORS - l'iframe a été redirigée vers un autre domaine
        // Cela peut indiquer une connexion réussie avec redirection
        console.log('Iframe monitoring: CORS restriction (possible login success)');
      }
    }, 500);
  }

  close() {
    this.modal.classList.remove('is-open');
    this.isOpen = false;

    // Réautoriser le scroll du body
    document.body.style.overflow = '';

    // Vider l'iframe après fermeture (pour éviter de garder la session)
    setTimeout(() => {
      this.iframe.src = 'about:blank';
    }, 300);
  }
}

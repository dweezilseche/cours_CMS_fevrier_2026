import Plyr from 'plyr';
import gsap from 'gsap';

/**
 *
 * @param {string} - DOM element
 * @param {object} - Facultative options
 */
export default class Medias {
  constructor(el, options = {}) {
    if (!el) return;

    this.options = {
      ...options,
    };

    // DOM
    this.DOM = {
      el,
      videos: [...el.querySelectorAll('.video-player')],
    };

    if (!this.DOM.videos.length) return;

    setTimeout(_ => {
      this.setPlayers();
    }, 0); // Delay to ensure videos are properly initialized, especially when loaded via PJAX
  }

  /**
   * Players
   */

  setPlayers() {
    this.players = this.DOM.videos.map((video, index) => {
      const player = video.querySelector('#player');
      const cursor = video.querySelector('.video-player__cursor');
      const cover = video.querySelector('.video-player__cover');
      const isBackground = video.classList.contains('is-background');

      const parent = video.parentNode;

      const plyr = new Plyr(player, {
        controls: [
          'play-large', // The large play button in the center
          // 'restart',      // Restart playback
          // 'rewind',       // Rewind by the seek time (default 10 seconds)
          'play', // Play/pause playback
          // 'fast-forward', // Fast forward by the seek time (default 10 seconds)
          'progress', // The progress bar and scrubber for playback and buffering
          // 'current-time', // The current time of playback
          // 'duration',     // The full duration of the media
          'mute', // Toggle mute
          'volume', // Volume control
          // 'captions',     // Toggle captions
          // 'settings',     // Settings menu
          // 'pip',          // Picture-in-picture (currently Safari only)
          // 'airplay',      // Airplay (currently Safari only)
          // 'download',     // Show a download button with a link to either the current source or a custom URL you specify in your options
          // 'fullscreen',   // Toggle fullscreen
        ],
      });

      if (isBackground) {
        // Autoplay video if the video background
        plyr.on('ready', _ => {
          plyr.muted = true;
          plyr.loop = true;
          // plyr.play();
        });
      } else {
        // Add cursor animations
        plyr.on('ready', _ => {
          cover.addEventListener('click', _ => {
            plyr.play();
          });
        });

        if (cursor) {
          this.initCursor(video, cursor);
        }
      }

      plyr.on('play', _ => video.classList.add('is-playing'));
      plyr.on('pause', _ => video.classList.remove('is-playing'));
      plyr.on('ended', _ => video.classList.remove('is-playing'));
      plyr.on('stop', _ => video.classList.remove('is-playing'));

      return plyr;
    });
  }

  /**
   * Cursor
   */

  initCursor(videoContainer, cursor) {
    if (window.innerWidth < 768) return;

    // Créer les fonctions quickTo pour le déplacement fluide
    const moveX = gsap.quickTo(cursor, 'x', { duration: 1, ease: 'expo.out' });
    const moveY = gsap.quickTo(cursor, 'y', { duration: 1, ease: 'expo.out' });

    // Réinitialiser la position CSS pour éviter les conflits
    // cursor.style.transform = 'translate(-50%, -50%)'

    // Gestionnaire de mouvement de souris
    const onMouseMove = e => {
      const rect = videoContainer.getBoundingClientRect();

      // Calculer la position par rapport au centre du conteneur
      const offsetX = 100;
      const offsetY = 50;

      const x = e.clientX - rect.left - rect.width / 2 + offsetX;
      const y = e.clientY - rect.top - rect.height / 2 + offsetY;

      moveX(x);
      moveY(y);
    };

    // Gestionnaire pour quitter la zone
    const onMouseLeave = () => {
      moveX(0);
      moveY(0);
    };

    // Ajouter les écouteurs d'événements
    videoContainer.addEventListener('mousemove', onMouseMove);
    videoContainer.addEventListener('mouseleave', onMouseLeave);

    // Stocker les références pour le nettoyage
    videoContainer._cursorHandlers = { onMouseMove, onMouseLeave };
  }

  /**
   * Destroy
   */

  destroy() {
    if (!this.DOM) return;

    // Nettoyer les écouteurs pour chaque vidéo
    this.DOM.videos.forEach(video => {
      if (video._cursorHandlers) {
        const { onMouseMove, onMouseLeave } = video._cursorHandlers;

        video.removeEventListener('mousemove', onMouseMove);
        video.removeEventListener('mouseleave', onMouseLeave);

        delete video._cursorHandlers;
      }
    });

    this.players?.forEach(player => {
      player.destroy();
    });

    this.DOM = null;
  }
}

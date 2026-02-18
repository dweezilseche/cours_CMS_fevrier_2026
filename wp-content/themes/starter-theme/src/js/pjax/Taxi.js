import gsap from 'gsap';
import { ScrollToPlugin } from 'gsap/ScrollToPlugin';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
gsap.registerPlugin(ScrollToPlugin, ScrollTrigger);

import { Core } from '@unseenco/taxi';
import PageTransitionDefault from './transitions/PageTransitionDefault';

// General
import MediasAsyncLoad from '../classes/MediasAsyncLoad';
import InView from '../classes/InView';

// Animations
import Introduction from '../animations/Introduction';

// Layout - Partials

export default class Taxi {
  constructor({ header, lenis }) {
    this.header = header;
    this.lenis = lenis;
    this.instances = {};

    // Check if required elements exist
    const taxiWrapper = document.querySelector('[data-taxi]');
    const taxiView = document.querySelector('[data-taxi-view]');

    if (!taxiWrapper || !taxiView) {
      console.warn('Taxi: Required elements not found in DOM. PJAX disabled.');
      return;
    }

    // Taxi Core (PJAX)
    this.core = new Core({
      links: 'a:not([target]):not([href^=\\#]):not([data-taxi-ignore])',
      removeOldContent: false,
      enablePrefetch: false, // avoid createCacheEntry error when prefetched page has no [data-taxi-view]
      renderers: {},
      transitions: {
        default: PageTransitionDefault,
      },
      hooks: {
        beforeLeave: () => {
          // Actions avant de quitter la page
          if (this.header && window.innerWidth < 768) {
            this.header.closeMenu();
            this.header.closeSecondaryMobile();
          }

          if (this.lenis) {
            this.lenis.stop();
          }
        },
        afterLeave: () => {
          this.destroyInstances();
        },
      },
    });

    // Where all modules are declared
    this.classMap = {
      // Général
      mediasAsyncLoad: {
        class: MediasAsyncLoad,
        params: () => [],
      },
      inview: {
        class: InView,
        params: container => [container, { lenis }],
      },
      // Partials
      // mediasSlider: add when MediasSlider class exists and is imported

      // Animations
      introduction: {
        class: Introduction,
        params: container => [container, { lenis }],
      },
    };

    this.setupEventListeners();
    this.markWooCommerceLinksAsIgnore();
    this.onDOMContentLoaded();
  }

  /**
   * Ajoute data-taxi-ignore aux liens panier/checkout/compte (y compris ceux générés par WooCommerce).
   * À appeler au chargement initial et après chaque entrée PJAX.
   */
  markWooCommerceLinksAsIgnore(container = document) {
    const root = container && container.body ? container.body : container || document.body;
    const wooPaths = ['/panier', '/cart', '/checkout', '/mon-compte', '/my-account'];
    root.querySelectorAll('a[href]').forEach(a => {
      const href = (a.getAttribute('href') || '').toLowerCase();
      if (wooPaths.some(path => href.includes(path))) {
        a.setAttribute('data-taxi-ignore', '');
      }
    });
  }

  /**
   * Instances
   */

  initializeClasses(container, options = {}) {
    Object.entries(this.classMap).forEach(([key, { class: Class, params }]) => {
      try {
        this.instances[key] = new Class(...params(container, options));
      } catch (error) {
        console.warn(`Failed to initialize ${key}:`, error);
      }
    });
  }

  destroyInstances() {
    Object.entries(this.instances).forEach(([key, instance]) => {
      if (instance && typeof instance.destroy === 'function') {
        try {
          instance.destroy();
          this.instances[key] = null;
        } catch (error) {
          console.warn(`Failed to destroy ${key}:`, error);
        }
      }
    });
  }

  /**
   * Events
   */

  setupEventListeners() {
    this.core.on('NAVIGATE_IN', ({ to, trigger }) => this.onEnter({ to, trigger }));
    this.core.on('NAVIGATE_END', ({ to, from, trigger }) =>
      this.onEnterCompleted({ to, from, trigger })
    );
    this.core.on('NAVIGATE_OUT', ({ from, trigger }) => this.onLeave({ from, trigger }));
  }

  onDOMContentLoaded() {
    this.initializeClasses(document);

    const page = document.body.querySelector('[data-taxi-view]');

    if (this.header) {
      this.header.updateTheme(document.querySelector('[data-taxi-view]'));
    }
  }

  onEnter({ to }) {
    // 1. Kill triggers
    ScrollTrigger.getAll().forEach(t => t.kill());

    // 2. Destroy modules
    this.destroyInstances();

    // 3. Init new page
    const page = to.renderer.content;
    this.initializeClasses(page);

    // 4. Liens WooCommerce (panier/checkout/compte) en full page
    this.markWooCommerceLinksAsIgnore(page);

    // 5. Refresh
    ScrollTrigger.refresh();
  }

  onEnterCompleted({ from, to, trigger }) {
    // Sur mobile : forcer la fermeture du menu et du menu secondaire (au cas où beforeLeave n’a pas suffi)
    if (this.header && window.innerWidth < 768) {
      this.header.closeMenu();
      this.header.closeSecondaryMobile();
    }

    // Restore the new page's body class (e.g. woocommerce, post-type-archive-product)
    // so page-specific CSS and WooCommerce layout don't collapse after PJAX.
    if (to?.page?.body?.className) {
      document.body.className = to.page.body.className;
    }

    if (this.lenis) {
      this.lenis.start();
      this.lenis.resize();
    }

    ScrollTrigger.refresh();

    if (window.location.hash) {
      const id = window.location.hash.replace('#', '');
      const target = document.getElementById(id);

      if (target) {
        gsap.to(window, {
          scrollTo: {
            y: target,
            offsetY: 150,
          },
          duration: 1.2,
          ease: 'power2.out',
        });
      }
    }
  }

  onLeave({ from, trigger }) {
    const page = from.renderer.content;

    if (this.header && window.innerWidth < 768) {
      this.header.closeMenu();
      this.header.closeSecondaryMobile();
    }
  }
}

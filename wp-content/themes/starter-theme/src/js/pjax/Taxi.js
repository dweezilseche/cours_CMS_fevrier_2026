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
import Introduction from '../animations/Introduction/index.js';

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
      renderers: {},
      transitions: {
        default: PageTransitionDefault,
      },
      hooks: {
        beforeLeave: () => {},
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

      mediasSlider: {
        class: MediasSlider,
        params: container => [container, { lenis }],
      },
    };

    this.setupEventListeners();
    this.onDOMContentLoaded();
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

    this.introduction = new Introduction(document, {
      lenis: this.lenis,
      delay: 0,
    });
  }

  onEnter({ to, trigger }) {
    const page = to.renderer.content;

    if (this.header) {
      this.header.reset();
    }

    this.initializeClasses(page, { delay: 0 });
  }

  onEnterCompleted({ from, to, trigger }) {
    ScrollTrigger.refresh();

    /**
     * Tarte au citron
     */

    const tarteAuCitron = window.tarteaucitron !== undefined ? window.tarteaucitron : null;

    // Debug
    /*console.log('Navigation data:', {
      tarteAuCitron,
      tarteAuCitron_job: tarteAuCitron?.job,
      gtagExists: typeof gtag, // undefined if the user does not accept the cookies
      page_path: this.core.targetLocation.pathname,
      page_title: to.page.title,
      page_location: this.core.targetLocation.href
    })*/

    if (tarteAuCitron && tarteAuCitron.job.includes('gtag') && typeof gtag !== 'undefined') {
      gtag('config', tarteAuCitron.user.gtagUa, {
        page_path: this.core.targetLocation.pathname,
        page_title: to.page.title,
        page_location: this.core.targetLocation.href,
      });
    }
  }

  onLeave({ from, trigger }) {
    const page = from.renderer.content;
  }
}

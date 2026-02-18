const mode = import.meta.env.MODE;

// Import fonts (Vite will process and generate correct URLs)
import './fonts';

// Import styles
import '../scss/app.scss';

import Stats from 'stats.js';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
gsap.registerPlugin(ScrollTrigger);
import Lenis from 'lenis';
import Taxi from './pjax/Taxi';

import { createConsoleLogCopyright } from './utils/createConsoleLogCopyright';
import { isTouchDevice } from './utils/isTouchDevice';
import { getDocumentDimensions } from './utils/getDocumentDimensions';
import { scrollToAnchor } from './utils/scrollToAnchor';

import Preloader from './layouts/Preloader';
import Header from './layouts/Header';
import Footer from './layouts/Footer';

class App {
  constructor() {
    this.isTouchDevice = isTouchDevice();

    createConsoleLogCopyright();

    this.createPreloader();

    this.preloader.on('complete', () => {
      setTimeout(() => {
        window.scrollTo(0, 0);
      }, 250);

      // this.createStats()
      this.createLenis();
      this.createHeader();
      this.createFooter();
      this.setTaxi();
      scrollToAnchor(this.lenis, 100);

      this.addEventListeners();
      this.onResize();
      this.update();
    });
  }

  /**
   * Scroll
   */

  createLenis() {
    this.lenis = null;

    if (!this.isTouchDevice) {
      this.lenis = new Lenis({
        duration: 0.82,
        easing: t => (t === 1 ? 1 : 1 - Math.pow(2, -10 * t)),
        smoothWheel: true,
        smoothTouch: false,
        normalizeWheel: true,
        autoResize: true,
        orientation: 'vertical',
      });

      window.lenis = this.lenis;
    }
  }

  /**
   * Stats
   */

  createStats() {
    if (mode !== 'development') return;

    this.fps = new Stats();
    this.fps.domElement.style.cssText = 'position:fixed;z-index:99999;bottom:0;right:0;';
    this.fps.showPanel(0);

    document.body.appendChild(this.fps.dom);
  }

  /**
   * PJAX
   */

  setTaxi() {
    this.taxi = new Taxi({
      header: this.header,
      lenis: this.lenis,
    });
  }

  /**
   * Layouts
   */

  createPreloader() {
    this.preloader = new Preloader();
  }

  createHeader() {
    this.header = new Header({
      lenis: this.lenis,
    });
  }

  createFooter() {
    this.footer = new Footer();
  }

  /**
   * Animations
   */

  /**
   * Events
   */

  onResize() {
    getDocumentDimensions();

    clearTimeout(this.resizeTimeout);
    this.resizeTimeout = setTimeout(() => {
      if (this.windowWidthResize !== window.innerWidth) {
        ScrollTrigger.refresh();
        if (this.header) {
          this.header.onResize();
        }
      }
    }, 250);
  }

  onWheel() {
    const scroll = {
      scroll: this.lenis?.scroll ?? window.scrollY,
      direction: this.lenis?.direction ?? (window.scrollY > this.lastScrollY ? 1 : -1),
    };
    this.lastScrollY = window.scrollY;

    this.header?.onScroll(scroll);
  }

  onTouchDown() {}

  onTouchMove() {}

  onTouchUp() {}

  /**
   * Loop
   */

  update(time) {
    // console.log('Update')

    if (this.fps) {
      this.fps.begin();
      this.fps.end();
    }

    if (this.lenis) {
      this.lenis.raf(time);
    }

    this.frame = window.requestAnimationFrame(this.update.bind(this));
  }

  /**
   * Cleanup
   */

  destroy() {
    if (this.resizeTimeout) {
      clearTimeout(this.resizeTimeout);
    }

    window.removeEventListener('resize', this.onResize);
    window.removeEventListener('orientationchange', this.onResize);
    window.removeEventListener('scroll', this.onWheel);
    window.removeEventListener('wheel', this.onWheel);

    if (this.frame) {
      window.cancelAnimationFrame(this.frame);
    }

    if (this.lenis) {
      this.lenis.destroy();
    }

    ScrollTrigger.killAll();
  }

  /**
   * Listeners
   */

  addEventListeners() {
    this.resizeTimeout = setTimeout(() => {}, 0);
    this.windowWidthResize = window.innerWidth;

    this.onWheel = this.onWheel.bind(this);
    this.onResize = this.onResize.bind(this);

    window.addEventListener('resize', this.onResize);
    window.addEventListener('orientationchange', this.onResize);
    window.addEventListener('scroll', this.onWheel);
    window.addEventListener('wheel', this.onWheel);
  }
}

const app = new App();

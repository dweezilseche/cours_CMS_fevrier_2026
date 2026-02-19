import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { SplitText } from 'gsap/SplitText';

gsap.registerPlugin(ScrollTrigger, SplitText);

export default class Introduction {
  constructor(el, options = {}) {
    if (!el) return;

    this.options = {
      lenis: options.lenis,
      header: options.header,
      prehome: options.prehome,
      delay: 0,
      ...options,
    };

    this.DOM = {
      el,
      page: el.querySelector('[data-taxi-view]') ? el.querySelector('[data-taxi-view]') : el,
      header: document.querySelector('#header'),
      footer: document.querySelector('#footer'),
      line: document.querySelector('[data-line-animation]'),

      blocksHero: el.querySelectorAll('.hero__content > *'),
      blocks: el.querySelectorAll('[data-block-animation]'),

      hero: el.querySelector('#hero'),
      heroTitle: el.querySelector('#hero .hero__title'),
      heroWrapperContent: el.querySelector('#hero .hero__wrapper__content'),

      sections: el.querySelector('[data-taxi-view]'),
    };

    this.init();
  }

  async init() {
    // Attendre que les polices soient chargées avant SplitText
    try {
      await document.fonts.ready;
    } catch (e) {
      console.warn('Fonts loading issue:', e);
    }

    this.createTimeline();
  }

  createTimeline() {
    this.timeline = gsap.timeline({
      delay: this.options.delay,
      defaults: {
        ease: 'power3.inOut',
        duration: 1,
      },
      onStart: () => {},
      onComplete: () => {},
    });

    // Body visible et cliquable dès le début de l’intro (évite clics ignorés par pointer-events: none)
    this.timeline.to(document.body, {
      opacity: 1,
      pointerEvents: 'auto',
      clearProps: 'all',
    });

    // Desktop only (comme ton code)
    if (window.innerWidth >= 768) {
      this.timeline.add(this.createHeaderFooter(0));
      this.timeline.add(this.animateHero(), '-=0.5');
    }
  }

  /**
   * Header & Footer
   */
  createHeaderFooter(delay = 0) {
    this.headerFooterTimeline = gsap.timeline({
      delay,
      onComplete: () => {},
    });

    if (this.DOM.header) {
      this.headerFooterTimeline.fromTo(
        this.DOM.header,
        {
          opacity: 0,
          yPercent: -100,
        },
        {
          opacity: 1,
          yPercent: 0,
          ease: 'power3.inOut',
          duration: 1.8,
          clearProps: 'opacity,yPercent',
        }
      );
    }

    return this.headerFooterTimeline;
  }

  /**
   * Hero
   */
  animateHero() {
    if (!this.DOM.hero) return null;

    const path = this.DOM.line?.querySelector('path');

    // Line path draw setup
    if (path) {
      const len = path.getTotalLength();
      gsap.set(path, { strokeDasharray: len, strokeDashoffset: len, opacity: 0 });
    }

    // Setup initial state for hero blocks
    if (this.DOM.blocksHero?.length) {
      gsap.set(this.DOM.blocksHero, { opacity: 0, y: 70 });
    }

    this.heroTimeline = gsap.timeline({
      onComplete: () => {},
    });

    // SplitText : garde une ref pour pouvoir revert au destroy
    if (this.DOM.heroTitle) {
      this.splitTitle = new SplitText(this.DOM.heroTitle, {
        type: 'lines',
        linesClass: 'line',
        mask: 'lines',
      });
    }

    // Labels = ultra pratique pour orchestrer
    this.heroTimeline.addLabel('start');

    // Draw line
    if (path) {
      this.heroTimeline.to(
        path,
        {
          strokeDashoffset: 0,
          duration: 5.5,
          ease: 'power1.out',
          clearProps: 'strokeDasharray,strokeDashoffset',
          opacity: 0.5,
        },
        'start'
      );
    }

    // Title lines in
    if (this.splitTitle?.lines?.length) {
      this.heroTimeline.from(
        this.splitTitle.lines,
        {
          yPercent: 100,
          opacity: 0,
          stagger: 0.1,
          ease: 'power3.inOut',
          duration: 1.6,
          clearProps: 'opacity,yPercent',
        },
        'start+=0.1'
      );
    }

    if (this.DOM.blocksHero?.length) {
      this.DOM.blocksHero.forEach((blockEl, i) => {
        // Apparition du bloc
        this.heroTimeline.to(
          blockEl,
          {
            opacity: 1,
            y: 0,
            duration: 1.5,
            ease: 'power3.out',
            clearProps: 'opacity,transform',
          },
          `start+=${1.7 + i * 0.15}`
        );

        // Animation de l'intérieur du bloc (list items, etc.)
        const insideTl = this.animateInsideBlock(blockEl);
        if (insideTl) {
          this.heroTimeline.add(insideTl, `start+=${1.75 + i * 0.05}`);
        }
      });
    }

    return this.heroTimeline;
  }

  animateInsideBlock(blockEl) {
    if (!blockEl) return null;

    // Option A : animate <li>
    const listItems = blockEl.querySelectorAll('li');

    // Option B (recommandé) : animate explicit targets
    // const listItems = blockEl.querySelectorAll('[data-anim="item"]');

    if (!listItems.length) return null;

    // initial state
    gsap.set(listItems, { opacity: 0, y: 30 });

    const tl = gsap.timeline();

    tl.to(listItems, {
      opacity: 1,
      y: 0,
      duration: 1.5,
      ease: 'power3.out',
      stagger: 0.06,
      clearProps: 'opacity,transform',
    });

    return tl;
  }

  /**
   * Destroy
   */
  destroy() {
    if (this.timeline) this.timeline.kill();
    if (this.headerFooterTimeline) this.headerFooterTimeline.kill();
    if (this.heroTimeline) this.heroTimeline.kill();
    if (this.splitTitle) this.splitTitle.revert();
  }
}

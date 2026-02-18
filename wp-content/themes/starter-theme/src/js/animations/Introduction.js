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

      hero: el.querySelector('#hero'),
      heroTitle: el.querySelector('#hero .hero__title'),
      heroWrapperContent: el.querySelector('#hero .hero__wrapper__content'),

      sections: el.querySelector('[data-taxi-view]'),
    };

    this.createTimeline();
  }

  createTimeline() {
    this.timeline = gsap.timeline({
      delay: this.options.delay,
      defaults: {
        ease: 'power3.inOut',
        duration: 1,
      },
      onStart: _ => {},
      onComplete: _ => {},
    });

    this.timeline.to(document.body, {
      opacity: 1,
      clearProps: 'all',
    });

    if (window.innerWidth >= 768) {
      this.timeline.add(this.createHeaderFooter(0), 0);
    }
  }

  /**
   * Header & Footer
   */

  createHeaderFooter(delay) {
    this.headerFooterTimeline = gsap.timeline({
      onComplete: _ => {},
      delay,
    });

    this.headerFooterTimeline.fromTo(
      this.DOM.header,
      {
        opacity: 0,
        yPercent: -100,
      },
      {
        opacity: 1,
        yPercent: 0,
        ease: 'expo.inOut',
        duration: 1.5,
        clearProps: 'opacity,yPercent',
      }
    );
  }

  /**
   * Destroy
   */

  destroy() {
    if (!this.DOM) return;

    if (this.timeline) {
      this.timeline.kill();
    }
  }
}

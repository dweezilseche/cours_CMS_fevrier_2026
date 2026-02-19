import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import SplitText from 'gsap/SplitText';
gsap.registerPlugin(ScrollTrigger);
gsap.registerPlugin(SplitText);

export default class Hero {
  constructor(el, options = {}) {
    if (!el) return;

    this.options = {
      ...options,
    };

    this.DOM = {
      el,
      media: el.querySelector('.hero__media'),
      title: el.querySelector('.hero__title'),
      subtitle: el.querySelector('.hero__subtitle'),
      line: el.querySelector('#hero-line'),
    };

    this.init();
  }

  init() {
    if (!this.DOM.el) return;

    this.createTimeline();
  }

  createTimeline() {
    this.timeline = gsap.timeline({
      defaults: { ease: 'none' },
      scrollTrigger: {
        trigger: this.DOM.subtitle,
        start: 'top bottom',
        toggleActions: 'play reverse play reverse',
      }
    });

    this.splitSubtitle = new SplitText(this.DOM.subtitle, {
      type: 'lines',
      linesClass: 'line',
      mask: 'lines',
    });

    this.timeline.from(this.splitSubtitle.lines, {
      yPercent: 100,
      opacity: 0,
      stagger: 0.1,
      ease: 'power4.inOut',
      duration: 1.6,
      clearProps: 'opacity,yPercent',
    });
  }
}

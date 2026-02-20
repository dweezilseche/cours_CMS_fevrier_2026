import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

export default class ListAnimation {
  constructor(el, options = {}) {
    if (!el) return;

    this.options = { ...options };

    this.DOM = {
      el,
      list: el.querySelector('[data-list-animation]'),
      cards: el.querySelectorAll('[data-card-animation]'),
    };

    this.init();
  }

  init() {
    if (!this.DOM.list || !this.DOM.cards.length) return;
    this.createTimeline();
  }

  createTimeline() {
    this.timeline = gsap.timeline()

    gsap.set(this.DOM.cards, { opacity: 0, y: 30 });

    this.timeline.to(this.DOM.cards, {
      opacity: 1,
      y: 0,
      stagger: 0.05,
      ease: 'power4.out',
      duration: 1.3,
      delay: 0.95,
    });
  }

  destroy() {
    if (!this.DOM.el) return;

    this.timeline.kill();
  }
}

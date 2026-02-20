import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

export default class Parallax {
  constructor(el, options = {}) {
    if (!el) return;

    this.options = {
      // speed par défaut si data-speed absent
      defaultSpeed: 0.2,
      // clearProps à la destruction
      clearProps: 'transform',
      // debug
      markers: true,
      ...options,
    };

    this.DOM = {
      el,
      items: el.querySelectorAll('[data-parallax-item]'),
      container: el.querySelector('[data-parallax-container]'),
    };

    this.triggers = [];
    this.tweens = [];

    this.init();
  }

  init() {
    if (!this.DOM.items.length) return;
    this.createParallax();
  }

  createParallax() {
    this.DOM.items.forEach(item => {
      const speed = parseFloat(item.dataset.speed || this.options.defaultSpeed);

      // Optionnel : éviter les valeurs extrêmes
      const safeSpeed = Number.isFinite(speed) ? speed : this.options.defaultSpeed;

      console.log(this.DOM.container);

      const tween = gsap.to(item, {
        yPercent: -safeSpeed * 100,
        ease: 'none',
        scrollTrigger: {
          trigger: this.DOM.container,
          start: 'top top',
          end: '+100%',
          scrub: true,
          markers: this.options.markers,
        },
      });

      this.tweens.push(tween);
      this.triggers.push(tween.scrollTrigger);
    });
  }

  destroy() {
    this.triggers.forEach(t => t && t.kill());
    this.tweens.forEach(tw => tw && tw.kill());

    if (this.DOM.items.length) {
      gsap.set(this.DOM.items, { clearProps: this.options.clearProps });
    }

    this.triggers = [];
    this.tweens = [];
  }
}

import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
gsap.registerPlugin(ScrollTrigger)

/**
 * @param {string} - Document selector
 */
export default class Footer {
  constructor(el = '#footer') {
    return

    // DOM
    this.DOM = { 
      footer: document.querySelector(el),
      footerContainer: document.querySelector('.footer__container')
    }

    if (!this.DOM.footer) return

    this.setAnimation()
  }

  setAnimation() {
    this.timeline = gsap.timeline({
      defaults: { ease: 'none' },
      scrollTrigger: {
        trigger: this.DOM.footer,
        start: 'top bottom',
        end: 'bottom bottom',
        scrub: true
      }
    })

    this.timeline.from(
      this.DOM.footerContainer,
      {
        yPercent: -80,
        scale: 1.2
      },
      0
    )
  }

  kill() {
    if (this.timeline) {
      this.timeline.kill()
    }
  }
}

import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
gsap.registerPlugin(ScrollTrigger)

export default class InView {
  constructor(el, options = {}) {
    if (!el) return

    this.options = {
      ...options
    }

    // DOM
    this.DOM = {
      els: [...el.querySelectorAll('.with-inview')]
    }

    this.initInView()
  }

  initInView() {
    if (!this.DOM.els.length) return

    this.scrollTriggers = this.DOM.els.map(section => {
      const scrollTrigger = ScrollTrigger.create({
        trigger: section,
        onEnter: _ => section.classList.add('is-visible'),
        // onLeave: _ => section.classList.remove('is-visible'),
        start: 'top 75%',
        end: 'bottom top'
      })

      return scrollTrigger
    })
  }

  destroy() {
    if (!this.DOM) return

    this.scrollTriggers?.forEach(st => st.kill())
    this.DOM = null
  }
}
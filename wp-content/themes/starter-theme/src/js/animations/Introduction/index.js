import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import { DrawSVGPlugin } from 'gsap/DrawSVGPlugin'
import { SplitText } from 'gsap/SplitText'
gsap.registerPlugin(ScrollTrigger, SplitText, DrawSVGPlugin)

import createPreloader from './createPreloader'
import createHero from './createHero'
import createHeaderFooter from './createHeaderFooter'
import createSections from './createSections'

export default class Introduction {
  constructor(el, options = {}) {
    if (!el) return

    this.options = {
      lenis: options.lenis,
      header: options.header,
      delay: 2,
      ...options
    }

    this.DOM = {
      el,
      preloader: el.querySelector('.preloader'),
      page: el.querySelector('[data-taxi-view]') ? el.querySelector('[data-taxi-view]') : el,
      header: document.querySelector('#header'),
      headerNav: document.querySelector('.header-nav'),
      headerButton: document.querySelector('.header__button-container'),
      footer: document.querySelector('#footer'),
      h1: el.querySelector('h1'),
      hero: el.querySelector('.hero'),
      heroSubtitle: el.querySelector('.hero__wrapper__subtitle'),
      heroButtons: el.querySelector('.hero__wrapper__buttons'),
      heroImg: el.querySelector('.hero__img img'),
      homeHero: el.querySelector('.home-hero'),
      homeHeroButton: el.querySelector('.home-hero__button'),
      homeHeroSvgMaskEPaths: [...el.querySelectorAll('.home-hero svg .mask-e path')],
      homeHeroSvgMaskCPaths: [...el.querySelectorAll('.home-hero svg .mask-c path')],
      sections: [...el.querySelectorAll('section')]
    }

    this.createTimeline()
  }

  createTimeline() {
    document.body.style = ''
    this.timeline = gsap.timeline({
      delay: this.options.delay,
      defaults: {
        ease: 'expo.inOut',
        duration: 1.5
      },
      onStart: _ => {
        // ScrollTrigger.refresh()
      },
      onComplete: _ => {
        this.options.lenis?.start()
        this.options.lenis?.resize()

        ScrollTrigger.refresh()
        document.documentElement.style.cursor = ''
      }
    })

    this.timeline.add(createPreloader(this.DOM), 0)

    if (window.innerWidth >= 768) {
      if (this.DOM.hero || this.DOM.homeHero) {
        this.splitLinesChild = new SplitText(this.DOM.h1, { linesClass: 'mask-child', type: 'lines' })
        this.splitLines = new SplitText(this.DOM.h1, { linesClass: 'mask-title oh', type: 'lines' })
        this.timeline.add(createHero(this.DOM, this.splitLines, this.splitLinesChild), 2)
        this.timeline.add(createSections(this.DOM.sections), 2.5)
        this.timeline.add(createHeaderFooter(this.DOM), 2.5)
      } else {
        this.timeline.add(createSections(this.DOM.sections), 0)
        this.timeline.add(createHeaderFooter(this.DOM))
      }
    }
  }

  destroy() {
    if (this.splitLinesChild) this.splitLinesChild.revert()
    if (this.splitLines) this.splitLines.revert()
    if (this.timeline) this.timeline.kill()
    this.splitLinesChild = null
    this.splitLines = null
    this.timeline = null
    this.DOM = null
  }
}

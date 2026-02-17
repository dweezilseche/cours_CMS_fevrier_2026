import gsap from 'gsap'

export default function createHero(DOM, splitLines, splitLinesChild) {
  const heroTimeline = gsap.timeline({
    defaults: {
      ease: 'expo.out',
      duration: 1
    },
    onComplete: () => {}
  })

  if (DOM.hero || DOM.homeHero) {
    if (splitLines?.lines) {
      heroTimeline.fromTo(splitLinesChild.lines, {
        yPercent: 100,
      }, {
        yPercent: 0,
        stagger: 0.125,
      }, 0)
    }

    if (DOM.heroSubtitle) {
      heroTimeline.fromTo(DOM.heroSubtitle, {
        y: 20,
        opacity: 0
      }, {
        y: 0,
        opacity: 1
      }, 0.35)
    }

    if (DOM.homeHeroButton || DOM.heroButtons) {
      heroTimeline.fromTo(DOM.homeHeroButton || DOM.heroButtons, {
        y: 20,
        opacity: 0
      }, {
        y: 0,
        opacity: 1
      }, 0.5)
    }

    if (DOM.heroImg) {
      heroTimeline.fromTo(DOM.heroImg, {
        scale: 1.2
      }, {
        scale: 1,
        duration: 1.5,
        ease: 'expo.out'
      }, 0)
    }

    if (DOM.homeHeroSvgMaskEPaths?.length && DOM.homeHeroSvgMaskCPaths?.length) {
      heroTimeline.from(DOM.homeHeroSvgMaskEPaths, {
        clipPath: 'inset(0% 0% 0% 0%)',
        stagger: 0.25,
        ease: 'power3.out',
        duration: 0.5
      }, 0.1)

      heroTimeline.from(DOM.homeHeroSvgMaskCPaths, {
        clipPath: 'inset(0% 0% 0% 0%)',
        stagger: 0.25,
        ease: 'power3.out',
        duration: 0.5
      }, 0.1 + 0.7)
    }
  }

  return heroTimeline
} 
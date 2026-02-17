import gsap from 'gsap'

export default function createPreloader(DOM) {
  if (!DOM.preloader) return

  const preloaderTimeline = gsap.timeline({
    defaults: {
      ease: 'power4.inOut',
      duration: 1
    },
    onComplete: _ => {
      DOM.preloader.remove()
    }
  })

  preloaderTimeline.fromTo('.preloader .ec', {
    opacity: 0
  }, {
    opacity: 1
  }, 0)

  preloaderTimeline.fromTo('.preloader .baseline > *', {
    y: 30,
    opacity: 0,
  }, {
    y: 0,
    opacity: 1,
    stagger: 0.075,
    duration: 0.75,
    ease: 'power4.out'
  }, 0.35)

  preloaderTimeline.to('.preloader', {
    yPercent: -100,
    duration: 1.25
  }, 1.35)

  preloaderTimeline.to('.preloader svg', {
    y: '125%',
    scale: 1.5,
    rotate: -10,
    duration: 1.25
  }, 1.35)

  return preloaderTimeline
} 
import gsap from 'gsap'

export default function createHeaderFooter(DOM) {
  const headerFooterTimeline = gsap.timeline({
    defaults: {
      ease: 'expo.out',
      duration: 1,
    },
    onComplete: _ => {}
  })

  headerFooterTimeline.fromTo(DOM.header, {
    opacity: 0,
    yPercent: -100
  }, {
    opacity: 1,
    yPercent: 0,
    stagger: 0.2,
    duration: 0.85,
    clearProps: 'all'
  }, 0)

  headerFooterTimeline.fromTo(DOM.footer, {
    opacity: 0,
  }, {
    opacity: 1,
    clearProps: 'opacity'
  }, 0)

  return headerFooterTimeline
} 
import gsap from 'gsap'

export default function createSections(sections) {
  if (!sections.length) return

  const timelineSections = gsap.timeline({
    defaults: {
      ease: 'expo.out',
      duration: 1,
    },
  })

  timelineSections.fromTo(sections, {
    opacity: 0,
    y: 50
  }, {
    opacity: 1,
    y: 0,
    clearProps: 'opacity,y'
  }, 0)

  return timelineSections
} 
import gsap from 'gsap'

export default function animateSplitText(splitLinesChild) {
  if (!splitLinesChild?.lines) return null

  const timeline = gsap.timeline()
  timeline.fromTo(
    splitLinesChild.lines,
    { yPercent: 100 },
    { yPercent: 0, stagger: 0.125 }
  )
  return timeline
} 
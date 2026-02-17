import gsap from 'gsap'

export function withGsapResponsive(breakpoint, onDesktop, onMobile) {
  const mm = gsap.matchMedia()
  
  const responsiveObj = {
    isDesktop: `(min-width: ${breakpoint}px)`,
    isMobile: `(max-width: ${breakpoint - 1}px)`
  }

  mm.add(responsiveObj, (context) => {
    if (context.conditions.isDesktop && onDesktop) onDesktop(context)
    if (context.conditions.isMobile && onMobile) onMobile(context)
  })

  return mm
}

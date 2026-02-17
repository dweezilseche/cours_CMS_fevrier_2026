export function scrollToAnchor(lenis, offset = 0) {
  const hash = window.location.hash
  if (!hash) return

  const target = document.querySelector(hash)
  if (!target) return

  setTimeout(() => {
    const top = target.getBoundingClientRect().top + window.scrollY - offset

    if (lenis) {
      lenis.scrollTo(top)
    } else {
      window.scrollTo({ top, behavior: 'smooth' })
    }
  }, 1000)
}
